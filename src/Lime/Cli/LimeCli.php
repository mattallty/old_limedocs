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
use Lime\Core;

class LimeCli extends Application
{
    public function __construct()
    {
        parent::__construct('Limedocs CLI', Core::VERSION);

        $this->setCatchExceptions(false);

        foreach (glob(__DIR__ . '/Command/*.php') as $file) {
            $command = 'Lime\\Cli\\Command\\' . basename($file, '.php');
            $this->add(new $command);
        }
    }

    public function exceptionHandler($exception)
    {
        Core::getLogger()->error($exception->getMessage());
    }

    public function run()
    {
        set_exception_handler(array($this, 'exceptionHandler'));
        parent::run();
    }
}