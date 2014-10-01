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

    public function __construct($func_name)
    {
        $this->info("Analysing function $func_name");
        parent::__construct($func_name);

        $this->fileInfo = FileInfoFactory::factory(
            $this->getFileName()
        );

        $this->setMetadata(
            Parser::parseDocComment($this->getDocComment(), $this->fileInfo, $this)
        );
    }

    /**
     * Get associated documentation filename
     *
     * @param string $extension Filename extension
     * @return string Return associated documentation filename
     */
    public function getDocFileName($base_href = '', $extension = 'html')
    {
        if (!$this->isUserDefined()) {
            return 'http://php.net/' . $this->name;
        }

        if($this->inNamespace() === false) {
            $nsPrefix = 'global';
        }else{
            $nsPrefix = str_replace('\\', '/', $this->getNamespaceName());
        }


        return $base_href . $nsPrefix . '/function.' . $this->getName() . '.' . $extension;
    }

    public function __toString()
    {
        return '[function:'.$this->getName().']';
    }

}