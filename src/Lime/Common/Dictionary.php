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

class Dictionary
{
    
    static $links = array();
    static $definitions = array();
    
    public static function addDefinition(&$obj)
    {
        self::$definitions[spl_object_hash($obj)] =& $obj;
    }
    
    public static function &getDefinition($obj)
    {
        $hash = spl_object_hash($obj);
        return isset(self::$definitions[$hash]) ? self::$definitions[$hash] : null;
    }
    
    public static function link($obj1, $obj2)
    {
        $hash1 = spl_object_hash($obj1);
        $hash2 = spl_object_hash($obj2);
        if (!isset(self::$links[$obj1])) {
            self::$links[$obj1] = $obj2;
        }
    }
    
}