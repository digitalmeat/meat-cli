<?php
namespace Meat\Cli\Console;

use GuzzleHttp\Exception\ClientException;
use Meat\Cli\Helpers\MeatAPI;
use Meat\Cli\Traits\canLogin;

/**
 * Class InstallCommand
 * @package Meat\Cli\Console
 */
class LoginCommand extends MeatCommand
{
    use canLogin;

    /**
     * @var bool
     */
    protected $needLogin = false;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'login';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Login into Meat Cloud and save the access token. ';

    /**
     * @var array
     */
    protected $configuration = [];

    /**
     *
     */
    public function handle() {
        $this->checkIfAccessTokenWorks();

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
        $this->info('Loged in!');
        $this->info('==========================================');
    }
    protected function checkIfAccessTokenWorks()
    {
        $works = false;
        try {
            $me = $this->api->me();
            $works = true;
        } catch (\Exception $e) {

        }

        if ($works) {
            $this->warn('You current access token appers to work just fine. ');
        }
    }

}