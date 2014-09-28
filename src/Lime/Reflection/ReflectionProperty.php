<?php
/*
 * This file is part of Limedocs
 *
 * Copyright (C) Matthias ETIENNE <matthias@etienne.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lime\Reflection;

use Lime\Parser\Parser;
use Lime\Filesystem\FileInfo;
use Lime\Common\Utils;
use Lime\Common\Utils\StrUtils;

/**
 * Reflection class handling class properties.
 *
 * This reflection class handles class properties. It extends the native PHP class
 * {ReflectionProperty} and use the TMetaData trait.
 *
 */
class ReflectionProperty extends \ReflectionProperty implements IMetaData
{

    use TMetaData;

    protected $inheritsFromClass;
    protected $defaultValue;

    /**
     * Creates a new ReflectionProperty object
     *
     * @param string $class Classname holding the property
     * @param string $name property name
     * @param FileInfo $fileInfo File in which is declared the property.
     */
    public function __construct($class, $name, FileInfo $fileInfo)
    {
        parent::__construct($class, $name);

        $this->fileInfo = $fileInfo;

        $this->setMetadata(
            Parser::parseDocComment(
                $this->getDocComment(),
                $this->fileInfo, $this
            )
        );
    }
    /**
     * Gets the property type
     *
     * @return string Type of the property or null if it cannot be guessed.
     */
    public function getType()
    {
        $meta = $this->getMetaData();
        return isset($meta['var']['type']) ? $meta['var']['type'] : null;
    }

    /**
     * Gets the shortest type
     *
     * @return mixed Returns  the shortened type or null if it cannot be guessed.
     */
    public function getShortType()
    {
        if (($type = $this->getType())) {
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
    public function getDescription()
    {
        $meta = $this->getMetaData();
        return (isset($meta['var']['description']) &&
                !empty($meta['var']['description'])) ?
                StrUtils::formatDescription($meta['var']['description'], $this->fileInfo, $this) :
                    '<span class="muted">No description.</span>';
    }

    /**
     * Sets the class from which inherits the current class
     *
     * @param ReflectionClass $class Inherited class
     * @return ReflectionProperty
     */
    public function setInherits(ReflectionClass $class)
    {
        $this->inheritsFromClass = $class;
        return $this;
    }

    /**
     * Sets the defaut value for the property.
     *
     * @param mixed $default Default value.
     * @return ReflectionProperty
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
     * @return ReflectionClass
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