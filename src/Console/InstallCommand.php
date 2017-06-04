<?php

namespace Meat\Cli\Console;

use M1\Env\Parser;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Class InstallCommand
 * @package Meat\Cli\Console
 */
class InstallCommand extends SymfonyCommand
{
    use Command;
    /** @var array $config */
    private $config = [];


    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $info = pathinfo(getcwd());
        $this->setName('install')->setDescription('Clone and install a MEAT project')
            ->addArgument(
                'project-code',
                InputArgument::OPTIONAL,
                'Slug of the project. When is not provided, the name of the current folder is used',
                $info['basename'])
            ->addArgument(
                'folder',
                InputArgument::OPTIONAL,
                'Folder where we will install the app. When not provided, the name of the project is used');

        $this->addConfig($this->getDefaultConfiguration());
    }
    /**
     * Execute the command.
     * @return void
     * @throws \Exception
     */
    protected function fire()
    {
        $project = $this->argument('project-code');
        $folder = $this->argument('folder') ? $this->argument('folder') : $project;

        if (file_exists($folder)) {
            $this->info("The folder $folder already exists...");
            if (!file_exists($folder . DIRECTORY_SEPARATOR . '.git')) {
                throw new \Exception('Folder already exists and is not project');
            }
        } else {
            $this->cloneRepository($project, $folder);
        }
        chdir($folder);
        if ($cmd = $this->config('scripts.pre-install')) {
            $this->runProcess($cmd);
        }

        $dotenv = $this->config('dotenv');
        if ($dotenv === 'auto') {
            $dotenv = !file_exists('.env') && file_exists('.env.example');
        }

        if ($dotenv) {
            $example = new Parser(file_get_contents(getcwd() . DIRECTORY_SEPARATOR . '.env.example'));
            $array = $example->getContent();
            $this->info('Creating .env interactively');
            $newenv = [];
            foreach($array as $key => $value) {
                $newenv[$key] = $key . '=' . $this->ask($key . ' (' . $value . '): ', $value);
            }

            file_put_contents(getcwd() . DIRECTORY_SEPARATOR . '.env', implode(PHP_EOL, $newenv));
        }


        if (!$this->projectHasAMeatFile()) {
            $this->info('File ' . $this->meatFilename() . ' not found. Using default configuration.');
        } else {
            $this->addConfig($this->getMeatFileConfiguration());
        }

        if ($this->config('composer')) {
            $this->info('Installing composer dependencies');
            $this->runProcess('composer install --prefer-dist');
        }

        if ($this->config('npm')) {
            $this->info('Installing NPM dependencies');
            $this->runProcess('npm install');
        }

        if ($driver = $this->config('assets_driver')) {
            $this->info('Provisioning assets...');
            $command = $this->config("assets_drivers.$driver.dev");
            $this->runProcess($command);
        }

        if ($driver = $this->config('envoy')) {
            $this->info('Running envoy...');
            $this->runProcess('envoy run sync_database');
            $this->runProcess('envoy run pull_images');
        }

        if ($cmd = $this->config('scripts.post-install')) {
            $this->runProcess($cmd);
        }



    }
    /**
     * @return string
     */
    protected function bitbucketUsername()
    {
        return 'digitalmeatdev';
    }
    /**
     * @return string
     */
    protected function meatFilename()
    {
        return 'meat.json';
    }

    /**
     * @return bool
     */
    protected function projectHasAMeatFile()
    {
        return file_exists(getcwd() . DIRECTORY_SEPARATOR . $this->meatFilename());
    }
    /**
     * @param $project
     * @param $folder
     * @throws \Exception
     */
    protected function cloneRepository($project, $folder)
    {
        $this->info("Cloning repository on $folder");
        $process = new Process('git clone git@bitbucket.org:' . $this->bitbucketUsername() . '/' . $project . '.git ' . $folder);
        $process->setTimeout(null);
        $process->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        if ($process->getExitCode() !== 0) {
            throw new \Exception('An error ocurred cloning the repository. ');
        }
    }
    /**
     * @return mixed
     */
    protected function getMeatFileConfiguration()
    {
        return json_decode(file_get_contents($this->meatFilename()));
    }

    /**
     * @return array
     */
    protected function getDefaultConfiguration()
    {
        return [
            'composer' => true,
            'npm' => true,
            'bower' => false,
            'fetch_database_from_server' => true,
            'dotenv' => 'auto',
            'envoy' => true,
            'scripts' => [
                'pre-install' => null,
                'post-install' => null,
            ],
            'uploads' => [
                'get_from_server' => true,
                'folders' => 'auto'
            ],
            'assets' => [
                'driver' => 'mix',
                'drivers' => [
                    'mix' => [
                        'dev' => 'npm run dev',
                        'watch' => 'npm run watch',
                        'production' => 'npm run production'
                    ],
                    'elixir' => [
                        'dev' => 'gulp',
                        'watch' => 'gulp watch',
                        'production' => 'gulp --production'
                    ],
                    'gulp' => [
                        'dev' => 'gulp',
                        'watch' => 'gulp',
                        'production' => 'gulp build'
                    ]
                ]
            ]
        ];
    }

    /**
     * @param $key
     * @return mixed|null
     */
    protected function config($key)
    {
        return (isset($this->config[$key])) ? $this->config[$key] : null;
    }

    /**
     * @param $config
     * @return $this
     */
    protected function addConfig($config)
    {
        $this->config = array_merge($config, $this->config);
        $this->config = array_merge($this->preloadAsDotNotation(), $this->config);

        return $this;
    }
    /**
     * @return array
     */
    protected function preloadAsDotNotation()
    {
        $ritit = new RecursiveIteratorIterator(new RecursiveArrayIterator($this->config));
        $result = array();
        foreach ($ritit as $leafValue) {
            $keys = array();
            foreach (range(0, $ritit->getDepth()) as $depth) {
                $keys[] = $ritit->getSubIterator($depth)->key();
            }
            $result[join('.', $keys)] = $leafValue;
        }

        return $result;
    }
}