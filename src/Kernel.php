<?php

namespace Meat\Cli;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;
use Meat\Cli\Console\BuildStagingCommand;
use Meat\Cli\Console\CompileAssetsCommand;
use Meat\Cli\Console\CreateBitbucketRepositoryCommand;
use Meat\Cli\Console\CreateCommand;
use Meat\Cli\Console\DeployNowCommand;
use Meat\Cli\Console\FindStagingCommand;
use Meat\Cli\Console\InstallCommand;
use Meat\Cli\Console\LoginCommand;
use Meat\Cli\Console\MountCommand;
use Meat\Cli\Console\ProjectInfoCommand;
use Meat\Cli\Console\SelfUpdateCommand;
use Meat\Cli\Console\SSHCommand;
use Meat\Cli\Console\WatchAssetsCommand;
use Meat\Cli\Console\WhoAmICommand;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        CreateBitbucketRepositoryCommand::class,
        WhoAmICommand::class,
        ProjectInfoCommand::class,
        MountCommand::class,
        InstallCommand::class,
        CreateCommand::class,
        WatchAssetsCommand::class,
        CompileAssetsCommand::class,
        BuildStagingCommand::class,
        DeployNowCommand::class,
        FindStagingCommand::class,
        SSHCommand::class,
        LoginCommand::class,
        SelfUpdateCommand::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {

    }


}