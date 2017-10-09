<?php

namespace Meat\Cli;

use Laravel\Lumen\Application;

class MeatApplication extends Application {
    /**
     * Prepare the application to execute a console command.
     *
     * @param  bool  $aliases
     * @return void
     */
    public function prepareForConsoleCommand($aliases = true)
    {
        $this->withFacades($aliases);

        $this->make('cache');
        $this->make('queue');

        $this->configure('database');

        //$this->register('Illuminate\Database\MigrationServiceProvider');
        //$this->register('Laravel\Lumen\Console\ConsoleServiceProvider');
    }
}