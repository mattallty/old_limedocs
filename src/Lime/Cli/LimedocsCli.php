<?php
/**
 * This file is part of Limedocs
 *
 * Copyright (C) Matthias ETIENNE <matthias@etienne.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Lime\Cli;

use Symfony\Component\Console\Application;

class LimedocsCli extends Application
{
    public function __construct()
    {
        parent::__construct('Limedocs', '@package_version@');
        foreach (glob(__DIR__ . '/Command/*.php') as $file) {
            $command = 'Lime\\Cli\\Command\\' . basename($file, '.php');
            $this->add(new $command);
        }
    }
}