<?php

namespace LightWeight\CLI\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;

class InitApp extends Command
{
    protected static $defaultName = 'init';
    protected static $defaultDescription = 'Copy a project skeleton to root';

    public function __construct(
        private string $templatesDir
    ) {
        $this->$templatesDir = $templatesDir;
    }
    protected function configure()
    {
        $this->addArgument("name", InputArgument::REQUIRED, "Directory Root");
    }
    protected function execute(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output)
    {

    }
}
