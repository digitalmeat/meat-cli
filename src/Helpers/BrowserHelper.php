<?php

namespace Meat\Cli\Helpers;

class BrowserHelper
{
    public function openUrl($url)
    {
        return (new ProcessRunner())->run('/usr/bin/open \'' . escapeshellarg($url) . '\'');
    }
}