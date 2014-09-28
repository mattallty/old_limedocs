<?php
/**
 * This file is part of Limedocs
 *
 * Copyright (C) Matthias ETIENNE <matthias@etienne.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Reflection Class
 *
 * @author Matthias Etienne <matthias@etienne.in>
 */
namespace Lime\Reflection;

use Lime\Logger\LoggerAwareInterface;
use Lime\Logger\TLogger;
use Lime\Parser\Parser;
use Lime\Filesystem\FileInfo;
use Lime\Filesystem\FileInfoFactory;
use Lime\Common\Utils;

/**
 * Reflection class handling classes and interfaces
 */
class ReflectionClass extends \ReflectionClass implements IMetaData, LoggerAwareInterface
{

    // use the TMetaData trait
    use TMetaData;

    // use TSourceCode Trait
    use TSourceCode;

    use TLogger;

    /**
     * Class methods
     * @var array
     */
    protected $methods;
    /**
     * Inherited class
     * @var ReflectionClass
     */
    protected $inheritsFromClass;
    /**
     * Class properties
     * @var array
     */
    protected $properties;

    /**
     * File that declares this class
     * @var FileInfo
     */
    protected $fileInfo;

    public function __construct($name, FileInfo $fileInfo)
    {
        $this->info("Analysing class $name");
        parent::__construct($name);

        $this->fileInfo = $fileInfo;

        $this->setMetadata(
            Parser::parseDocComment(
                $this->getDocComment(),
                $this->fileInfo, $this
            )
        );

        $this->getMethods();

        if (($parentClass = $this->getParentClass())) {
            $parentClassName = $parentClass->getName();
            $parentFileInfo = FileInfoFactory::factory($parentClass->getFileName());
            $reflexParent = ReflectionFactory::factory(
                'Lime\Reflection\ReflectionClass',
                $parentClassName, $parentFileInfo
            );

            $this->setInherits($reflexParent);
        }
    }

    /**
     * Get constants informations
     * @return array Returns an array of constants names => constants infos.
     */
    public function getConstantsInfos()
    {
        $constants = parent::getConstants();
        $response = array();
        foreach ($constants as $name => $value) {
            $response[$name] = array(
                'type' => gettype($value),
                'value' => gettype($value) === 'string' ? '"'.$value.'"' : $value
            );
        }
        return $response;
    }

    /**
     * Set inherited class
     * @param \Lime\Reflection\ReflectionClass $class Inherited class
     * @return \Lime\Reflection\ReflectionClass
     */
    public function setInherits(ReflectionClass $class)
    {
        $this->inheritsFromClass = $class;
        return $this;
    }

    /**
     * Get inherited class
     * @return \Lime\Reflection\ReflectionClass
     */
    public function getInherits()
    {
        return $this->inheritsFromClass;
    }

    /**
     * Get class ancestors
     * @return \Lime\Reflection\ReflectionClass[]
     */
    public function getAncestors()
    {
        $ret = array();
        $ret[$this->getName()] = $this;
        if (($parent = $this->getInherits())) {
            while ($parent) {
                $ret[$parent->getName()] = $parent;
                $parent = $parent->getInherits();
            }
        }
        return array_reverse($ret);
    }

    /**
     * Get associated documentation filename
     *
     * @param string $extension Filename extension
     * @return string Return associated documentation filename
     */
    public function getDocFileName($extension = 'html')
    {
        return str_replace('\\', '.', $this->name).'.'.$extension;
    }

    /**
     * Get file object
     * @return FileInfo
     */
    public function getFileInfo()
    {
        return $this->fileInfo;
    }

    /**
     * Get informations about a specific method
     * @param string $method Method name
     * @return ReflectionMethod|null
     * @see getMethods
     */
    public function getMethod($method)
    {
        $methods = $this->getMethods();
        return isset($methods[$method]) ? $methods[$method] : null;
    }

