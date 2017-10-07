<?php

namespace Meat\Cli\Console;


class WhoAmICommand extends MeatCommand
{

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $info = pathinfo(getcwd());
        $this->setName('whoami')
            ->setDescription('Get my user information from the api');

    }

    /**
     * Execute the command.
     * @return void
     * @throws \Exception
     */
    protected function fire() {
        $user = $this->api->me();
        if (!$user) {
            $this->error('I don\'t know who you are. Access token is invalid or it is not set.');
            $this->info('You can refresh the access token running  "meat init" command ');
            return;
        }
        $this->info('Access token working as expected. You are '. $user['name'] . '.');
        $this->info('');
        $this->info('ID: ' . $user['id']);
        $this->info('Nombre: ' . $user['name']);
        $this->info('Email: ' . $user['email']);
        $this->info('');
    }
}