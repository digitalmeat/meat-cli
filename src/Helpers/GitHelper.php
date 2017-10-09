<?php

namespace Meat\Cli\Helpers;

class GitHelper
{
    public function getRespositoryName($folder = null)
    {
        $olddir = getcwd();
        if ($folder !== null) {
            chdir($folder);
        }
        $process = (new ProcessRunner())->run('git remote get-url origin', false);
        chdir($olddir);
        return substr(explode($this->bitbucketUsername(), trim($process->getOutput()))[1], 1, -4);
    }



    /**
     * @return string
     */
    public function bitbucketUsername()
    {
        return 'digitalmeatdev';
    }

    public function getCurrentBranch()
    {
        $currentBranch = (new ProcessRunner())->run('git branch | awk \'/\*/ { print $2; }\'', false);
        return trim($currentBranch->getOutput());
    }
}