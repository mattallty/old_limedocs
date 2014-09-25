<?php
/**
 * This file is part of Limedocs
 *
 * Copyright (C) Matthias ETIENNE <matthias@etienne.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Doculizr\Reflection;

/**
 * Reflection Factory
 *
 * @package Doculizr
 * @subpackage Reflection
 */
class DoculizrReflectionFactory {
    protected static $factoryCache = array();

    public static function &factory(/* $className, $arg ... $argN */)
    {
        $args = func_get_args();
        $className = array_shift($args);
        $key = md5($className . json_encode($args));
        
        if (!isset(self::$factoryCache[$key])) {
            $class = new \ReflectionClass($className);
            self::$factoryCache[$key] = $class->newInstanceArgs($args);
        }

        return self::$factoryCache[$key];
    }

    public static function register(/* $object, $className, $arg ... $argN */)
    {
        $args = func_get_args();
        $object = array_shift($args);
        $className = array_shift($args);
        $key = md5($className . json_encode($args));
        self::$factoryCache[$key] = $object;
    }

}