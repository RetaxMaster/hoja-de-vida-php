<?php

// La estructira completa de esta clase se encuentra en la documentación de Symfony Commands: https://symfony.com/doc/current/console.html

namespace App\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HelloWorldCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:hello-world';

    protected function configure() {
        $this->addArgument('name', InputArgument::REQUIRED, 'Name');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $output->writeln("Hello {$input->getArgument('name')}");
        return Command::SUCCESS;

    }
}

?>