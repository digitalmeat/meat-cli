<?php

namespace Meat\Cli\Console;


use Meat\Cli\Helpers\ProjectConfigurationHelper;
use Meat\Cli\Helpers\ProjectHelper;

class CompileAssetsCommand extends MeatCommand
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'compile {folder?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compile assets';

    /**
     * Execute the command.
     * @param ProjectHelper $projectHelper
     * @return void
     */
    public function handle(ProjectHelper $projectHelper) {
        $folder = $this->argument('folder') ?? getcwd();

        if ($projectHelper->isThisFolderAProject($folder)) {
            ProjectConfigurationHelper::getInstance()->setPath($folder)->addMeatFileConfiguration();
            $command = get_project_assets_compilation_script('dev');
            $this->info('Compiling and watching assets: ' . $command );
            $this->changeWorkingDirectory($folder);
            $this->execPrint($command);
        } else {
            $this->error('Could not find a project on ' . $folder);
        }
    }
}