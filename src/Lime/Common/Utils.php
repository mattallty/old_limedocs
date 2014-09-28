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








}
