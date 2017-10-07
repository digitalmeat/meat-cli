<?php
namespace Meat\Cli\Console;

use GuzzleHttp\Exception\ClientException;
use Meat\Cli\Helpers\MeatAPI;

class InstallCommand extends MeatCommand
{
    protected $needLogin = false;
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
        $this->askMeatCredentials();
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
    private function askMeatCredentials()
    {
        $this->info('');
        $this->info('Please enter your MEAT credentials');
        $this->info('==========================================');

        $access_token = null;
        while(is_null($access_token)) {
            $user = $this->ask('MEAT Email: ');
            $pass = $this->secret('MEAT Password: ');

            try {
                $access_token = (new MeatAPI())->login($user, $pass);
            } catch (ClientException $exception) {
                $response = json_decode($exception->getResponse()->getBody()->getContents(), true);
                $this->line('<error>' . $response['msg'] .'</error>');
                $access_token = null;
                continue;
            }

            config(['access_token' => $access_token['token']]);
        }
    }
}