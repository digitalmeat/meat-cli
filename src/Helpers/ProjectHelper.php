<?php

namespace Meat\Cli\Helpers;

/**
 * Class ProjectHelper
 * @package Meat\Cli\Helpers
 */
class ProjectHelper
{

    /**
     * Check if this folder is a installed project
     *
     * @param $path
     * @return bool
     */
    public function isThisFolderAProjectRepository($path)
    {
        //@TODO: Change this to GitHelper
        return file_exists($path . DIRECTORY_SEPARATOR . '.git');
    }
}