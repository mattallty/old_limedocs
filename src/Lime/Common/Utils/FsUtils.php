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
 * Some filesystem-related utils
 */
class FsUtils {

    public static function rmdir($dir)
    {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            if(is_dir("$dir/$file")) {
                self::rmdir("$dir/$file");
            } else{
                unlink("$dir/$file");
            }
        }
        return rmdir($dir);
    }

    public static function cpdir($source_dir, $dest_dir) {

        $source_dir = realpath($source_dir);
        $src_len = strlen($source_dir);

        if(!is_dir($dest_dir)) {
            mkdir($dest_dir, 0777, true);
        }
        $directory = new \RecursiveDirectoryIterator($source_dir);
        foreach (new \RecursiveIteratorIterator($directory) as $filename => $current) {
            $src = $current->getPathName();
            $path = substr($src, $src_len);
            $dest = $dest_dir . $path;
            $dir = dirname($dest);

            if(!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            if(!copy($src, $dest)) {
                throw new \Exception(error_get_last());
                return false;
            }
        }
        return true;
    }

}
