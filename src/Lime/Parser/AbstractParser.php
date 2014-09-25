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
 * Doculizr Abstract Parser
 *
 * @author Matthias Etienne <matt@allty.com>
 * @copyright (c) 2012, Matthias Etienne
 * @license http://doculizr.allty.com/license MIT
 * @link http://doculizr.allty.com Doculizr Website
 *
 */
namespace Doculizr\Parser;

use Doculizr\Core;
use Doculizr\Finder\IFinder;
use Doculizr\Logger\TLogger;

/**
 * Parser abstract class
 *
 */
abstract class AbstractParser implements IParser {
    
    
    use TLogger;
    
    /**
     * @var \Doculizr\Finder\IFinder Finder instance
     */
    protected $finder;


    /**
     * Constructor
     *
     * @param \Doculizr\Finder\IFinder $finder A IFinder instance
     * @param array $options Options
     */
    final public function __construct(IFinder &$finder)
    {
        $this->finder = &$finder;

        if (($bootstrap = Core::getInstance()->getOption('bootstrap'))) {
            require_once($bootstrap);
        }

        $this->parse();
    }
    
    /**
     * Gets the {IFinder} instance.
     * 
     * @return IFinder Returns a {IFinder} instance.
     */
    final public function getFinder() {
        return $this->finder;
    }

}

?>
