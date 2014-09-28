<?php
/*
 * This file is part of Limedocs
 *
 * Copyright (C) Matthias ETIENNE <matthias@etienne.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Lime\Renderer;

use Lime\App\App;
use Lime\App\RuntimeParameterAware;
use Lime\App\TRuntimeParameter;
use Lime\Logger\LoggerAwareInterface;
use Lime\Logger\TLogger;
use Lime\Template\TemplateInterface;
use Lime\Common\Utils\FsUtils;

/**
 * Base Renderer class
 */
abstract class Renderer implements RendererInterface, LoggerAwareInterface, RuntimeParameterAware {

    use TLogger;
    use TRuntimeParameter;

    /** @var TemplateInterface template instance */
    protected $template;

    /**
     * Constructor
     *
     * @param TemplateInterface $template TemplateInterface instance
     */
    final public function __construct(TemplateInterface &$template)
    {
        $this->template = $template;
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
            return $this->getOutputDir() . DS;
        }
        if(is_array($part)) {
            $part = implode(DS, $part);
        }
        return $this->getOutputDir() . DS . $part . DS;
    }

    /**
     * Write the documentation files to the filesystem.
     *
     */
    public function prepareFilesystem()
    {
        $this->info('Preparing filesystem');

        $output_dir = $this->getOutputDir();

        if(!is_string($output_dir) || empty($output_dir)) {
            throw new \RuntimeException('Invalid output directory.');
        }

        // clean docs dir
        is_dir($output_dir) &&
            FsUtils::rmdir($output_dir);

        $oldumask = umask(0);

        // recreate docs dir
        $this->info('Creating directory ' . $output_dir);
        mkdir($output_dir, 0777, true);

        umask($oldumask);

        return $this;
    }

    public function getOutputDir()
    {
        return App::getInstance()->getParameter('generate.output');
    }


}