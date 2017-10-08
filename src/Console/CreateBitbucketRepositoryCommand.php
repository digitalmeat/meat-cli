<?php

namespace Meat\Cli\Console;


use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;

class CreateBitbucketRepositoryCommand extends MeatCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create-repo {code : Repository name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a Bitbucket repository';


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $name = str_slug($this->argument('code'));
        $this->info('Creating repository... ');
        $repo = $this->api->createRepository($name);
        $this->info('Repository created successfully!');

        $this->table([], [
            ['Nombre: ', $repo['name']],
            ['URL: ', $repo['links']['html']['href']],
            ['Clone HTTPS: ', $repo['links']['clone'][0]['href']],
            ['Clone SSH: ', $repo['links']['clone'][1]['href']],
        ]);

    }


}