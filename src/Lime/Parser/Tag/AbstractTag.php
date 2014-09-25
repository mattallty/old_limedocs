<?php
/**
 * This file is part of Limedocs
 *
 * Copyright (C) Matthias ETIENNE <matthias@etienne.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lime\Parser\Tag;

use Lime\Filesystem\FileInfo;
use Lime\Common\Utils;

/**
 * Abstract Tag
 *
 * @package Doculizr
 * @subpackage Tags
 */
abstract class AbstractTag implements ITag {

    /** @var array Parsed data */
    protected $parsedData;

    /** @var FileInfo File object */
    protected $fileInfo;

    /** @var \Reflector Reflector object */
    protected $refObject;

    /** @var string tag*/
    protected $tag;

    /**
     * Constructs Tag
     *
     * @param string $tagValue Tag value
     * @param FileInfo $fileInfo related file object
     * @param \Reflector $refObject Related reflection object
     * @param string $tag tag name
     */
    final public function __construct($tagValue, FileInfo $fileInfo,
            \Reflector $refObject, $tag)
    {
        $this->tag = strtolower($tag);
        $this->fileInfo = $fileInfo;
        $this->refObject = $refObject;
        $this->parsedData = $this->parseData($tagValue);
    }

    /**
     * Get the referal object
     *
     * @return Reflector Reflector object
     *
     */
    final public function getRefObject()
    {
        return $this->refObject;
    }

    /**
     * Get related file object
     *
     * @param string $property only returns this file-object property
     * @return mixed The whole file object or the property value (or null)
     *
     */
    final public function getFileInfo($property = null)
    {
        if (is_null($property)) {
            return $this->fileInfo;
        }
        return isset($this->fileInfo[$property]) ?
                $this->fileInfo[$property] : null;
    }

    /**
     * Filter numeric indexes
     * 
     * @param array $regs Array of tags
     * @return array filtered array
     */
    protected function filterNumericIndexes(&$regs)
    {
        reset($regs);
        $filtered = array_filter($regs,
                function () use (&$regs) {
                    $ret = !is_numeric(key($regs));
                    next($regs);
                    return $ret;
                });

        return $filtered;
    }

    /**
     * Returns the tag, ie 'param', 'property', 'property-read'
     *
     * @return string tag
     */
    final public function getTag()
    {
        return $this->tag;
    }

    /**
     * Returns tag label
     * @return string Tag label
     */
    public function getLabel() {
        return 'unknown';
    }
    
    /**
     * Get PHP native types
     * 
     * @return string PHP Type, ie 'string', 'bool', 'float', etc.
     */
    public function getNativeTypes()
    {
        return array(
            'string',
            'bool',
            'boolean',
            'null',
            'int',
            'integer',
            'float',
            'double',
            'array',
            'object',
            'mixed',
            'callable',
            'closure',
            'resource',
            'stdclass'
        );
    }

    /**
     * Check if given string correponds to a native PHP type
     * 
     * @param string $type Type
     * @return boolean returns true if $type is a native PHP type, otherwise false.
     */
    public function isNativeType($type)
    {
        return in_array(strtolower($type), $this->getNativeTypes());
    }

    /**
     * Checks if a class name is fully scoped.
     * 
     * @param string $element Element, ie class or interface name.
     * @return string Returns the class name prepended by its namespace if needed.
     */
    public function isFullyScoped($element)
    {
        return substr($element, 0, 1) === '\\';
    }

    /**
     * Scope a string by prefixing it depending on environment, ie namespace,
     * imports, class, etc.
     *
     * @param string $element Element string
     * @return string scoped element
     */
    public function scopeElement($element)
    {
        // Check the 'type' if the code is namespaced
        $uses = $this->getFileInfo()->getBaseUses();
        $elements = explode('|', $element);
        
        foreach ($elements as $typeIndex => $type) {
            
            if (in_array($type, array('$this', 'self', 'this'))) {
                $elements[$typeIndex] = $this->getRefObject()->class;
                
            } elseif (!$this->isNativeType($type) && !$this->isFullyScoped($type)) {
                
                $typeTopLevel = $this->getNamespaceTopLevel($type);
                
                if (false !== $usesIndex = array_search($typeTopLevel, $uses)) {
                    $elements[$typeIndex] = substr($usesIndex, 0,
                                    -strlen($typeTopLevel)) . $type;
                } else {
                    $ns = $this->getFileInfo()->getNamespaces();
                    if(count($ns)) {
                        $elements[$typeIndex] = $ns[0].'\\'.$typeTopLevel;
                    }
                }
            }
            
            $elements[$typeIndex] = Utils::stripStartBackslash($elements[$typeIndex]);
        }

        return implode('|', $elements);
    }

    /**
     * Get to level of a namespace
     *
     * For example, if type is Foobar\Joe\Name, top level is Foobar
     * Works with a NS starting with a "\" (backslash) or not,
     * so you can use it with "use" statements.
     * 
     * @return string
     */
    protected function getNamespaceTopLevel($namespace)
    {
        return strtok($namespace, '\\');
    }

    /**
     * {@inheritdoc}
     * @return boolean
     */
    public function allowEmpty()
    {
        return true;
    }

    /**
     * Get the class name of the current object.
     * 
     * @return string returns class name.
     */
    public function getClassName()
    {
        return get_called_class();
    }

    /**
     * {@inheritdoc}
     * @return boolean
     */
    public function allowMultiLines()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     * @return boolean
     */
    public function overrideSameTag()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getParsedData()
    {
        return $this->parsedData;
    }

    /**
     * {@inheritdoc}
     * @return boolean
     */
    public function allowMultiTimes()
    {
        return true;
    }

}