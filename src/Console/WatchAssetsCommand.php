<?php

namespace Meat\Cli\Console;


use Meat\Cli\Helpers\ProjectConfigurationHelper;
use Meat\Cli\Helpers\ProjectHelper;

class WatchAssetsCommand extends MeatCommand
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'watch {folder?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start assets watcher';

    /**
     * Execute the command.
     * @return void
     * @throws \Exception
     */
    public function handle(ProjectHelper $projectHelper) {
        $folder = $this->argument('folder') ?? getcwd();

        if ($projectHelper->isThisFolderAProject($folder)) {
            ProjectConfigurationHelper::getInstance()->setPath($folder)->addMeatFileConfiguration();
            $command = get_project_assets_compilation_script('watch');
            $this->info('Compiling and watching assets: ' . $command );
            $this->changeWorkingDirectory($folder);
            $this->execPrint($command);
        } else {
            $this->error('Could not find a project on ' . $folder);
        }
    }
}