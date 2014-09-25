<?php
//------------------------------------------------------------------------------
//
//  This file is part of Doculizr -- The PHP 5 Documentation Generator
//
//  Copyright (C) 2012 Matthias ETIENNE <matt@allty.com>
//
//  Permission is hereby granted, free of charge, to any person obtaining a
//  copy of this software and associated documentation files (the "Software"),
//  to deal in the Software without restriction, including without limitation
//  the rights to use, copy, modify, merge, publish, distribute, sublicense,
//  and/or sell copies of the Software, and to permit persons to whom the
//  Software is furnished to do so, subject to the following conditions:
//
//  The above copyright notice and this permission notice shall be included in
//  all copies or substantial portions of the Software.
//
//  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
//  EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
//  MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
//  IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,
//  DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
//  OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR
//  THE USE OR OTHER DEALINGS IN THE SOFTWARE.
//
//------------------------------------------------------------------------------

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