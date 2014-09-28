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

use Lime\App\TRuntimeParameter;
use Lime\Cli\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Lime\App\App;
use Lime\Template\Utils;

/**
 * Generates documentation
 *
 * This class represents the cli-command which generates documentation
 */
class Generate extends Command
{

    use TRuntimeParameter;

    /**
     * Configure command
     *
     * My configure long desc
     *
     * @param InputOption $input my input data
     */
    protected function configure(InputOption $input = null)
    {
        $this
            ->setName('generate')
            ->setDescription('Generate documentation')
            ->addArgument(
                'source-dir', InputArgument::REQUIRED,
                'Source code directory'
            )
            ->addOption(
                'output', 'o', InputOption::VALUE_REQUIRED,
                'Directory zhere to generate the docu;entation. If not specified, the default is "docs".',
                './docs'
            )
            ->addOption(
                'bootstrap', 'b', InputOption::VALUE_REQUIRED,
                'Boostrap file used to autoload/include your classes/dependencies.'
            )
            ->addOption(
                'introduction', null, InputOption::VALUE_REQUIRED,
                'Use the specified file as the introduction page for the generated documentation.'
            )
            ->addOption(
                'config', 'c', InputOption::VALUE_REQUIRED,
                'Configuration file where to take parameters from.',
                null
            )
            ->addOption(
                'title', 't', InputOption::VALUE_REQUIRED,
                'Documentation title.'
            )
            ->addOption(
                'ignore', 'i', InputOption::VALUE_REQUIRED,
                'Ignore directories.'
            )
            ->addOption(
                'finder-cache-duration', null, InputOption::VALUE_REQUIRED,
                'Finder cache duration. Set to 0 to disable finder caching.'
            )
            ->addOption(
                'no-follow', '', InputOption::VALUE_NONE,
                "Don't follow symlinks."
            )
            ->addOption(
                'no-recursive', null, InputOption::VALUE_NONE,
                "Don't browse source recursively.", null
            )
            ->addOption(
                'inception', null, InputOption::VALUE_NONE,
                "Inception mode for Limedocs developers only (when limedocs is used to generate its own doc)."
            )
            ->addOption(
                'export-xml', null, InputOption::VALUE_REQUIRED,
                'Destination directory where xml documentation will be exported.'
            )
            ->addOption(
                'html-tags', null, InputOption::VALUE_REQUIRED,
                'Allowed HTML tags in documentation.'
            )
            ->addOption(
                'without-template-cache', null, InputOption::VALUE_NONE,
                'Disable Twig templates caching (for template developers).'
            );
    }

    /**
     * Own error handler set up to display errors through console
     * @param int $errno Error number
     * @param string $errstr Error description
     * @param string $errfile Filename
     * @param string $errline Line
     */
    public function errorHandler($errno, $errstr, $errfile, $errline)
    {
        App::getInstance()->get('logger')->error("ERROR : [$errno] $errstr in file $errfile line $errline");
        exit(1);
    }

    /**
     * Handles script shutdown due to error happening or not
     */
    public function handleShutdown()
    {
        $error = error_get_last();
        if ($error !== null) {
            if ($error['type'] === \E_ERROR) {
                App::getInstance()->get('logger')->error(
                    "Doculizr Error : ", $error['message'],
                    ' in file ', $error['file'], ' on line ', $error['line']
                );
                exit(1);
            }
        }
    }

    /**
     * This method is called when the `generate` command is actually run.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input Input Interface
     * @param \Symfony\Component\Console\Output\OutputInterface $output Output Interface
     * @return void
     */
    protected function exec(InputInterface $input)
    {
        $app = App::getInstance();
        $logger = $app->get('logger');
        $options = array_filter($input->getOptions());

        foreach ($options as $optName => $optVal) {
            $this->setParameter('generate.' . $optName, $optVal);
        }

        $this->setParameter(
            'generate.source-dir',
            realpath($input->getArgument('source-dir'))
        );

        $logger->info("Initializing Limedocs...");

        set_error_handler(array($this, 'errorHandler'));
        register_shutdown_function(array($this, 'handleShutdown'));

        $app->get('renderer')->render();
    }

}