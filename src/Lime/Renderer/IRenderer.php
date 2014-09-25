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

use Lime\Template\ITemplate;

/**
 * Renderer interface
 *
 */
interface IRenderer {

    /**
     * Render documentation by Namspaces
     */
    const RENDERING_MODE_NAMESPACES = 'ns';

    /**
     * Render documentation by Packages
     */
    const RENDERING_MODE_PACKAGES = 'packages';

    /**
     * Autodetect Render mode
     */
    const RENDERING_MODE_AUTO = 'auto';
    
    /**
     * HTML rendering format
     */
    const RENDER_FORMAT_HTML = 'HTML';
    /**
     * PHP rendering format
     */
    const RENDER_FORMAT_PHP = 'PHP';
    /**
     * PDF rendering format
     */
    const RENDER_FORMAT_PDF = 'PDF';
    
    
    public function __construct(ITemplate &$template);
    
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
    public function getFilesExtension();
    public function getRenderFormat();
    public function getAvailableRenderingModes();
    /**
     * Build files & directories
     * 
     * @return void
     */
    public function buildTree();
    public function getTemplate();
}

?>
