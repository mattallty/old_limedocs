<?php
/**
 * This file is part of Limedocs
 *
 * Copyright (C) Matthias ETIENNE <matthias@etienne.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lime\Common;

use Lime\Reflection\ReflectionFactory;
use Lime\Filesystem\FileInfo;
use Michelf\MarkdownExtra;


/**
 * Collection of utils
 */
class Utils {


    public static $globalFunctions;

    /**
     * Find global functions defined in a file
     *
     * @param string|FileInfo $file Filename or {FileInfo} object
     * @return array functions found
     */
    public static function getGlobalFunctions(FileInfo $file)
    {
        if (!isset(self::$globalFunctions)) {
            $definedFuncs = get_defined_functions();
            // get only user-defined functions
            foreach ($definedFuncs['user'] as $func) {
                $refFunc = ReflectionFactory::factory('Lime\Reflection\ReflectionFunction',
                    $func, $file);
                $filename = $refFunc->getFileName();
                if (!isset(self::$globalFunctions[$filename])) {
                    self::$globalFunctions[$filename] = array();
                }
                self::$globalFunctions[$filename][$func] = $refFunc;
            }
        }

        return isset(self::$globalFunctions[$file->getFilename()]) ?
            self::$globalFunctions[$file->getFilename()] :
            array();
    }

    /**
     * @deprecated since version number
     * @param string $description The description string to beautify
     * @return string Returns the beautified description
     */
    public static function beautifyDescription($description) {
        if(empty($description)) {
            return '';
        }
        $regexLinks = '{([a-z0-9\_\\\]+)}';
        $punctuation = array('!', '.', '?');
        $desc = ucfirst($description);
        if(!in_array(substr($desc, -1), $punctuation)) {
            return $desc.'.';
        }
        return $desc;
    }

    public static function formatDescription($description, $fileInfo, $refObject) {
        $description = preg_replace_callback('/\{([a-z0-9_\\\]+)\}/i',
            function($regs) use($fileInfo, $refObject) {

                $type = self::scopeElement($regs[1], $fileInfo, $refObject);
                $url = self::objectTypeToFilepath($type);

                return '<a href="' . $url . '">' . $regs[1] . '</a>';
            }, $description);

        $markdown = MarkdownExtra::defaultTransform(
            self::beautifyDescription($description)
        );

        return $markdown;
    }

    public static function objectTypeToFilepath($filepath) {
        return strtolower(str_replace('\\', '.', $filepath)).'.html';
    }


    /**
     * Scope a string by prefixing it depending on environment, ie namespace,
     * imports, class, etc.
     *
     * @param string $element Element string
     * @return string scoped element
     */
    public static function scopeElement($element, $fileInfo, $refObject)
    {
        // Check the 'type' if the code is namespaced
        $uses = $fileInfo->getBaseUses();
        $elements = explode('|', $element);

        foreach ($elements as $typeIndex => $type) {

            if (in_array($type, array('$this', 'self', 'this'))) {
                $elements[$typeIndex] = $refObject->class;

            } elseif (!self::isNativeType($type) && !self::isFullyScoped($type)) {

                $typeTopLevel = self::getNamespaceTopLevel($type);

                if (false !== $usesIndex = array_search($typeTopLevel, $uses)) {
                    $elements[$typeIndex] = substr($usesIndex, 0,
                            -strlen($typeTopLevel)) . $type;
                } else {
                    $ns = $fileInfo->getNamespaces();
                    if(count($ns)) {
                        $elements[$typeIndex] = $ns[0].'\\'.$typeTopLevel;
                    }
                }
            }

            $elements[$typeIndex] = self::stripStartBackslash($elements[$typeIndex]);
        }

        return implode('|', $elements);
    }

    public static function rmdirRecursive($dir)
    {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? self::rmdirRecursive("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

}
