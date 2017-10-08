<?php
namespace Meat\Cli\Console;

use GuzzleHttp\Exception\ClientException;
use Meat\Cli\Helpers\MeatAPI;

/**
 * Class InstallCommand
 * @package Meat\Cli\Console
 */
class InstallCommand extends MeatCommand
{
    /**
     * @var bool
     */
    protected $needLogin = false;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This is the installation process. Create your personal .meat file once so you can easily run your commands afterwards';

    /**
     * @var array
     */
    protected $configuration = [];

    /**
     *
     */
    public function handle() {
        $this->printBigMessage('MEAT CLI Installation ✌️');


        if ($this->configurationHandler->isInstalled()) {
            if (!$this->confirm('MEAT Cli is already installed. Do you want to update your ~/.meat file?')) {
                return;
            }
        }
        $this->askDatabaseInformation();
        $this->askMeatCredentials();
        $this->finishMessage();

    }

    /**
     *
     */
    public function askDatabaseInformation()
    {
        $this->info('');
        $this->info('Please enter your local database credentials');
        $this->info('==========================================');

        $configuration = [
            'db_user' => $this->ask('Database User', 'root'),
            'db_pass' => $this->secretAllowEmpty('Database Password'),
            'db_host' => $this->ask('Database Host', 'localhost'),
        ];

        $this->configurationHandler->save($configuration);
    }

    /**
     *
     */
    private function finishMessage()
    {
        $this->info('');
        $this->info('==========================================');
        $this->info('Installation complete!');
        $this->info('==========================================');
    }

    /**
     * Promt user for meat cloud credentials
     */
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
                $access_token = $this->api->login($user, $pass);
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