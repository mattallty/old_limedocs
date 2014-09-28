<?php
/**
 * This file is part of Limedocs
 *
 * Copyright (C) Matthias ETIENNE <matthias@etienne.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if(defined('LIMEDOCS_BOOTSTRAPPED')) {
    return; // Already bootstrapped
}

$tz = ini_get('date.timezone');

if (!$tz or empty($tz)) {
    date_default_timezone_set('UTC');
}

define('DS', DIRECTORY_SEPARATOR);
define('LIMEDOCS_BOOTSTRAPPED', true);

if(!function_exists('limedocs_includeIfExists')) {
    function limedocs_includeIfExists($file)
    {
        return file_exists($file) ? include $file : false;
    }
}

if ((!$loader = limedocs_includeIfExists(__DIR__.'/../vendor/autoload.php'))
    && (!$loader = limedocs_includeIfExists(__DIR__.'/../../../autoload.php'))) {
    echo 'You must set up the project dependencies, run the following commands:'.PHP_EOL.
        'curl -sS https://getcomposer.org/installer | php'.PHP_EOL.
        'php composer.phar install'.PHP_EOL;
    exit(1);
}

return $loader;