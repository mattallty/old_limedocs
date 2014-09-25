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


namespace Doculizr\Renderer;

use Doculizr\Options\IOptionsAware;
use Doculizr\Options\TOptions;
use Doculizr\Parser\IParserAware;
use Doculizr\Parser\TParser;
use Doculizr\Template\ITemplate;
use Doculizr\Core;

/**
 * Renderer abstract class
 *
 * @author Matthias Etienne <matt@allty.com>
 * @copyright (c) 2012, Matthias Etienne
 * @license http://doculizr.allty.com/license MIT
 * @link http://doculizr.allty.com Doculizr Website
 *
 */
abstract class AbstractRenderer implements IRenderer, IOptionsAware, IParserAware {
    
    use TOptions, TParser;
    
    
    /** @var ITemplate ITemplate instance */
    protected $template;

    /**
     * Constructor
     * 
     * @param ITemplate $template ITemplate instance
     */
    final public function __construct(ITemplate &$template)
    {
        $this->template = $template;
    }
    
    /**
     * Gets the rendering mode
     * 
     * @return string Returns rendering mode used
     */
    protected function getMode() {
        return $this->options['render-mode'];
    }
    
    /**
     * Get available rendering modes
     * 
     * @return array List of available rendering modes
     */
    public function getAvailableRenderingModes() {
        return array(
                    self::RENDERING_MODE_NAMESPACES,
                    self::RENDERING_MODE_PACKAGES,
                    self::RENDERING_MODE_AUTO
                );
    }
    
    /**
     * Gets the template instance
     * @return ITemplate
     */
    final public function getTemplate() {
        return $this->template;
    }
   
    /**
     * Gets documentation path
     * 
     * @param string $part Documentation part
     * @return string Path
     */
    public function getDocPath($part = null) {
        if($part === null) {
            return $this->options['destination'] . DS;
        }
        if(is_array($part)) {
            $part = implode(DS, $part);
        }
        return $this->options['destination'] . DS . $part . DS;
    }
    
    /**
     * Gets the logger instance
     * 
     * @return \Doculizr\Logger\ILogger ILogger instance
     */
    protected function getLogger() {
        return Core::getInstance()->getLogger();
    }


    /**
     * Write the documentation files to the filesystem.
     * 
     */
    public function buildTree()
    {
        
        $logger = Core::getInstance()->getLogger();
        $logger->info('Builing file tree...');
        
        if(!is_string($this->options['destination']) || 
                empty($this->options['destination'])) {
            throw new \RuntimeException('Invalid destination dir.');
        }
        
        // clean docs dir
        is_dir($this->options['destination']) && 
            \Doculizr\Utils\DoculizrUtils::rmdirRecursive(
                    $this->options['destination']
            );

        $oldumask = umask(0);

        // recreate
        $logger->info('Creating directory ' . $this->options['destination']);
        mkdir($this->options['destination'], 0777, true);

        // generate index
        $template_name = $this->getTemplate()->getName();
        $template_version = $this->getTemplate()->getVersion();
        
        $this->viewsPath = DOCULIZR_DATA_DIR . DS . 'templates' . DS . 
                $template_name . DS . $template_version . DS . 'template' . DS;

        umask($oldumask);
        
        return $this;
    }

}