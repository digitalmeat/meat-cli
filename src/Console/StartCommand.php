<?php

namespace Meat\Cli\Console;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;

class StartCommand extends SymfonyCommand
{
    use Command;


    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $info = pathinfo(getcwd());
        $this->setName('create')
            ->setDescription('Start a new project based on a predefined template')
            ->addArgument(
            'project-code',
            InputArgument::OPTIONAL,
            'themosis | laravel | magento | blank',
            'blank');

    }

    public function getRepoUrlByType($type)
    {
        $repos = [
            'themosis'  => 'git@github.com:digitalmeat/themosis.git'
        ];

        return $repos[$type];
    }
}