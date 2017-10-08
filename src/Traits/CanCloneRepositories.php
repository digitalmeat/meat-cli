<?php

namespace Meat\Cli\Traits;

use Meat\Cli\Helpers\GitHelper;
use Meat\Cli\Helpers\ProcessRunner;
use Meat\Cli\Helpers\ProjectHelper;

/**
 * Class CanCloneRepositories
 * @package Meat\Cli\Traits
 */
trait CanCloneRepositories
{
    /**
     * @return $this
     * @throws \Exception
     */

    protected function cloneRepositoryOrCheckDirectory()
    {
        if (file_exists($this->working_path)) {
            $this->warn("The folder $this->working_path already exists. Skipping git clone...");
            if (!(new ProjectHelper())->isThisFolderAProject($this->working_path)) {
                throw new \Exception('Folder already exists and could not find a project');
            }
            $this->setProjectNameBasedOnGitRepository();
        } else {
            $this->cloneRepository($this->project, $this->folder_name);

            $this->checkoutCorrectBranch();
        }

        return $this;
    }

    /**
     *
     */
    protected function setProjectNameBasedOnGitRepository()
    {

        $this->project = (new GitHelper())->getRespositoryName($this->working_path);
        $this->line('Project name defined as '. $this->project);
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
        $this->execPrint($command);
    }

    /**
     * @return string
     */
    protected function bitbucketUsername()
    {
        return (new GitHelper())->bitbucketUsername();
    }

    public function checkoutCorrectBranch()
    {
        $currentBranch = (new GitHelper())->getCurrentBranch();
        $branch = $this->ask('Checkout branch', $currentBranch);
        (new ProcessRunner())->run('git checkout ' . escapeshellarg($branch));
    }
}