<?php
/**
 * This file is part of Limedocs
 *
 * Copyright (C) Matthias ETIENNE <matthias@etienne.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lime;

if ($tz = \ini_get('date.timezone') && !empty($tz)) {
    define('LIMEDOCS_DEFAULT_TIMEZONE', $tz);
}else{
    define('LIMEDOCS_DEFAULT_TIMEZONE', 'UTC');
}

define('DS', DIRECTORY_SEPARATOR);
define('LIMEDOCS_ROOT_DIR', \realpath(\dirname(__FILE__)));

$_ENV['LIMEDOCS_TIMEZONE'] = $_ENV['LIMEDOCS_TIMEZONE'] ?: LIMEDOCS_DEFAULT_TIMEZONE;
$_ENV['LIMEDOCS_DATA_DIR'] = $_ENV['LIMEDOCS_DATA_DIR'] ?: LIMEDOCS_ROOT_DIR . DS . 'data';

date_default_timezone_set(LIMEDOCS_TIMEZONE);

function includeIfExists($file)
{
    return file_exists($file) ? include $file : false;
}

if ((!$loader = includeIfExists(__DIR__.'/../vendor/autoload.php')) && (!$loader = includeIfExists(__DIR__.'/../../../autoload.php'))) {
    echo 'You must set up the project dependencies, run the following commands:'.PHP_EOL.
        'curl -sS https://getcomposer.org/installer | php'.PHP_EOL.
        'php composer.phar install'.PHP_EOL;
    exit(1);
}

return $loader;