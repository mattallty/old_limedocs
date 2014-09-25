<?php
//------------------------------------------------------------------------------
//
//  This file is part of Doculizr -- The PHP 5 Documentation Generator
//
//  Copyright (C) 2012 Matthias ETIENNE <matt@allty.com>
//
//  Permission is hereby granted, free of charge, to any person obtaining a
//  copy of this software and associated documentation files (the "Software"),
//  to deal in the Software without restriction, including without limitation
//  the rights to use, copy, modify, merge, publish, distribute, sublicense,
//  and/or sell copies of the Software, and to permit persons to whom the
//  Software is furnished to do so, subject to the following conditions:
//
//  The above copyright notice and this permission notice shall be included in
//  all copies or substantial portions of the Software.
//
//  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
//  EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
//  MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
//  IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,
//  DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
//  OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR
//  THE USE OR OTHER DEALINGS IN THE SOFTWARE.
//
//------------------------------------------------------------------------------
namespace Doculizr\Reflection;

use Doculizr\Parser\DoculizrParser;
use Doculizr\Finder\DoculizrFileInfo;
use Doculizr\Utils\DoculizrUtils;

/**
 * Reflection class handling class properties.
 * 
 * This reflection class handles class properties. It extends the native PHP class
 * {ReflectionProperty} and use the TMetaData trait.
 *
 * @author Matthias Etienne <matt@allty.com>
 * @copyright (c) 2012, Matthias Etienne
 * @license http://doculizr.allty.com/license MIT
 * @link http://doculizr.allty.com Doculizr Website
 *
 */
class DoculizrReflectionProperty extends \ReflectionProperty implements IMetaData {
    
    use TMetaData;
    
    protected $inheritsFromClass;
    protected $defaultValue;

    /**
     * Creates a new DoculizrReflectionProperty object
     * 
     * @param string $class Classname holding the property
     * @param string $name property name
     * @param \Doculizr\Finder\DoculizrFileInfo $fileInfo File in which is declared the property.
     */
    public function __construct($class, $name, DoculizrFileInfo $fileInfo)
    {
        parent::__construct($class, $name);

        $this->fileInfo = $fileInfo;

        $this->setMetadata(
                DoculizrParser::parseDocComment($this->getDocComment(),
                        $this->fileInfo, $this)
        );
    }
    /**
     * Gets the property type
     * 
     * @return string Type of the property or null if it cannot be guessed.
     */
    public function getType() {
        $meta = $this->getMetaData();
        return isset($meta['var']['type']) ? $meta['var']['type'] : null;
    }
    
    /**
     * Gets the shortest type
     * 
     * @return mixed Returns  the shortened type or null if it cannot be guessed.
     */
    public function getShortType() {
        if(($type = $this->getType())) {
            $parts = explode('\\', $type);
            return array_pop($parts);
        }
        return null;
    }
    
    /**
     * Gets the property description
     * 
     * @return string
     */
    public function getDescription() {
        $meta = $this->getMetaData();
        return (isset($meta['var']['description']) && 
                !empty($meta['var']['description'])) ? 
                DoculizrUtils::formatDescription($meta['var']['description'], $this->fileInfo, $this) : 
                    '<span class="muted">No description.</span>';
    }

    /**
     * Sets the class from which inherits the current class
     * 
     * @param \Doculizr\Reflection\DoculizrReflectionClass $class Inherited class
     * @return \Doculizr\Reflection\DoculizrReflectionProperty
     */
    public function setInherits(DoculizrReflectionClass $class)
    {
        $this->inheritsFromClass = $class;
        return $this;
    }

    /**
     * Sets the defaut value for the property.
     * 
     * @param mixed $default Default value.
     * @return \Doculizr\Reflection\DoculizrReflectionProperty
     */
    public function setDefault($default)
    {
        $this->defaultValue = $default;
        return $this;
    }

    /**
     * Gets the property default value
     * 
     * @return mixed
     */
    public function getDefault()
    {
        return $this->defaultValue;
    }

    /**
     * Gets the inherited class
     * 
     * @return \Doculizr\Reflection\DoculizrReflectionClass
     */
    public function getInherits()
    {
        return $this->inheritsFromClass;
    }


    public function __toString()
    {
        return '[property:'.$this->getName().']';
    }


}