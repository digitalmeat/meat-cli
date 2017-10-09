<?php

namespace Meat\Cli\Traits;

trait canLogin
{
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