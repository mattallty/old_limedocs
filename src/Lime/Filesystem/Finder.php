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

use Lime\Core;
use Lime\Exception\RuntimeException;

/**
 * Finder
 * 
 * The goal of the Finder class is to find files to generate documentation for,
 * based on paths, extensions, ignore lists, etc.
 *
 */
class Finder {
    
    /**
     * @var string base directory of source code to search in
     */
    protected $baseDir;
    
    protected $composerFile;
    protected $composerObj;
    
    protected $package;
    
    /**
     * @var array Array of files found
     */
    protected $fileset = array();

    /** @var array Nammespaces container */
    protected $namespaces = array();

    /**
     * Constructor
     * 
     * @param string $baseDir Base directory to search in
     */
    final public function __construct($baseDir) {

        $this->logger = Core::getLogger();
        $this->logger->debug('Initializing ' . get_called_class());
        $this->baseDir = $baseDir;

        $core = Core::getInstance();

        if (!is_dir($this->baseDir)) {
            throw new RuntimeException(
            sprintf('Directory %s does not exists.', $this->baseDir));
        }
        
        $this->composerFile = $this->baseDir. DS. 'composer.json';
        if(!file_exists($this->composerFile)) {
            throw new RuntimeException(
            sprintf('composer.json not found at %s', $this->composerFile));
        }
        
        // read composer 
        if(!($this->composerObj = json_decode(file_get_contents($this->composerFile)))) {
            throw new RuntimeException('Cannot parse composer file '.$this->composerFile);
        }
        
        if(!isset($this->composerObj->name) || empty($this->composerObj->name)) {
            throw new RuntimeException('Cannot detect package name from composer file '.$this->composerFile);
        }
        
        $this->package = $this->composerObj->name;
        
        $this->analysePsr();
        
        $core->setOption('destination', $core->getOption('destination').DS.$this->package);

        if (!is_string(($extensions = $core->getOption('extensions')))) {
            throw new RuntimeException('Argument "extension" must be a string.');
        }

        if (!is_string(($ignore = $core->getOption('ignore')))) {
            throw new RuntimeException('Argument "ignore" must be a string.');
        }

        // Redraw 'extensions' to a regular expression
        $extensionsArray = array_map('preg_quote', explode('|', $extensions));

        $core->setOption('extensions', implode('|', $extensionsArray));

        // If needed, redraw 'ignore' to a regular expression
        if ($ignore) {
            $ignoreArray = array_map('preg_quote', explode('|', $ignore));
            $core->setOption('ignore', implode('|', $ignoreArray));
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
        $logger = $this->getLogger();
        $core = Core::getInstance();

        $logger->debug('Trying to find files in ' . $this->baseDir);


        // get all all options
        $cacheKey = md5('finder.' . serialize($core->getOption()));

        if (!$core->getOption('finder-cache-disable') &&
            ($this->fileset = Core::getCache()->get($cacheKey))) {
            $logger->debug('Finder fileset taken from cache');
            return $this->fileset;
        }else{
            $logger->debug('Finder is going to fetch a fresh fileset from filesystem. '.
                '(finder-cache-disable='.$core->getOption('finder-cache-disable').')');
        }

        // Do not return '.' and '..' entries
        $flags = \FilesystemIterator::SKIP_DOTS;

        // shall we follow symlinks ?
        if (!$core->getOption('no-follow')) {
            $flags |= \FilesystemIterator::FOLLOW_SYMLINKS;
        }

        // match file extensions
        $extensions = $core->getOption('extensions');

        // ignore patterns
        $ignore = $core->getOption('ignore');

        $dirIterator = new \RecursiveDirectoryIterator($this->baseDir, $flags);
        $iterator = new \RecursiveIteratorIterator($dirIterator,
            \RecursiveIteratorIterator::SELF_FIRST);

        // don't search recursively
        if ($core->getOption('no-recursive')) {
            $iterator->setMaxDepth(0);
        }

        foreach ($iterator as $file) {

            if ($file->isDir()) {
                continue;
            }

            $matchExt = preg_match('@' . $extensions . '$@',
                $file->getExtension());

            $pathName = $file->getPathname();

            if ($matchExt && (!$ignore || !preg_match('@' . $ignore . '@',
                        $pathName))) {
                $this->fileset[$pathName] = FileInfoFactory::factory($pathName);
            } else {
                $logger->debug('Ignoring file ' . $pathName);
            }
        }

        Core::getCache()->set($cacheKey, $this->fileset, $core->getOption('finder-cache-duration'));
        return $this->fileset;
    }

    
    
    protected function analysePsr() {
        if(!isset($this->composerObj->autoload) || !isset($this->composerObj->autoload->{'psr-0'})) {
            throw new RuntimeException('Cannot find "autoload" or "psr-0" key in composer.json');
        }
        foreach ($this->composerObj->autoload->{'psr-0'} as $namespace => $path) {
            //var_dump($namespace, $path);
        }
        //exit;
       // var_dump($this->composerObj->autoload);exit;
    }

    /**
     * Get files found by the finder
     * 
     * @return array fileset of matching files
     */
    final public function getFileset() {
        return $this->fileset;
    }
    
    /**
     * Gets one file from the fileset
     * 
     * @param string $filepath File path to search for
     * @return mixed Returns a {DoculizrFileInfo} or null
     */
    final public function getFileFromFileset($filepath) {
        return isset($this->fileset[$filepath]) ? 
                $this->fileset[$filepath] : null;
    }

    /**
     * @deprecated
     * @param type $namespaces
     */
    final public function registerNamespaces($namespaces) {
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
    final public function getInterfacesInFileset() {
        $interfaces = array();
        /* @var $fileInfo Lime\Finder\FileInfo */
        foreach ($this->fileset as $fileInfo) {
            $interfaces = array_merge($interfaces, $fileInfo->getInterfaces());
        }
        return array_unique($interfaces);;
    }

    /**
     * Get all classes found in fileset
     * 
     * @return array
     */
    final public function getClassesInFileset() {
        $classes = array();
        /* @var $fileInfo Lime\Finder\FileInfo */
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
    final public function getNamespacesInFileset() {
        $ns = array();
        /* @var $fileInfo Lime\Finder\FileInfo */
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
     */
    final public function updateFileInfo($pathname, FileInfo $fileinfo) {
        $this->fileset[$pathname] = $fileinfo;
        return $this;
    }

    /**
     * Get the documentation tree, ie, an array of all namespaces, traits/classes, 
     * interfaces, and methods that have been grabbed.
     * 
     * @return array
     */
    function getDocumentationTree() {
        return $this->namespaces;
    }

    /**
     * Analyse the file set and build the file tree that can be retrieved by 
     * {getDocumentationTree()}
     * 
     * @return void
     * @changelog 1.1 Added 'hasTraits' table key.
     */
    final public function analyseFileset() {
        foreach ($this->getFileset() as $file => $fileInfo) {
            $this->getLogger()->info("Parsing file $file");
            $fileInfo->analyse();
        }
        $this->namespaces['global'] = array(
            'classes' => array(),
            'interfaces' => array(),
            'hasTraits' => false
        );
        foreach ($this->fileset as $fileInfo) {

            foreach ($fileInfo->getNamespaces() as $ns) {
                if (!array_key_exists($ns, $this->namespaces)) {
                    $this->namespaces[$ns] = array(
                        'classes' => array(),
                        'interfaces' => array(),
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
                if($cls->isTrait()) {
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
