<?php

namespace Meat\Cli\Console;
use Meat\Cli\Helpers\ConfigurationHandler;
use Meat\Cli\Helpers\MeatAPI;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

/**
 * Class MeatCommand
 * @package Meat\Cli\Console
 */
class MeatCommand extends SymfonyCommand
{
    use Command;

    /**
     * @var bool
     */
    protected $needLogin = true;

    /**
     * @var ConfigurationHandler
     */
    protected $configurationHandler;

    /**
     * @var MeatAPI
     */
    protected $api;

    /**
     * InstallCommand constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->configurationHandler = new ConfigurationHandler();
        $this->api = new MeatAPI();
    }

    /**
     * @param $folder
     * @return $this
     * @throws \Exception
     */
    protected function changeWorkingDirectory($folder)
    {
        if (!chdir($folder)) {
            throw new \Exception('Could not change the directory to ' . $folder);
        }

        return $this;
    }
}