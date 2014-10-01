<?php
/**
 * This file is part of Limedocs
 *
 * Copyright (C) Matthias ETIENNE <matthias@etienne.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Lime\Filesystem;

use Lime\App\RuntimeParameterAware;
use Lime\App\TRuntimeParameter;
use Lime\App\App;
use Lime\Exception\RuntimeException;
use Lime\Logger\LoggerAwareInterface;
use Lime\Logger\TLogger;
use Lime\Reflection\ReflectionNamespace;

/**
 * Finder
 *
 * The goal of the Finder class is to find files to generate documentation for,
 * based on paths, extensions, ignore lists, etc.
 *
 */
class Finder implements LoggerAwareInterface, RuntimeParameterAware
{
    use TLogger;
    use TRuntimeParameter;

    /**
     * @var string base directory of source code to search in
     */
    protected $sourceDir;

    /**
     * @var array Array of files found
     */
    protected $fileset = array();

    /** @var array Nammespaces container */
    protected $namespaces = array();

    /**
     * Create a new instance
     *
     * @param string $sourceDir Base directory to search in
     * @throws RuntimeException
     */
    final public function __construct($sourceDir)
    {

        $this->debug('Initializing ' . get_called_class());

        $this->sourceDir = $sourceDir;

        if (!is_dir($this->sourceDir)) {
            throw new RuntimeException(
                sprintf('Directory %s does not exists.', $this->sourceDir)
            );
        }

        if (!is_string(($extensions = $this->getParameter('generate.extensions')))) {
            throw new RuntimeException('Argument "extensions" must be a string.');
        }

        if (!is_string(($ignore = $this->getParameter('generate.ignore')))) {
            throw new RuntimeException('Argument "ignore" must be a string.');
        }

        // Redraw 'extensions' to a regular expression
        $this->setParameter(
            'generate.extensions', implode(
                '|',
                array_map('preg_quote', explode('|', $extensions))
            )
        );

        // If needed, redraw 'ignore' to a regular expression
        if ($ignore) {
            $ignoreArray = array_map('preg_quote', explode('|', $ignore));
            $this->setParameter('generate.ignore', implode('|', $ignoreArray));
        }

        $this->findFiles();
    }

    /**
     * Find matching source-code files through the filesystem
     *
     * @return array Returns a fileset, ie an array of {DoculizrFileInfo} objects
     */
    public function &findFiles()
    {
        $app = App::getInstance();
        $cache = $app->get('cache');

        $this->debug('Trying to find files in ' . $this->sourceDir);

        // get all all options
        $cacheKey = md5('finder.' . serialize($this->getParameters()));

        if (!$this->getParameter('generate.without-finder-cache') &&
            ($this->fileset = $cache->fetch($cacheKey))) {
            $this->debug('Finder fileset taken from cache');
            return $this->fileset;
        } else {
            $this->debug(
                'Finder is going to fetch a fresh fileset from filesystem. '.
                '(without-finder-cache='.$this->getParameter('generate.without-finder-cache').')'
            );
        }

        // Do not return '.' and '..' entries
        $flags = \FilesystemIterator::SKIP_DOTS;

        // shall we follow symlinks ?
        if ($this->getParameter('generate.follow-symlinks')) {
            $flags |= \FilesystemIterator::FOLLOW_SYMLINKS;
        }

        // match file extensions
        $extensions = $this->getParameter('generate.extensions');

        // ignore patterns
        $ignore = $this->getParameter('generate.ignore');

        $dirIterator = new \RecursiveDirectoryIterator($this->sourceDir, $flags);
        $iterator = new \RecursiveIteratorIterator(
            $dirIterator,
            \RecursiveIteratorIterator::SELF_FIRST
        );

        // don't search recursively
        if (!$this->getParameter('generate.recursive')) {
            $iterator->setMaxDepth(0);
        }

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                continue;
            }

            $matchExt = preg_match('@' . $extensions . '$@', $file->getExtension());
            $pathName = $file->getPathname();

