<?php

namespace Meat\Cli\Console;

use GuzzleHttp\Exception\ClientException;
use Meat\Cli\Traits\CanCloneRepositories;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class CreateCommand
 * @package Meat\Cli\Console
 */
class CreateCommand extends MeatCommand
{
    /**
     * @var
     */
    protected $folder;

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {

        $info = pathinfo(getcwd());
        $this->setName('create')
            ->setDescription('Create a new project based on a predefined template')
            ->addOption(
                'no-commit',
                'c',
                InputOption::VALUE_NONE,
                'Do not run the first commit. Just add the remote origin')
            ->addOption(
                'yes',
                'y',
                InputOption::VALUE_NONE,
                'Automatically reply yes to every answer');

    }

    /**
     *
     */
    public function fire()
    {
        /*$project = $this->api->setupProject('1', [
            'bitbucket' => true,
            'staging' => true,
            'trello' => true,
            'slack' => true
        ]);
        var_dump($project);
        die;*/

        $type = $this->choice('Select a base scaffolding', [
            'themosis' => 'Themosis',
            'laravel' => 'Laravel',
            'blank' => 'Blank'
        ], 'blank');

        $this->info($type . ' selected');

        $name = $this->ask('Project name: ', null, null, true);
        $code = str_slug($name);
        while(true) {
            $code = $this->ask("Project code ($code): ",$code, null, true);

            try {
                $project = $this->createProjectOnMeatApi($name, $code, $type);
                break;
            } catch (ClientException $e) {
                $this->error(json_decode($e->getResponse()->getBody()->getContents(), true)['msg']);
            }
        }

        $folder = getcwd() . DIRECTORY_SEPARATOR .  $code;
        while(true) {
            $folder = $this->ask("Installation folder ($folder): ", $folder);
            if (!file_exists($folder)) {
                break;
            }
            $this->error('This directory already exists... ');
        }

        $this->folder = $folder;

        $this->installBaseRepository($type)
            ->changeWorkingDirectory($folder);



        $bitbucket = $this->confirm('Create Bitbucker repository? (Y/n): ');
        $staging = $bitbucket && $this->confirm('Create staging on Laravel Forge? (Y/n): ');
        $trello = $this->confirm('Create Trello Board? (Y/n): ');
        $slack = $this->confirm('Create Slack Channel? (Y/n): ');

        $this->info('Setting up project...');

        $project = $this->api->setupProject($project['id'], [
            'bitbucket' => $bitbucket,
            'staging' => $staging,
            'trello' => $trello,
            'slack' => $slack
        ]);

        if ($bitbucket) {
            $this->info('Setting newly created repository as a git remote');
            $this->setAsNewGitRepository($this->getRemoteUrlByProject($project));
        }
        
        $this->line('');
        $this->line('=============================');
        $this->line('Process complete!');
        $this->line('=============================');
        $this->line('');

    }

    /**
     * @param $type
     * @param $folder
     * @return mixed
     */
    public function getCreateCommandByProjectType($type, $folder)
    {
        $escapedFolder = escapeshellarg($folder);
        $repos = [
            'themosis'  => 'composer create-project digitalmeat/themosis:dev-master ' . $escapedFolder,
            'laravel'  => 'laravel new ' . $escapedFolder . ' > /dev/null 2>&1 || composer create-project --prefer-dist laravel/laravel ' . $escapedFolder,
            'blank'  => 'mkdir ' . $escapedFolder,
        ];

        return $repos[$type];
    }

    /**
     * @param $base
     * @return $this
     */
    private function installBaseRepository($base)
    {
        $folder = $this->folder;
        $command = $this->getCreateCommandByProjectType($base, $folder);
        $this->info("Installing base on $folder");

        $this->runProcess($command);

        return $this;
    }

    /**
     * @param $project_code
     * @param $project_name
     * @param $project_type
     * @return mixed
     */
    public function createProjectOnMeatApi($project_name, $project_code, $project_type) {
        $this->line('Creating project on MEAT Cloud...');
        $project = $this->api->createProject($project_code, $project_name, $project_type);
        $this->info('Project created successfully!');
        return $project;
    }

    /**
     * @param $remote
     */
    protected function setAsNewGitRepository($remote)
    {
        if (file_exists('.git')) {
            rmdir('.git');
        }

        $this->execPrint('git init');
        $this->execPrint('git remote add origin ' . escapeshellarg($remote));

        if (!$this->option('no-commit')) {
            $this->runProcess('git add .');
            $this->runProcess('git commit -m"[GIT] Initial commit"');
        }

    }

    public function confirm($question, $default = true) {
        if ($this->option('yes')) {
            return true;
        }

        return parent::confirm($question, $default = true);
    }
    /**
     * @param $project
     * @return string
     */
    protected function getRemoteUrlByProject($project)
    {
        return 'git@bitbucket:' . $project['repo_full_name'] . '.git';
    }

}