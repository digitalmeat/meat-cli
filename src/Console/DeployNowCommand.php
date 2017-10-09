<?php

namespace Meat\Cli\Console;


use Meat\Cli\Helpers\GitHelper;
use Meat\Cli\Helpers\ProjectHelper;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;

class DeployNowCommand extends MeatCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deploy-now 
                            {project-code? : Code of the project. When is not provided, the name of the project in current folder will be used}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the deployment script on Laravel Forge if this project has a Forge Site associated';

    /**
     * Execute the console command.
     * @return mixed
     * @throws \Exception
     */
    public function handle()
    {

        $this->info('Connecting to MEAT Cloud...');
        $project = $this->getProject();
        if ($project === null) {
            return;
        }

        if (!$project['forge_site_id']) {
            $this->call('find-staging');
        }

        $this->printBigMessage('Running deployment script. This may take a while...');
        try {
            $this->api->deployNow($project['code']);
        } catch (\Exception $e) {
            $this->error('Something went wrong... Please try again later.');
            echo $e->getMessage();
        }

        $this->printBigMessage('Process complete!');

    }

}