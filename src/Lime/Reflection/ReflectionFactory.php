<?php
/**
 * This file is part of Limedocs
 *
 * Copyright (C) Matthias ETIENNE <matthias@etienne.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Lime\Reflection;

use Lime\Logger\TLogger;
use Lime\App\App;

/**
 * Reflection Factory
 *
 */
class ReflectionFactory
{

    protected static $factoryCache = array();

    public static function &factory()
    {
        $args = func_get_args();
        $className = array_shift($args);
        $logger = App::getInstance()->get('logger');

        $key = $className . '|' . implode('.', $args);

        $logger->warning(__METHOD__ . ' with key '.$key);


        if (!isset(self::$factoryCache[$key])) {
            $class = new \ReflectionClass($className);
            $logger->warning(__METHOD__ . ' instanciate ' . $className . ' with '.json_encode($args));
            self::$factoryCache[$key] = $class->newInstanceArgs($args);
        }else{
            $logger->warning(__METHOD__ . ' take cache for key '.$key);
        }

        return self::$factoryCache[$key];
    }
}