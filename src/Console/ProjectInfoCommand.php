<?php

namespace Meat\Cli\Console;


use GuzzleHttp\Exception\ClientException;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;

class ProjectInfoCommand extends MeatCommand
{

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $info = pathinfo(getcwd());
        $this->setName('info')
            ->setDescription('Get information of the current project')
            ->addArgument(
                'project-code',
                InputArgument::OPTIONAL,
                'Code of the project. When is not provided, the name of the current folder is used',
                $info['basename']);

    }

    /**
     * Execute the command.
     * @return void
     * @throws \Exception
     */
    protected function fire() {
        $code = $this->argument('project-code');
        $project = false;
        try {
            $project = $this->api->getProject($code);
        } catch (ClientException $exception) {
            if ($exception->getCode() == 404) {
                $this->error('Project "' . $code . '" does not exists');
                return;
            }
        }

        if (!$project) {
            $this->error('I don\'t know who you are. Access token is invalid or it is not set.');
            $this->info('You can refresh the access token running  "meat init" command ');
            return;
        }


        $table = new Table($this->output);
        $table->setRows([
            ['Name: ', $project['name']],
            ['Code: ', $project['code']],
            ['Repository service: ', $project['repo_service']],
            ['Repository URL: ', $project['repo_url']],
            ['Repository Name: ', $project['repo_name']],
            ['Repository Full Name: ', $project['repo_full_name']],
            ['Repository Clone HTTPS: ', $project['repo_clone_https']],
            ['Repository Clone SSL: ', $project['repo_clone_ssh']],
        ]);

        $table->render();

    }
}