    /**
     * Get all methods declared by this class
     *
     * @param integer $filter Flag used to filter properties returned. Use one of the following flag :
     *  - {ReflectionProperty::IS_STATIC}
     *  - {ReflectionProperty::IS_STATIC}
     *  - {ReflectionProperty::IS_STATIC}
     *  - {ReflectionProperty::IS_STATIC}
     * @return \Lime\Reflection\ReflectionMethod[]
     * @see getMethod, getProperties
     */
    public function getMethods($filter = null)
    {
        if (is_null($this->methods)) {
            $this->methods = array();
            $methods = parent::getMethods();

            foreach ($methods as $method) {
                $fileInfo = FileInfoFactory::factory($method->getFilename());
                $reflexMethod = ReflectionFactory::factory(
                    'Lime\Reflection\ReflectionMethod',
                    $this->getName(), $method->name, $fileInfo
                );

                $declaringClass = $reflexMethod->getDeclaringClass()->getName();
                $reflexMethod->setClass($this);

                if ($declaringClass != $this->getName()) {
                    $this->info("Method {$method->name} in class {$this->getName()} inherits from upper class {$declaringClass}");

                    $fileInfo = FileInfoFactory::factory($reflexMethod->getDeclaringClass()->getFilename());

                    $reflexMethod->setInherits(
                        ReflectionFactory::factory(
                            'Lime\Reflection\ReflectionClass',
                            $declaringClass, $fileInfo
                        )
                    );

                }

                $reflexMethod->setupDocHeritage();
                $this->methods[$reflexMethod->getShortName()] = $reflexMethod;
            }

            ksort($this->methods);
        }

        return $this->methods;
    }

    /**
     * Get all properties
     *
     * @param type $filter Used for filtering properties.
     * @return array
     */
    public function getProperties($filter = null)
    {
        if (is_null($this->properties)) {
            $reflexProperties = $this->getDefaultProperties();
            $this->properties = array();
            $fileInfo = FileInfoFactory::factory($this->getFilename());

            foreach ($reflexProperties as $propName => $defaultValue) {
                $reflexProp = ReflectionFactory::factory(
                    'Lime\Reflection\ReflectionProperty',
                    $this->getName(),
                    $propName,
                    $fileInfo
                );

                $reflexProp->setAccessible(true);
                $reflexProp->setDefault($defaultValue);

                $declaringClass = $reflexProp->getDeclaringClass()->getName();

                if ($declaringClass !== $this->getName()) {
                    $fileInfo = FileInfoFactory::factory($reflexProp->getDeclaringClass()->getFilename());
                    $reflexProp->setInherits(
                        ReflectionFactory::factory(
                            'Lime\Reflection\ReflectionClass',
                            $reflexProp->class, $fileInfo
                        )
                    );
                }

                $this->properties[$propName] = $reflexProp;
            }
        }

        return $this->properties;
    }

    /**
     * Gets public methods
     *
     * @see getMethods
     * @param bool $showInherited Also gets inherited methods
     * @return array
     */
    public function getPublicMethods($showInherited = true)
    {
        return $this->getMethodsByVisibility(
            \ReflectionMethod::IS_PUBLIC,
            $showInherited
        );
    }
    /**
     * Gets protected methods
     *
     * @see getMethods
     * @param bool $showInherited Also gets inherited methods
     * @return array
     */
    public function getProtectedMethods($showInherited = true)
    {
        return $this->getMethodsByVisibility(
            \ReflectionMethod::IS_PROTECTED,
            $showInherited
        );
    }
    /**
     * Gets private methods
     *
     * @see getMethods
     * @param bool $showInherited Also gets inherited methods
     * @return array
     */
    public function getPrivateMethods($showInherited = true)
    {
        return $this->getMethodsByVisibility(
            \ReflectionMethod::IS_PRIVATE,
            $showInherited
        );
    }


    public function getPossibleDescription()
    {
        if (!$this->isUserDefined()) {
            return Utils::beautifyDescription(Core::getQuickRef($this->name, $this->getDeclaringClass()->name));
        }
        $meta = $this->getMetaData();
        if (isset($meta['longDescription']) && !empty($meta['longDescription'])) {
            return $meta['longDescription'];
        } elseif (isset($meta['shortDescription']) && !empty($meta['shortDescription'])) {
            return $meta['shortDescription'];
        }
        return false;
    }

    public function getShortDescription()
    {
        if (!$this->isUserDefined()) {
            return Utils::beautifyDescription(Core::getQuickRef($this->name, $this->getDeclaringClass()->name));
        }
        $meta = $this->getMetaData();
        if (isset($meta['shortDescription']) && !empty($meta['shortDescription'])) {
            return $meta['shortDescription'];
        }
        return false;
    }


    private function getMethodsByVisibility($visibility, $showInherited)
    {
        $methods = array();
        if ($visibility === \ReflectionMethod::IS_PUBLIC) {
            $checkMethod = 'isPublic';
        } elseif ($visibility === \ReflectionMethod::IS_PRIVATE) {
            $checkMethod = 'isPrivate';
        } else {
            $checkMethod = 'isProtected';
        }
        foreach ($this->getMethods() as $method) {
            if ($method->$checkMethod() && ($showInherited || \is_null($method->getInherits()))) {
                $methods[$method->getName()] = $method;
            }
        }
        return $methods;
    }

    public function __toString()
    {
        return '[class:'.$this->getName().']';
    }

}