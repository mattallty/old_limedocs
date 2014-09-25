<?php
/**
 * This file is part of Limedocs
 *
 * Copyright (C) Matthias ETIENNE <matthias@etienne.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Lime\Cli\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Lime\Core;
use Lime\Finder\DoculizrFinder;
use Lime\Parser\DoculizrParser;
use Lime\Renderer\HtmlRenderer;
use Lime\Template\Utils;
use Lime\Logger\TLogger;

/**
 * Generates documentation
 * 
 * This class represents the cli-command which generates documentation
 */
class GenerateDoc extends Command {
    
    use TLogger;
    
    public function __construct($name = null)
    {
        parent::__construct($name);
    }

    /**
     * Configure command
     *
     * My configure long desc
     *
     * @param InputOption $input my input data
     */
    protected function configure(InputOption $input = null)
    {

        $baseOpts = Core::getBaseOptions();

        $this
                ->setName('generate')
                ->setDescription('Generate documentation')
                ->addArgument(
                        'source', InputArgument::REQUIRED,
                        'Source code directory'
                )
                ->addOption(
                        'destination', 'd', InputOption::VALUE_OPTIONAL,
                        'Destination directory where documentation will be written.',
                        $baseOpts->destination
                )
                ->addOption(
                        'bootstrap', 'b', InputOption::VALUE_OPTIONAL,
                        'Boostrap file used to autoload/include your classes/dependencies.',
                        $baseOpts->bootstrap
                )
                ->addOption(
                        'config', 'c', InputOption::VALUE_OPTIONAL,
                        'Configuration file where to take options from.',
                        null
                )
                ->addOption(
                        'title', 't', InputOption::VALUE_OPTIONAL,
                        'Documentation title.', $baseOpts->title
                )
                ->addOption(
                        'ignore', 'i', InputOption::VALUE_OPTIONAL,
                        'Ignore directories.', $baseOpts->ignore
                )
                ->addOption(
                        'finder-cache-duration', null, InputOption::VALUE_OPTIONAL,
                        'Finder cache duration. Set to 0 to disable finder caching.', $baseOpts->{'finder-cache-duration'}
                )
                ->addOption(
                        'no-follow', '', InputOption::VALUE_NONE,
                        "Don't follow symlinks.", null
                )
                ->addOption(
                        'no-recursive', null, InputOption::VALUE_NONE,
                        "Don't browse source recursively.", null
                )
                ->addOption(
                        'open', null, InputOption::VALUE_NONE,
                        "Open index after generating documentation.", null
                )
                ->addOption(
                        'export-xml', null, InputOption::VALUE_OPTIONAL,
                        'Destination directory where xml documentation will be exported.',
                        $baseOpts->{'export-xml'}
                )
                ->addOption(
                        'html-tags', null, InputOption::VALUE_OPTIONAL,
                        'Allowed HTML tags in documentation.',
                        $baseOpts->{'html-tags'}
                )
                ->addOption(
                        'no-tpl-cache', null, InputOption::VALUE_OPTIONAL,
                        'Disable Twig templates caching (for template developers).',
                        $baseOpts->{'no-tpl-cache'}
        );
    }
    
    /**
     * Own error handler set up to display errors through console
     * @param int $errno Error number
     * @param string $errstr Error description
     * @param string $errfile Filename
     * @param string $errline Line
     */
    public function errorHandler($errno, $errstr, $errfile, $errline) {
        $this->getLogger()->error("ERROR : [$errno] $errstr in file $errfile line $errline");
        exit(1);
    }
    
    /**
     * Handles script shutdown due to error happening or not
     */
    public function handleShutdown() {
        $error = error_get_last();
        if($error !== NULL){
            if($error['type'] === \E_ERROR) {
                $this->getLogger()->error("Doculizr Error : ", $error['message'] ,
                        ' in file ', $error['file'] ,' on line ', $error['line']);
                exit(1);
            }
        }
    }

    /**
     * This method is called when the `generate` command is actually run.
     * @param \Symfony\Component\Console\Input\InputInterface $input Input Interface 
     * @param \Symfony\Component\Console\Output\OutputInterface $output Output Interface 
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //error_reporting(0);
        Core::getInstance()->setOptions($input->getOptions(), true);

        $this->getLogger()->info("Initializing Doculizr...");
        
        set_error_handler(array($this, 'errorHandler'));
        register_shutdown_function(array($this, 'handleShutdown'));
        
        
        $source = realpath($input->getArgument('source'));
        
        
        
        $finder = new DoculizrFinder($source);
        $parser = new DoculizrParser($finder);

        //$xml = new XML();
        //$xml->export($finder, '/Users/matt/Desktop/export.xml');
        $options = Core::getInstance()->getOption();
        
        // template
        $templateObject = Utils::getTemplateObject($options['template']);
        
        $renderer = new HtmlRenderer($templateObject);
        $renderer   ->setParser($parser)
                    ->setOptions($options)
                    ->init()
                    ->render();
    }

}