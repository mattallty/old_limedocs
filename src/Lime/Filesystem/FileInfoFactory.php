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
 * FileInfo Factory
 *
 * @author Matthias Etienne <matt@allty.com>
 * @copyright (c) 2012, Matthias Etienne
 * @license http://doculizr.allty.com/license MIT
 * @link http://doculizr.allty.com Doculizr Website
 *
 */
class FileInfoFactory {
    /**
     * FileInfo's holder
     * @var array
     */
    protected static $factoryCache = array();

    /**
     * Factory method used to create and cache {DoculizrFileInfo} objects 
     * 
     * @param string $filename Filename
     * @return DoculizrFileInfo
     */
    public static function factory($filename)
    {
        if (!isset(self::$factoryCache[$filename])) {
            self::$factoryCache[$filename] = new FileInfo($filename);
        }
        return self::$factoryCache[$filename];
    }

}