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
use Lime\Filesystem\FileInfo;
use Lime\Core;

/**
 * Reflection class handling functions
 *
 * This reflection class handles functions, either global or in namespaces.
 * It implements the {IMetaData} interface, extends the {ReflectionFunction}
 * class, and make use of the {TMetaData} trait.
 *
 */
class ReflectionFunction extends \ReflectionFunction implements IMetaData, LoggerAwareInterface
{

    // use the TMetaData trait
    use TMetaData;

    use TLogger;

    /**
     * @var \Lime\Filesystem\FileInfo
     */
    protected $fileInfo;

    public function __construct($name, FileInfo $fileInfo)
    {
        $this->info("Analysing function $name");
        parent::__construct($name);

        $this->fileInfo = $fileInfo;

        $this->setMetadata(
            Parser::parseDocComment($this->getDocComment(), $this->fileInfo, $this)
        );
    }

    public function __toString()
    {
        return '[function:'.$this->getName().']';
    }

}