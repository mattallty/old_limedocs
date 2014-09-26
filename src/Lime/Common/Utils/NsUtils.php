<?php
/**
 * This file is part of Limedocs
 *
 * Copyright (C) Matthias ETIENNE <matthias@etienne.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lime\Common\Utils;

use Lime\Reflection\ReflectionFactory;
use Lime\Filesystem\FileInfo;
use Michelf\MarkdownExtra;


/**
 * Some Namespace-related utils
 */
class NsUtils {

    /**
     * Strip the starting basckslash from a namespaced string, or from an array of strings.
     *
     * @param mixed $elem A string or an array of strings
     * @return string
     */
    public static function stripLeadingBackslash($elem = '')
    {
        if (is_array($elem)) {
            return array_map(array(__CLASS__, 'stripLeadingBackslash'), $elem);
        }
        return ltrim($elem, '\\');
    }

    /**
     * Replace namespace backslashes by forwardslashes
     *
     * @param string $namespace Namespace string
     * @return string
     */
    public static function nsToPath($namespace) {
        return strtr($namespace, '\\', '/');
    }

    public static function aerateNs($namespace) {
        return str_replace('\\', '<span class="ns">\\</span>', $namespace);
    }

    /**
     * Get the short name from a namespaced name
     *
     * @param $elem
     * @return mixed
     */
    public static function getElementShortName($elem) {
        $parts = explode('\\', $elem);
        return array_pop($parts);
    }


    /**
     * Scope a string by prefixing it depending on environment, ie namespace,
     * imports, class, etc.
     *
     * @param string $element Element string
     * @return string scoped element
     */
    public static function scopeElement($element, FileInfo $fileInfo, $refObject)
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


    public static function getNamespaceTopLevel($namespace)
    {
        return strtok($namespace, '\\');
    }

    /**
     * Checks if a class name is fully scoped.
     *
     * @param string $element Element, ie class or interface name.
     * @return string Returns the class name prepended by its namespace if needed.
     */
    public static function isFullyScoped($element)
    {
        return substr($element, 0, 1) === '\\';
    }
}
