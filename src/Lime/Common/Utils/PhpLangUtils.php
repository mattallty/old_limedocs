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

/**
 * Some php-language utils
 */
class PhpLangUtils {

    /**
     * @var array PHP internal functions documentation
     */
    static $quickref = array();

    const QUICKREF_URL = 'https://raw.githubusercontent.com/salathe/phpdoc-base/master/quickref.txt';


    public function __construct()
    {
        $handle = fopen(LIMEDOCS_ROOT_DIR . DS . 'config' . DS . 'quickref.txt', 'r');
        while($line = fgets($handle)) {
            list($command, $description) = explode("-", $line, 2);
            self::$quickref[trim($command)] = trim($description);
        }
    }

    /**
     * Gets a quick refrence, ie description, of a *native* PHP function/method.
     * @param string $function Function or method  name
     * @param string $className Class name in the case of a method.
     * @return string Returns the description {string} or {null} if the function cannot be found.
     */
    public static function getQuickRef($function, $className = '') {
        $lookup = (empty($className)) ? $function : $className.'::'.$function;
        return isset(self::$quickref[$lookup]) ? self::$quickref[$lookup] : null;
    }

    /**
     * Get PHP native types
     *
     * @return string PHP Type, ie 'string', 'bool', 'float', etc.
     */
    public static function getNativeTypes()
    {
        return array(
            'string',
            'bool',
            'boolean',
            'int',
            'void',
            'integer',
            'float',
            'double',
            'array',
            'mixed',
            'object',
            'mixed',
            'callable',
            'resource',
            'stdClass'
        );
    }

    /**
     * Check if given string correponds to a native PHP type
     *
     * @param string $type Type
     * @return boolean returns true if $type is a native PHP type, otherwise false.
     */
    public static function isNativeType($type)
    {
        return in_array(strtolower($type), self::getNativeTypes());
    }

}