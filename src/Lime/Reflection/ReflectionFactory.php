<?php
//------------------------------------------------------------------------------
//
//  This file is part of Doculizr -- The PHP 5 Documentation Generator
//
//  Copyright (C) 2012 Matthias ETIENNE <matt@allty.com>
//
//  Permission is hereby granted, free of charge, to any person obtaining a
//  copy of this software and associated documentation files (the "Software"),
//  to deal in the Software without restriction, including without limitation
//  the rights to use, copy, modify, merge, publish, distribute, sublicense,
//  and/or sell copies of the Software, and to permit persons to whom the
//  Software is furnished to do so, subject to the following conditions:
//
//  The above copyright notice and this permission notice shall be included in
//  all copies or substantial portions of the Software.
//
//  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
//  EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
//  MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
//  IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,
//  DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
//  OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR
//  THE USE OR OTHER DEALINGS IN THE SOFTWARE.
//
//------------------------------------------------------------------------------

/**
 * Doculizr Reflection Factory
 *
 * @author Matthias Etienne <matt@allty.com>
 * @copyright (c) 2012, Matthias Etienne
 * @license http://doculizr.allty.com/license MIT
 * @link http://doculizr.allty.com Doculizr Website
 *
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