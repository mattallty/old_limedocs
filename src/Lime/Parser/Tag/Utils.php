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

/**
 * Tag utils
 *
 * A collection of methods that helps with tags handling.
 *
 * @package Doculizr
 * @subpackage Tags
 */
class Utils
{

    /** @var array tag aliases */
    public static $tagAliases = array(
        'deprec' => 'deprecated',
        'property-read' => 'property',
        'property-write' => 'property',
        'aliasof' => 'alias'
    );

    /**
     * Get tag alias or null if no alias exists
     *
     * @param string $tagname tag name
     * @return string|null
     *
     */
    public static function getTagAlias($tagname)
    {
        return isset(self::$tagAliases[$tagname]) ?
                self::$tagAliases[$tagname] : null;
    }

    public static function addTagAlias($alias, $tag)
    {
        self::$tagAliases[$alias] = $tag;
    }

    public static function removeTagAlias($alias)
    {
        unset(self::$tagAliases[$alias]);
    }





    /**
     * @alias getTagAlias()
     */
    public static function tagAlias($tagname)
    {
        return self::getTagAlias($tagname);
    }

    public static function factory($tagName, $tagValue, $fileInfo, $refObject)
    {
        $clsTag = '\Lime\Parser\Tag\Tag' . ucfirst($tagName);

        // tag does not exists , check in aliases
        if (!class_exists($clsTag)) {
            if (($alias = self::getTagAlias($tagName))) {
                $clsTag = '\Lime\Parser\Tag\Tag' . ucfirst($alias);
            } else {
                return false;
            }
        }

        return new $clsTag($tagValue, $fileInfo, $refObject, $tagName);
    }

    /**
     * Split an element in scope and name parts
     * @param string $element Element string
     * @return array|boolean Returns elements or false on failure.
     * @see isFile
     */
    public static function getScopedElementParts($element)
    {
        $regs = null;
        if (!preg_match('/^([a-z0-9_]+)::([\$a-z0-9\(\)]+)/i', $element, $regs)) {
            return false;
        }
        return array($regs[1], $regs[2]);
    }

    /**
     * Checks if a string has a file format
     *
     * @param string $fileStr String to check
     * @return string|boolean Returns the string or false if the string is not a file
     */
    public static function isFile($fileStr)
    {
        $regs = null;
        $rgxFile = '/^(?<file>[a-z0-9_\.\-]+\.[a-z0-9]+)$/';
        if (preg_match($rgxFile, $fileStr, $regs)) {
            return $regs['file'];
        }
        return false;
    }

    /**
     * Checks if a string is a class
     *
     * @param string $element String to check
     * @return boolean Returns true if the string is a class
     */
    public static function isClass($element)
    {
        $regexClassName = '/^[a-z0-9_]+$/i';
        return preg_match($regexClassName, $element) && class_exists($element);
    }

    /**
     * Checks if a string is formated as a variable name, ie, starting with a "$"
     *
     * @param string $varStr string to check
     * @return string|boolean the string is returned if correctly checked,
     * otherwise false is returned
     */
    public static function isVar($varStr)
    {
        $regs = null;
        $rgxVar = '/^(?<variable>$[a-z0-9_]+)$/';
        if (preg_match($rgxVar, $varStr, $regs)) {
            return $regs['variable'];
        }
        return false;
    }

    /**
     * Checks if a string is formated as a function/method name
     *
     * @param string $funcStr string to check
     * @return string|boolean the string is returned if correctly checked,
     * otherwise false is returned
     */
    public static function isFunc($funcStr)
    {
        $regs = null;
        $rgxFunc = '/^(?<func>$[a-z0-9_]+)\(\)(.*)$/';
        if (preg_match($rgxFunc, $funcStr, $regs)) {
            return $regs['func'];
        }
        return false;
    }

    /**
     * Clean a propert/variable name by removing its leading '$'
     * @param string $property variable/property name
     * @return string cleaned property name
     */
    public static function cleanPropertyName($property)
    {
        if (substr($property, 0, 1) === '$') {
            $property = substr($property, 1);
        }
        return $property;
    }

    /**
     * Clean a method name by removing ending parenthesis "()"
     *
     * @param string $method function/method name
     * @return string cleaned method
     */
    public static function cleanMethodName($method)
    {
        if (substr($method, -2) === '()') {
            $method = substr($method, 0, -2);
        }
        return $method;
    }

    /**
     * Checks if a string is a valid URL
     *
     * @param string $url URL to check
     * @return boolean Returns true if the given string is a well formed URL
     */
    public static function isUrl($strUrl)
    {
        $parts = explode(' ', $strUrl, 2);

        if (!filter_var($parts[0], \FILTER_VALIDATE_URL)) {
            return false;
        }
        return array('url' => $parts[0],
            'text' => isset($parts[1]) ? $parts[1] : $parts[0]);
    }

    /**
     * @todo Implement method
     */
    public static function detectFileString($strFile)
    {
        return false;

    }

}