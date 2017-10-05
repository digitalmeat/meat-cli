<?php
namespace Meat\Cli\Console;

class InstallCommand extends MeatCommand
{

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('init')->setDescription('');
    }

    protected function fire() {
        $this->line('');
        $this->info('==========================================');
        $this->info('MEAT CLI Installation');
        $this->info('==========================================');
        $this->line('');


        if ($this->configurationHandler->isInstalled()) {
            if (!$this->confirm('Ya existe un archivo ~/.meat. ¿Desea actualizarlo? (Y/n)')) {
                return;
            }
        }
        $this->askDatabaseInformation();
        $this->finishMessage();

    }

    public function askDatabaseInformation()
    {
        $this->info('');
        $this->info('Please enter your local database credentials');
        $this->info('==========================================');

        $configuration = [
            'db_user' => $this->ask('Database User (root): ', 'root'),
            'db_pass' => $this->secret('Database Password (--empty--): '),
            'db_host' => $this->ask('Database Host (localhost): ', 'localhost'),
        ];

        $this->configurationHandler->save($configuration);
    }

    private function finishMessage()
    {
        $this->info('');
        $this->info('==========================================');
        $this->info('Installation complete!');
        $this->info('==========================================');
    }
}