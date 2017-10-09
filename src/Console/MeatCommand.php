<?php

namespace Meat\Cli\Console;
use Illuminate\Console\Command;
use Meat\Cli\Helpers\ConfigurationHandler;
use Meat\Cli\Helpers\GitHelper;
use Meat\Cli\Helpers\MeatAPI;
use Meat\Cli\Helpers\ProcessRunner;
use Meat\Cli\Helpers\ProjectHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Class MeatCommand
 * @package Meat\Cli\Console
 */
class MeatCommand extends Command
{
    /**
     * @var bool
     */
    protected $needLogin = true;

    /**
     * @var ConfigurationHandler
     */
    protected $configurationHandler;

    /**
     * @var MeatAPI
     */
    protected $api;
    /**
     * @var ProcessRunner
     */
    private $processRunner;

    /**
     * InstallCommand constructor.
     */
    public function __construct(ProcessRunner $processRunner)
    {
        parent::__construct();

        $this->configurationHandler = new ConfigurationHandler();
        $this->api = new MeatAPI();
        $this->processRunner = $processRunner;

    }

    /**
     * Execute the console command.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return mixed
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $install = true;
        while ($install) {
            $install = false;
            if ($this->needLogin) {
                if (!(new ConfigurationHandler())->isInstalled()) {
                    $this->error('You have not installed MEAT Cli. Entering the installation process...');
                    $install = true;
                } elseif (!$this->api->me()) {
                    $this->error('Access token is invalid. Please run "meat init" again');
                    $install = true;
                }
            }
            if ($install) {
                $this->call('init');
            }
        }

        //this refresh the token so coming calls will use the new token
        $this->api->setClient();

        return $this->laravel->call([$this, 'handle']);
    }

    /**
     * @param $folder
     * @return $this
     * @throws \Exception
     */
    protected function changeWorkingDirectory($folder)
    {
        if (!chdir($folder)) {
            throw new \Exception('Could not change the directory to ' . $folder);
        }

        return $this;
    }

    /**
     * @param $command
     * @param bool $print
     * @param null $timeout
     */
    public function runProcess($command, $print = true, $timeout = null)
    {
        $this->processRunner->run($command, $print, $timeout);
    }

    /**
     * @param $command
     */
    public function execPrint($command)
    {
        $this->processRunner->execPrint($command);
    }
    /**
     * @param $msg
     */
    protected function printBigMessage($msg)
    {
        $length = strlen($msg) + 5;
        if ($length <= 60) {
            $length = 60;
        }
        $this->line('');
        $this->line(str_pad('========', $length, '='));
        $this->line($msg);
        $this->line(str_pad('========', $length, '='));
        $this->line('');
    }

    /**
     * Prompt the user for input but hide the answer from the console.
     *
     * @param  string  $question
     * @param  bool    $fallback
     * @return string
     */
    public function secretAllowEmpty($question, $fallback = true)
    {
        $question = new Question($question, false);

        $question->setHidden(true)->setHiddenFallback($fallback);

        return $this->output->askQuestion($question);
    }
    /**
     * @return array|bool|string
     * @throws \Exception
     */
    protected function getProjectCode()
    {
        $project_code = $this->argument('project-code');
        if (!$project_code) {
            $folder = getcwd();
            if (!(new ProjectHelper())->isThisFolderAProject($folder)) {
                throw new \Exception('Could not find a project on ' . $folder);
            }
            $project_code = (new GitHelper())->getRespositoryName($folder);
        }

        return $project_code;
    }
    /**
     * @return mixed|null
     */
    protected function getProject()
    {
        $project_code = $this->getProjectCode();

        $project = null;
        try {
            $project = $this->api->getProject($project_code);
        } catch (\Exception $e) {
            if ($e->getCode() == 404) {
                $this->error('The project "' . $project_code . '" is not created on MEAT Cloud... ');
            }
        }

        return $project;
    }
}