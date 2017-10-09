<?php

namespace Meat\Cli\Console;


use Carbon\Carbon;
use GuzzleHttp\Exception\ClientException;
use Meat\Cli\Helpers\GitHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;

class ProjectInfoCommand extends MeatCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'info {project-code? : Code of the project. When is not provided, the name of the current folder is used}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get information of the current project';

    /**
     * Execute the command.
     * @return void
     * @throws \Exception
     */
    public function handle() {
        $code = $this->argument('project-code');
        if (!$code) {
            $code = (new GitHelper())->getRespositoryName();
        }
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
        $this->table([],[
            ['Name: ', $project['name']],
            ['Code: ', $project['code']],
            ['Author Name: ', $project['author']['data']['name'] ?? '-'],
            ['Author Email: ', $project['author']['data']['email'] ?? '-'],
            ['Staging IP: ', $project['forge_staging_ip']],
            ['Staging URL: ', $project['forge_staging_domain']],
            ['Staging Forge ID: ', $project['forge_staging_site_id']],
            ['Repository service: ', $project['repo_service']],
            ['Repository URL: ', $project['repo_url']],
            ['Repository Name: ', $project['repo_name']],
            ['Repository Full Name: ', $project['repo_full_name']],
            ['Repository Clone HTTPS: ', $project['repo_clone_https']],
            ['Repository Clone SSL: ', $project['repo_clone_ssh']],
        ]);

        $this->info('Project users');
        $users =collect($project['users']['data'])->map(function($user) {
            return [$user['name'], $user['email'], Carbon::parse($user['assigned_at'])->diffForHumans()];
        });
        $this->table(['Name', 'Email', 'When'], $users);

        if ($project['installations']) {
            $this->info('');
            $this->info('Project installations');
            $users =collect($project['installations']['data'])->map(function($user) {
                return [$user['user']['data']['name'], $user['user']['data']['email'], Carbon::parse($user['created_at'])->diffForHumans()];
            });
            $this->table(['Name', 'Email', 'When'], $users);
        }


    }
}