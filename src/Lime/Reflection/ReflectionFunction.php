<?php
/*
 * This file is part of Limedocs
 *
 * Copyright (C) Matthias ETIENNE <matthias@etienne.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Doculizr Reflection Function
 *
 * @author Matthias Etienne <matt@allty.com>
 * @copyright (c) 2012, Matthias Etienne
 * @license http://doculizr.allty.com/license MIT
 * @link http://doculizr.allty.com Doculizr Website
 *
 */
namespace Doculizr\Reflection;

use Doculizr\Parser\DoculizrParser;
use Doculizr\Finder\DoculizrFileInfo;
use Doculizr\Core;

/**
 * Reflection class handling functions
 * 
 * This reflection class handles functions, either global or in namespaces.
 * It implements the {IMetaData} interface, extends the {ReflectionFunction}
 * class, and make use of the {TMetaData} trait.
 * 
 */
class DoculizrReflectionFunction extends \ReflectionFunction implements IMetaData {
    
    // use the TMetaData trait
    use TMetaData;
    
    
    protected $fileInfo;

    public function __construct($name, DoculizrFileInfo $fileInfo)
    {
        Core::getLogger()->info("Analysing function $name");
        parent::__construct($name);

        $this->fileInfo = $fileInfo;

        $this->setMetadata(
                DoculizrParser::parseDocComment($this->getDocComment(),
                        $this->fileInfo, $this)
        );
    }

    public function __toString()
    {
        return '[function:'.$this->getName().']';
    }

}