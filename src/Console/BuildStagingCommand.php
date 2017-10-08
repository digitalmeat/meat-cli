<?php

namespace Meat\Cli\Console;


use Meat\Cli\Helpers\GitHelper;
use Meat\Cli\Helpers\ProjectHelper;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;

class BuildStagingCommand extends MeatCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'build-staging 
                            {project-code? : Code of the project. When is not provided, the name of the current folder will be used}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a Bitbucket repository';

    /**
     * Execute the console command.
     * @return mixed
     * @throws \Exception
     */
    public function handle()
    {
        $this->printBigMessage('Building staging on Laravel Forge... ');

        $project_code = $this->argument('project-code');
        if (!$project_code) {
            $folder = getcwd();
            if (!(new ProjectHelper())->isThisFolderAProject($folder)) {
                throw new \Exception('Could not find a project on ' . $folder);
            }
            $project_code = (new GitHelper())->getRespositoryName($folder);
        }

        $this->api->setupProjectForge($project_code, get_project_assets_compilation_script('production'));

    }


}