<?php
/*
 * This file is part of Limedocs
 *
 * Copyright (C) Matthias ETIENNE <matthias@etienne.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Lime\Reflection;

use Lime\Logger\LoggerAwareInterface;
use Lime\Logger\TLogger;
use Lime\Parser\Parser;
use Lime\Filesystem\FileInfoFactory;
use Lime\Core;

/**
 *
 *
 */
class ReflectionNamespace implements IMetaData, LoggerAwareInterface
{

    // use the TMetaData trait
    use TMetaData;

    use TLogger;

    public $name;

    /**
     * Creates a new ReflectionNamespace
     *
     * @param string $namespace namespace name
     */
    public function __construct($namespace)
    {
        $this->name = $namespace;
    }

    public function getName() {
        return $this->name;
    }

}