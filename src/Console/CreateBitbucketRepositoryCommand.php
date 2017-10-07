<?php

namespace Meat\Cli\Console;


use Symfony\Component\Console\Input\InputArgument;

class CreateBitbucketRepositoryCommand extends MeatCommand
{

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $info = pathinfo(getcwd());
        $this->setName('create-repo')
            ->setDescription('Create a Bitbucket repository')
            ->addArgument(
                'code',
                InputArgument::REQUIRED,
                'Repository name');

    }

    /**
     * Execute the command.
     * @return void
     * @throws \Exception
     */
    protected function fire() {
        $name = str_slug($this->argument('code'));
        $this->info('Creating repository... ');
        $repo = $this->api->createRepository($name);

        var_dump($repo);
        $this->info('');
        $this->info('ID: ' . $user['id']);
        $this->info('Nombre: ' . $user['name']);
        $this->info('Email: ' . $user['email']);
        $this->info('');
    }
}