            if ($matchExt &&
                (!$ignore || !preg_match('@' . $ignore . '@', $pathName))) {
                    $this->debug("Finder found file $pathName");
                    $this->fileset[$pathName] = FileInfoFactory::factory($pathName);
            } else {
                $this->debug("Finder ignored file $pathName");
            }
        }

        $cache->save(
            $cacheKey, $this->fileset,
            $this->getParameter('generate.finder-cache-ttl')
        );

        return $this->fileset;
    }

    /**
     * Get files found by the finder
     *
     * @return array fileset of matching files
     */
    final public function getFileset()
    {
        return $this->fileset;
    }

    /**
     * Gets one file from the fileset
     *
     * @param string $filepath File path to search for
     * @return mixed Returns a {DoculizrFileInfo} or null
     */
    final public function getFileFromFileset($filepath)
    {
        return isset($this->fileset[$filepath]) ?
                $this->fileset[$filepath] : null;
    }

    /**
     * @deprecated
     * @param type $namespaces
     */
    final public function registerNamespaces($namespaces)
    {
        foreach ($namespaces as $ns) {
            if (!array_key_exists($ns, $this->namespaces)) {
                $this->namespaces[$ns] = array();
            }
        }
    }

    /**
     * Get all interfaces found in fileset
     *
     * @return array
     */
    final public function getInterfacesInFileset()
    {
        $interfaces = array();
        /* @var $fileInfo \Lime\Filesystem\FileInfo */
        foreach ($this->fileset as $fileInfo) {
            $interfaces = array_merge($interfaces, $fileInfo->getInterfaces());
        }
        return array_unique($interfaces);
        ;
    }

    /**
     * Get all classes found in fileset
     *
     * @return array
     */
    final public function getClassesInFileset()
    {
        $classes = array();
        /* @var $fileInfo \Lime\Filesystem\FileInfo */
        foreach ($this->fileset as $fileInfo) {
            $classes = array_merge($classes, $fileInfo->getClasses());
        }
        return array_unique($classes);
    }

    /**
     * Get all namespaces found in fileset
     *
     * @return array
     */
    final public function getNamespacesInFileset()
    {
        $ns = array();
        /* @var $fileInfo \Lime\Filesystem\FileInfo */
        foreach ($this->fileset as $fileInfo) {
            $ns = array_merge($ns, $fileInfo->getNamespaces());
        }
        return array_unique($ns);
    }

    /**
     * Update file information in fileset
     *
     * @param string $pathname File path
     * @param FileInfo $fileinfo new file information
     * @return $this
     */
    final public function updateFileInfo($pathname, FileInfo $fileinfo)
    {
        $this->fileset[$pathname] = $fileinfo;
        return $this;
    }

    /**
     * Get the documentation tree, ie, an array of all namespaces, traits/classes,
     * interfaces, and methods that have been grabbed.
     *
     * @return array
     */
    final public function getNamespaces()
    {
        return $this->namespaces;
    }

    final public function namespaceExists($ns) {
        return array_key_exists($ns, $this->namespaces);
    }


    /**
     * Analyse the file set and build the namespaces tree that can be retrieved by
     * {getNamespaces()}
     *
     * @return void
     * @changelog 1.1 Added 'hasTraits' table key.
     */
    final public function analyseFileset()
    {
        foreach ($this->getFileset() as $file => $fileInfo) {
            $this->debug("Parsing file $file");
            $fileInfo->analyse();
        }
        $this->namespaces['global'] = array(
            'classes' => array(),
            'interfaces' => array(),
            'nsObject' => new ReflectionNamespace('global'),
            'hasTraits' => false
        );
        foreach ($this->fileset as $fileInfo) {
            foreach ($fileInfo->getNamespaces() as $ns) {
                if (!array_key_exists($ns, $this->namespaces)) {
                    $this->namespaces[$ns] = array(
                        'classes' => array(),
                        'interfaces' => array(),
                        'nsObject' => new ReflectionNamespace($ns),
                        'hasTraits' => false
                    );
                }
            }
            foreach ($fileInfo->getClasses() as $cls) {
                $ns = $cls->getNamespaceName();
                if (empty($ns)) {
                    $ns = 'global';
                }
                $this->namespaces[$ns]['classes'][$cls->getShortName()] = $cls;
                if ($cls->isTrait()) {
                    $this->namespaces[$ns]['hasTraits'] = true;
                }
            }

            foreach ($fileInfo->getInterfaces() as $itf) {
                $ns = $itf->getNamespaceName();
                if (empty($ns)) {
                    $ns = 'global';
                }
                $this->namespaces[$ns]['interfaces'][$itf->getShortName()] = $itf;
            }
        }

    }

}
