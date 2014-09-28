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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Lime\App\TRuntimeParameter;
use Lime\Cli\Command;
use Lime\App\App;

/**
 * Class handling configuration display
 */
class ConfigShow extends Command
{


    use TRuntimeParameter;

    /**
     * Configure command
     *
     * @param InputOption $input my input data
     */
    protected function configure(InputOption $input = null)
    {
        $this
                ->setName('config:show')
                ->setDescription('Show configuration variables.');
    }

    protected function exec(InputInterface $input)
    {
        $app = App::getInstance();
        $logger = $app->get('logger');

        var_dump(get_defined_functions());

        foreach (array('foo' => 'bar', 'far' => 'bim') as $key => $value) {
            $logger->info("<comment>$key : $value</comment>");
        }
    }

}