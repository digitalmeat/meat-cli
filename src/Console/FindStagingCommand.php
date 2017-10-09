<?php

namespace Meat\Cli\Console;


class FindStagingCommand extends MeatCommand
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'staging:find 
                            {project-code? : Code of the project. When is not provided, the name of the project in current folder will be used}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Associate the current project with a Forge staging  site';

    /**
     * Execute the command.
     * @return void
     * @throws \Exception
     */
    public function handle() {
        $this->printBigMessage('Associating project with an staging server on Laravel Forge');
        $project = $this->getProject();
        if ($project === null) {
            return;
        }

        if ($project['forge_staging_site_id']) {
            $this->error('This project already has a forge_staging_site_id');
            return;
        }

        $found = false;
        $this->info('The project does not have a Forge site associated. Trying to fix automatically... ');
        try {
            $this->api->autoFindForgeSite($project['id']);
            $this->printBigMessage('Site found and was associated to the project 🙌');
            $found = true;
        } catch (\Exception $exception) {
            $this->warn('Could not automatically detect your staging site on forge.');
            $found = false;
        }

        if (!$found) {
            if ($this->confirm('Do you know the name of the domain on staging?', false)) {
                $domain = $this->anticipate('Enter domain name: ', $this->api->getStagingSites()->pluck('domain')->toArray());
                try {
                    $this->api->autoFindForgeSite($project['id'], $domain);
                    $this->printBigMessage('Site found and was associated to the project 🙌');
                    $found = true;
                } catch (\Exception $e) {
                    $this->error('Could not locate your staging site on forge. You have to set it manually or create a new one with "meat staging:build"');
                    $found = false;
                    return;
                }
            }
        }

        if (!$found) {
            throw new \Exception('We could not create staging');
        }
    }

}