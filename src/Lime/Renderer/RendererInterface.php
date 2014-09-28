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
 * Renderer Interface
 *
 * @author Matthias Etienne <matt@allty.com>
 * @copyright (c) 2012, Matthias Etienne
 * @license http://doculizr.allty.com/license MIT
 * @link http://doculizr.allty.com Doculizr Website
 *
 */
namespace Lime\Renderer;

use Lime\Template\TemplateInterface;

/**
 * Renderer interface
 *
 */
interface RendererInterface {

    public function __construct(TemplateInterface &$template);

    /**
     * Initializer
     *
     * This method must return $this just like a constructor.
     */
    public function init();
    /**
     * Render documentation
     *
     * @return void
     */
    public function render();
    /**
     * Get files extension for generated documentation
     *
     * @return string
     */
    public function getFileExt();

    /**
     * Build files & directories
     *
     * @return void
     */
    public function prepareFilesystem();
    public function getTemplate();

    public function getOutputDir();
}
