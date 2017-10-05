<?php

namespace Meat\Cli\Console;

use M1\Env\Parser;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class InstallCommand
 * @package Meat\Cli\Console
 */
class MountCommand extends MeatCommand
{
    /** @var array $config */
    private $config = [];
    /**
     * @var
     */
    private $project;
    /**
     * @var
     */
    private $folder_name;
    /**
     * @var
     */
    private $path;

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $info = pathinfo(getcwd());
        $this->setName('mount')->setDescription('Clone and install a MEAT project')
            ->addArgument(
                'project-code',
                InputArgument::OPTIONAL,
                'Slug of the project. When is not provided, the name of the current folder is used',
                $info['basename'])
            ->addArgument(
                'folder',
                InputArgument::OPTIONAL,
                'Folder where we will install the app. When not provided, the name of the project is used')
            ->addOption(
                'no-images',
                'k',
                InputOption::VALUE_NONE,
                'Prevent envoy from downloading all the images and user files');


        $this->addConfig($this->getDefaultConfiguration());
    }
    /**
     * Execute the command.
     * @return void
     * @throws \Exception
     */
    protected function fire()
    {
        $this->project = $this->argument('project-code');
        $this->folder_name = $this->argument('folder') ? : $this->project;
        $this->path = $this->folder_name ? : '.';
        $this->cloneRepositoryOrCheckDirectory()
            ->changeWorkingDirectory($this->path)
            ->runPreInstallScripts()
            ->configureDotEnv()
            ->createDatabaseIfNeeded()
            ->addMeatFileConfiguration()
            ->composerInstall()
            ->npmInstall()
            ->compileAssets()
            ->syncDataFromServer()
            ->runMigrationsIfLaravel()
            ->runPostInstallScripts();

        $this->info('Process complete!');



    }

    public function isThemosis()
    {
        return file_exists('library/Thms/Config/Environment.php');
    }

    public function isLaravel()
    {
        return file_exists('artisan');
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
        $repo_clone = 'git@bitbucket.org:' . $this->bitbucketUsername() . '/' . $project . '.git';
        $this->info("Cloning $repo_clone repository on $folder");
        $command = "git clone $repo_clone $folder";
        $this->runProcess($command);
    }

    function execPrint($command) {
        $result = array();
        $return_status = null;
        exec($command, $result, $return_status);
        foreach ($result as $line) {
            print($line . "\n");
        }

        return $return_status;
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
            'envoy' => 'auto',
            'valet' => 'auto',
            'artisan_migrate'   => 'auto',
            'scripts' => [
                'pre-install' => null,
                'post-install' => null,
            ],
            'sync_folders' =>  'auto',
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
        if (!is_array($config)) {
            return $this;
        }
        $this->config = array_merge_recursive($config, $this->config);
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

    /**
     * @return $this
     * @throws \Exception
     */

    protected function cloneRepositoryOrCheckDirectory()
    {
        if (file_exists($this->path)) {
            $this->line("The folder $this->path already exists...");
            if (!file_exists($this->path . DIRECTORY_SEPARATOR . '.git')) {
                throw new \Exception('Folder already exists and could not find a project');
            }
            $this->setProjectNameBasedOnGitRepository();
        } else {
            $this->cloneRepository($this->project, $this->folder_name);
        }

        return $this;
    }
    /**
     */
    protected function changeWorkingDirectory($folder)
    {
        if (!chdir($folder)) {
            throw new \Exception('Could not change the directory to ' . $folder);
        }

        return $this;
    }
    /**
     * @return $this
     */
    protected function runPreInstallScripts()
    {
        if ($cmd = $this->config('scripts.pre-install')) {
            $this->runProcess($cmd);
        }

        return $this;
    }
    /**
     * @return $this
     */
    protected function configureDotEnv()
    {
        $shouldConfigureDotEnv = $this->config('dotenv');
        if ($shouldConfigureDotEnv === 'auto') {
            $shouldConfigureDotEnv = !file_exists('.env') && file_exists('.env.example');
        }

        if ($shouldConfigureDotEnv === true) {
            $dotEnv = new Parser(file_get_contents(getcwd() . DIRECTORY_SEPARATOR . '.env.example'));
            $dotEnvConfiguration = $dotEnv->getContent();
            $this->line('');
            $this->line('=============================');
            $this->line('Creating .env interactively');
            $this->line('=============================');
            $this->line('');
            $newEnv = [];

            foreach ($dotEnvConfiguration as $key => $value) {
                $autocompleteOptions = $this->getAutocompletionOptions($value, $key);
                if ($this->confirm("{$key} ({$autocompleteOptions[0]}) (Y/n):")) {
                    $newEnv[$key] = $key . '=' . $autocompleteOptions[0];
                } else {
                    $response = $this->ask($key . ' (' . $autocompleteOptions[0] . '): ', $autocompleteOptions[0], $autocompleteOptions);;
                    $newEnv[$key] = $key . '=' . $response;
                }

            }

            file_put_contents(getcwd() . DIRECTORY_SEPARATOR . '.env', implode(PHP_EOL, $newEnv));
        }

        return $this;
    }
    /**
     * @return $this
     */
    protected function addMeatFileConfiguration()
    {
        if (!$this->projectHasAMeatFile()) {
            $this->info('File ' . $this->meatFilename() . ' not found. Using default configuration.');
        } else {
            $this->addConfig($this->getMeatFileConfiguration());
        }

        return $this;
    }
    /**
     * @return $this
     */
    protected function composerInstall()
    {
        if ($this->config('composer')) {
            $this->info('Installing composer dependencies');
            $this->runProcess('composer install --prefer-dist');
        }

        return $this;
    }
    /**
     * @return $this
     */
    protected function npmInstall()
    {
        if ($this->config('npm')) {
            $this->info('Installing NPM dependencies');
            $this->runProcess('npm install');
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function compileAssets()
    {
        $driver = $this->config('assets.driver');
        if ($driver) {
            $command = $this->config("assets.drivers.$driver.dev");
            $this->info('Compiling assets: ' . $command);
            $this->runProcess($command);
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function syncDataFromServer()
    {
        $envoy = $this->config('envoy');
        if ($envoy == 'auto') {
            if (file_exists('envoy.blade.php') || file_exists('Envoy.blade.php')) {
                $envoy = true;
            }
        }

        if ($envoy === true) {
            $this->info('Running envoy sync_database...');
            $this->runProcess('envoy run sync_database');
            if (!$this->option('no-images')) {
                $this->info('Running envoy pull_images...');
                $this->runProcess('envoy run pull_images');
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function runMigrationsIfLaravel()
    {
        if ($this->config('migrate') || ($this->config('migrate') == 'auto' && $this->isLaravel())) {
            $this->info('Running Laravel Migrations...');
            $this->runProcess('envoy run sync_database');
            $this->runProcess('envoy run pull_images');
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function runPostInstallScripts()
    {
        if ($cmd = $this->config('scripts.post-install')) {
            $this->runProcess($cmd);
        }

        return $this;
    }


    protected function setProjectNameBasedOnGitRepository()
    {
        $process = $this->runProcess('git remote get-url origin', false);
        $this->project = substr(explode($this->bitbucketUsername(), trim($process->getOutput()))[1], 1, -4);
        $this->line('Project name defined as '. $this->project);
    }
    /**
     * @param $value
     * @param $key
     * @return array
     */
    protected function getAutocompletionOptions($value, $key)
    {
        $autocompletes = [$value];
        if ($key == 'DB_NAME' || $key == 'DATABASE_NAME') {
            array_unshift($autocompletes, $this->project);
        }
        if ($key == 'WP_HOME') {
            array_unshift($autocompletes, 'http://' . $this->folder_name . '.dev');
        }
        if ($key == 'WP_SITEURL') {
            array_unshift($autocompletes, '${WP_HOME}/cms');
        }

        return $autocompletes;
    }

    protected function createDatabaseIfNeeded()
    {
        $dbConfiguration = $this->getDatabaseConfiguration();
        switch($dbConfiguration['type']) {
            case 'mysql':
                $link = @mysqli_connect($dbConfiguration['host'], $dbConfiguration['user'], $dbConfiguration['password']);
                if ($link === false) {
                    $this->line('<error>Could not establish connection with the database</error>');
                    return false;
                }
                if (!@mysqli_select_db($link, $dbConfiguration['name'])) {
                    if ($this->confirm('The database "' . $dbConfiguration['name'] . '" doesn\'t exists. Do you want to create it? (Y/n): "' )) {
                        $sql = "CREATE DATABASE `" . mysqli_escape_string($link, $dbConfiguration['name']) . '`';
                        if (!mysqli_query($link, $sql)) {
                            $this->line("<error>Error creating database: $sql" . mysqli_error($link) . "</error>");
                        }
                    }
                }

                break;
            default:
                break;
        }

        return $this;
    }
    protected function getDotEnvConfiguration()
    {
        $dotEnv = new Parser(file_get_contents(getcwd() . DIRECTORY_SEPARATOR . '.env'));
        return $dotEnv->getContent();
    }
    protected function getDatabaseConfiguration()
    {
        $dotEnv = $this->getDotEnvConfiguration();

        $dbConfig = [
            'type' => 'mysql',
            'name' => '',
            'host' => '',
            'user' => '',
            'pass' => ''
        ];
        foreach($dotEnv as $key => $value) {
            if(strpos($key, 'DB') === false && strpos($key, 'DATABASE') === false) {
                continue;
            }
            if(strpos($key, 'NAME') !== false) {
                $dbConfig['name'] = $value;
            }
            if(strpos($key, 'HOST') !== false) {
                $dbConfig['host'] = $value;
            }

            if(strpos($key, 'USER') !== false) {
                $dbConfig['user'] = $value;
            }

            if(strpos($key, 'PASS') !== false) {
                $dbConfig['password'] = $value;
            }

            if(strpos($key, 'TYPE') !== false) {
                $dbConfig['type'] = $value;
            }
        }

        return $dbConfig;
    }
}