<?php

namespace Meat\Cli\Console;

use GuzzleHttp\Exception\ClientException;
use Meat\Cli\Helpers\GitHelper;
use Meat\Cli\Helpers\ProjectHelper;
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
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create-project
                            {--c|no-commit : Do not run the first commit. Just add the remote origin}
                            {--y|yes : Automatically reply yes to every answer}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new project based on a predefined template';

    /**
     * @var
     */
    protected $folder;

    /**
     * @param ProjectHelper $projectHelper
     */
    public function handle(ProjectHelper $projectHelper)
    {
        if($projectHelper->isThisFolderAProjectRepository(getcwd())) {
            //You cant create a project inside a projects, so lets create this project on MEAT
            $this->createNewProjectFromThisRepository();
            return;
        } else {
            $project = $this->createNewProjectFromBase();
        }

        $bitbucket = $this->confirm('Create Bitbucket repository?', true);
        if ($bitbucket) {
            $this->info('Setting up bitbucket repository...');
            try {
                $project = $this->api->setupProjectBitbucket($project['id']);
            } catch (\Exception $e) {
                $this->error('Something went wrong creating the Bitbucket repository...');
                $bitbucket = false;
            }

            $this->info('Setting newly created repository as a git remote');
            $this->setAsNewGitRepository($this->getRemoteUrlByProject($project));
            $this->info('Repository added to remote origin successfully');
        }

        $staging = $bitbucket && $this->confirm('Create staging on Laravel Forge?', true);
        if ($staging) {
            $this->info('Setting up staging...');
            try {
                $project = $this->api->setupProjectStaging($project['id'], get_project_assets_compilation_script('production'));
            } catch (\Exception $e) {
                $this->error('Something went wrong creating the staging...');
                $staging = false;
            }

        }

        $this->printBigMessage('Project created successfully');

        unlink('.env');

        $this->call('mount');
        /*$trello = $this->confirm('Create Trello Board?', true);
        if ($trello) {
            $this->info('Setting up Trello...');
            $project = $this->api->setupProjectTrello($project['id']);
        }

        $slack = $this->confirm('Create Slack Channel?', true);
        if ($slack) {
            $this->info('Setting up Slack...');
            $project = $this->api->setupProjectSlack($project['id']);
        }*/


        $this->printBigMessage('Process complete! 🙌');

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
            'blank'  => 'mkdir ' . $escapedFolder . ' && cd ' . $escapedFolder . ' && echo "# Readme" > readme.md',
        ];

        return $repos[$type];
    }

    /**
     * @param $base
     * @return $this
     */
    private function installBaseScaffolding($base)
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
            $this->info('Pushing first commit');
            $this->runProcess('git push --set-upstream origin master');
            $this->runProcess('git checkout -b develop');
            $this->runProcess('git push --set-upstream origin develop');
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
        return 'git@bitbucket.org:' . $project['repo_full_name'] . '.git';
    }
    /**
     * @param $name
     * @param $type
     * @return mixed
     */
    protected function askCodeAndCreateProject($name, $type)
    {
        $code = str_slug($name);
        $project = false;
        while (true) {
            $code = $this->ask("Project code", $code);

            try {
                $project = $this->createProjectOnMeatApi($name, $code, $type);
                break;
            } catch (ClientException $e) {
                $this->error(json_decode($e->getResponse()->getBody()->getContents(), true)['msg']);
            }
        }

        return $project;
    }
    /**
     * @return array
     */
    protected function askTypeAndName()
    {
        $type = $this->choice('Select a base scaffolding', [
            'themosis' => 'Themosis',
            'laravel' => 'Laravel',
            'blank' => 'Blank'
        ], 'blank');

        $this->info($type . ' selected');
        $name = $this->ask('Project name', null, null, true);

        return array($type, $name);
    }
    /**
     * @param $project
     * @return string
     */
    protected function askForInstallationDirectory($project)
    {
        $folder = getcwd() . DIRECTORY_SEPARATOR . $project['code'];
        while (true) {
            $folder = $this->ask("Installation folder", $folder);
            if (!file_exists($folder)) {
                break;
            }
            $this->error('This directory already exists... ');
        }

        $this->folder = $folder;

        return $folder;
    }
    /**
     * @return mixed
     */
    protected function createNewProjectFromBase()
    {
        list($type, $name) = $this->askTypeAndName();
        $project = $this->askCodeAndCreateProject($name, $type);
        $folder = $this->askForInstallationDirectory($project);

        $this->installBaseScaffolding($type);
        $this->changeWorkingDirectory($folder);

        return $project;
    }
    /**
     * @return mixed|bool
     */
    protected function createNewProjectFromThisRepository()
    {
        $project_code = (new GitHelper())->getRespositoryName();
        $project = $this->getProject($project_code);
        if ($project) {
            $this->error('This repository is already connected to a Meat project. ');
            $this->line('');
            $this->call('info');
            return false;
        }
        $name = $this->ask('Project name: ');
        $type = $this->getProjectType();
        $project = $this->createProjectOnMeatApi($name, $project_code, $type);

        return $project;
    }

}