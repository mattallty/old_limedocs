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

use Lime\Reflection\ReflectionFactory;

/**
 * File-related informations class
 *
 * This class match one or more documentable elements with a file.
 */
class FileInfo {

    /**
     * @var string Filename
     */
    protected $filename;
    /**
     * @var array Classnames found/used in the file
     */
    protected $uses = array();
    /**
     * @var array Cleaned classnames/used found in the file
     */
    protected $baseUses = null;
    /**
     * @var array Nemspaces found in the file
     */
    protected $namespaces = array();
    /**
     * @var array Classes declared in the file
     */
    protected $classes = array();
    /**
     * @var array Interfaces declared in the file
     */
    protected $interfaces = array();
    /**
     * @var array Functions declared in the file
     */
    protected $functions = array();


    /**
     * Constructor
     *
     * @param string $filename Filename to analyse
     */
    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    /**
     * Analyse the file to find classes and interfaces.
     *
     * @return \Doculizr\Finder\DoculizrFileInfo
     */
    public function analyse()
    {
        if (count($this->classes)) {
            foreach (array_keys($this->classes) as $class) {

                $this->classes[$class] = ReflectionFactory::factory(
                                'Lime\Reflection\ReflectionClass',
                                $class, $this);
            }
        }

        if (count($this->interfaces)) {
            foreach (array_keys($this->interfaces) as $itf) {
                $this->interfaces[$itf] = ReflectionFactory::factory(
                                'Lime\Reflection\ReflectionClass',
                                $itf, $this);
            }
        }

        return $this;
    }

    /**
     * Gets the filename
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set namespaces used in the file
     *
     * @param array $uses Array of namespaces names
     * @return \Doculizr\Finder\DoculizrFileInfo
     */
    public function setUses(array $uses)
    {
        if (count($uses)) {
            $this->uses = $uses;
        }
        $this->getBaseUses(true); // force
        return $this;
    }

    /**
     * Get used classes within the file
     *
     * @see setUses
     * @return array
     */
    public function getUses()
    {
        return $this->uses;
    }

    /**
     * Gets cleaned namespace used in this file
     *
     * @param boolean $force Force to compute the base names
     * @return array
     */
    public function getBaseUses($force = false)
    {
        if (!isset($this->baseUses) || $force === true) {
            $uses = $this->getUses();
            if(count($uses)) {
                $this->baseUses = array_combine($uses,
                        array_map(array($this, 'getUseBase'), $uses));
            }else{
                $this->baseUses = array();
            }
        }
        return $this->baseUses;
    }

    /**
     * Returns the element basename
     *
     * @param string $usepath "use" Path
     * @return string Cleaned name
     */
    private function getUseBase($usepath)
    {
        $parts = explode('\\', $usepath);
        return array_pop($parts);
    }

    /**
     * Set classes in file
     *
     * @param array $classes Array of classes
     * @return \Doculizr\Finder\DoculizrFileInfo
     * @see setNamespaces
     * @see getClasses
     */
    public function setClasses(array $classes)
    {
        $this->classes = array();
        if (count($classes)) {
            foreach ($classes as $class) {
                $this->classes[$class] = true;
            }
        }
        return $this;
    }

    /**
     * Get classes declared in the file.
     *
     * @see setClasses
     * @return array
     */
    public function getClasses()
    {
        return $this->classes;
    }

    /**
     * Set the namepaces that are declared in the file.
     *
     * @param array $namespaces Namespaces declared in the file
     * @return \Doculizr\Finder\DoculizrFileInfo
     * @see getNamespaces
     */
    public function setNamespaces(array $namespaces)
    {
        if (count($namespaces)) {
            $this->namespaces = array_unique($namespaces);
        }
        return $this;
    }

    /**
     * Gets the namespaces declared in the file
     * @return array
     */
    public function getNamespaces()
    {
        return $this->namespaces;
    }
    /**
     * Sets the interfaces declared
     * @return array Array of interfaces
     * @param array $interfaces Array of {DoculizrReflectionClass}
     * @see getInterfaces
     */
    public function setInterfaces(array $interfaces)
    {
        $this->interfaces = array();
        if (count($interfaces)) {
            foreach ($interfaces as $itf) {
                $this->interfaces[$itf] = true;
            }
        }
        return $this;
    }
    /**
     * Gets the iterfaces declared
     * @return array
     */
    public function getInterfaces()
    {
        return $this->interfaces;
    }
    /**
     * Set the functions declared
     *
     * @param array $functions Array of functions declared in the file.
     * @return array
     */
    public function setFunctions(array $functions)
    {
        if (count($functions)) {
            $this->functions = $functions;
        }
        return $this;
    }
    /**
     * Get the functions declared
     * @return array
     */
    public function getFunctions()
    {
        return $this->functions;
    }

}