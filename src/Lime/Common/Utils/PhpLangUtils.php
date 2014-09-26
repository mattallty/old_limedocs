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