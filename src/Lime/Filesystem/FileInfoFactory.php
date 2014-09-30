<?php
/**
 * This file is part of Limedocs
 *
 * Copyright (C) Matthias ETIENNE <matthias@etienne.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Lime\Filesystem;

/**
 * Factory to create {FileInfo} objects
 *
 * This factory will create {FileInfo} objects and cache them.
 */
class FileInfoFactory
{
    /**
     * FileInfo's holder
     * @var array
     */
    protected static $factoryCache = array();

    /**
     * Factory method used to create and cache {FileInfo} objects
     *
     * @param string $filename Filename
     * @return FileInfo
     */
    public static function factory($filename)
    {
        if (!isset(self::$factoryCache[$filename])) {
            self::$factoryCache[$filename] = new FileInfo($filename);
        }
        return self::$factoryCache[$filename];
    }

}