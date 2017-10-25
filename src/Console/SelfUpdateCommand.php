<?php

namespace Meat\Cli\Console;


class SelfUpdateCommand extends MeatCommand
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'self-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update meat-cli';

    /**
     * Execute the command.
     * @return void
     * @throws \Exception
     */
    public function handle() {
        $this->info('Updating meat-cli...');
        passthru($this->getInstallationCommand());
    }

    protected function getInstallationCommand()
    {
        return 'curl -s https://api.meat.cl/install.sh | sh -';
    }
}