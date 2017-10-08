<?php

namespace Meat\Cli\Providers;

use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Meat\Cli\Helpers\MeatAPI;

/**
 * Class AppServiceProvider
 * @package Meat\Cli\Providers
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Boot
     */
    public function boot()
    {
        Event::listen(CommandStarting::class, function ($foo) {
            //@TODO: (new MeatAPI())->notifyCommandStarted() if user accepted to do so;
        });
    }

    /**
     * Register
     */
    public function register()
    {

    }
}