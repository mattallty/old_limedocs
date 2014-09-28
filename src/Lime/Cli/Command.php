<?php
namespace Lime\Cli;

use Lime\App\App;
use Lime\App\RuntimeParameterAware;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use \Symfony\Component\Console\Command\Command as SymfonyCommand;


abstract class Command extends SymfonyCommand implements RuntimeParameterAware {

    private function setupLogger(OutputInterface $output)
    {
        $logger = new ConsoleLogger($output);
        $app = App::getInstance();
        $app->get('logger')->setLogger($logger);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setupLogger($output);
        $this->exec($input);
    }

    abstract protected function exec(InputInterface $input);

}
