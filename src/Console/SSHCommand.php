<?php

namespace Meat\Cli\Console;


class SSHCommand extends MeatCommand
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ssh 
                            {--p|production : Connect to production server instead } 
                            {--c|command= : Send this single command and the disconnect}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Connect to staging server';

    /**
     * Execute the command.
     * @return void
     * @throws \Exception
     */
    public function handle() {
        $project = $this->getProject();
        if ($this->option('production')) {
            if (!$project || !$project['forge_production_ip']) {
                $this->error('Sorry, I do no know the IP of the production server. Set it up on Meat Cloud first. ');
                exit(1);
            }

            $folder = '/home/forge/' . $project['forge_production_domain'];
            $ip = escapeshellarg($project['forge_production_ip']);
            $this->info('Connecting to production server...');
        } else {
            if (!$project || !$project['forge_staging_ip']) {
                $this->error('Sorry, I do no know the IP of the staging server. Set it up on Meat Cloud first. ');
                exit(1);
            }

            $folder = '/home/forge/' . $project['forge_staging_domain'];
            $ip = escapeshellarg($project['forge_staging_ip']);
            $this->info('Connecting to staging server...');
        }

        $ssh_command = "cd $folder; bash -l";
        if ($command = $this->option('command')) {
            $ssh_command = "cd $folder; $command";
        }
        passthru("ssh forge@$ip -t '$ssh_command'");

    }
}