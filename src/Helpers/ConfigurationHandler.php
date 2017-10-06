<?php

namespace Meat\Cli\Helpers;

class ConfigurationHandler
{
    public function isInstalled()
    {
        return $this->configurationFileExists();
    }
    public function configurationFileExists()
    {
        return file_exists($this->getConfigurationPath());
    }

    public function getConfigurationPath()
    {
        return $this->get_user_home_directory() . '/.meat';
    }

    public function save(array $config, $merge = true)
    {
        $newConfiguration = $config;
        if ($merge) {
            $currentConfiguration = $this->all();
            $newConfiguration = array_merge($config, $currentConfiguration);
        }

        file_put_contents($this->getConfigurationPath(), json_encode($newConfiguration, JSON_PRETTY_PRINT));

        return $this;
    }

    public function get($config)
    {
        return $this->all()[$config] ?? null;
    }
    public function all()
    {
        $file = $this->getConfigurationPath();
        if (!$this->configurationFileExists()) {
            return [];
        }

        $response = json_decode(file_get_contents($file), true);
        if ($response == null) {
            return [];
        }

        return $response;
    }

    protected function get_user_home_directory() {
        // Cannot use $_SERVER superglobal since that's empty during UnitUnishTestCase
        // getenv('HOME') isn't set on Windows and generates a Notice.
        $home = getenv('HOME');
        if (!empty($home)) {
            // home should never end with a trailing slash.
            $home = rtrim($home, '/');
        }
        elseif (!empty($_SERVER['HOMEDRIVE']) && !empty($_SERVER['HOMEPATH'])) {
            // home on windows
            $home = $_SERVER['HOMEDRIVE'] . $_SERVER['HOMEPATH'];
            // If HOMEPATH is a root directory the path can end with a slash. Make sure
            // that doesn't happen.
            $home = rtrim($home, '\\/');
        }
        return empty($home) ? NULL : $home;
    }
}