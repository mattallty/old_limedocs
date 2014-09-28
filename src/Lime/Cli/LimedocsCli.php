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
        $dirItr = new \RecursiveDirectoryIterator(__DIR__ . '/Command',
            \FilesystemIterator::KEY_AS_PATHNAME |
            \FilesystemIterator::CURRENT_AS_FILEINFO |
            \FilesystemIterator::SKIP_DOTS
        );
        foreach($dirItr as $file) {
            $command = 'Lime\\Cli\\Command\\' . $file->getBasename('.php');
            $this->add(new $command);
        }
    }
}