<?php

namespace Meat\Cli\Console;
use Meat\Cli\Helpers\ConfigurationHandler;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class MeatCommand extends SymfonyCommand
{
    use Command;

    protected $configurationHandler;
    /**
     * InstallCommand constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->configurationHandler = new ConfigurationHandler();
    }
}