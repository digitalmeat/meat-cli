<?php

namespace Meat\Cli\Helpers;

use RecursiveArrayIterator;
use RecursiveIteratorIterator;

/**
 * Class ProjectConfigurationHelper
 * @package Meat\Cli\Helpers
 */
class ProjectConfigurationHelper
{
    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var self
     */
    private static $instance;

    /**
     * @return self
     */
    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * ProjectConfigurationHelper constructor.
     */
    protected function __construct() {
        $this->addConfig($this->getDefaultConfiguration());
        $this->addMeatFileConfiguration();
    }
    private function __clone() {}
    private function __wakeup() {}

    /**
     * @return array
     */
    protected function getDefaultConfiguration()
    {
        return [
            'composer' => true,
            'npm' => true,
            'bower' => false,
            'dotenv' => 'auto',
            'envoy' => 'auto',
            'artisan_migrate'   => 'auto',
            'scripts' => [
                'pre-install' => null,
                'post-install' => null,
            ],
            'assets' => [
                'driver' => 'mix',
                'drivers' => [
                    'mix' => [
                        'dev' => 'npm run dev',
                        'watch' => 'npm run watch',
                        'production' => 'npm run production'
                    ],
                    'elixir' => [
                        'dev' => 'gulp',
                        'watch' => 'gulp watch',
                        'production' => 'gulp --production'
                    ],
                    'gulp' => [
                        'dev' => 'gulp',
                        'watch' => 'gulp',
                        'production' => 'gulp build'
                    ]
                ]
            ]
        ];
    }

    /**
     * @param $key
     * @return mixed|null
     */
    public function get($key)
    {
        return (isset($this->config[$key])) ? $this->config[$key] : null;
    }

    /**
     * @param $config
     * @return $this
     */
    public function addConfig($config)
    {
        if (!is_array($config)) {
            return $this;
        }
        $this->config = array_replace_recursive($this->config, $config);
        $this->config = array_merge($this->config, $this->getConfigAsDotNotation($config));
        return $this;
    }

    /**
     * @return array
     */
    protected function getConfigAsDotNotation($config = null)
    {
        if ($config === null) {
            $config = $this->config;
        }
        $ritit = new RecursiveIteratorIterator(new RecursiveArrayIterator($config));
        $result = array();
        foreach ($ritit as $leafValue) {
            $keys = array();
            foreach (range(0, $ritit->getDepth()) as $depth) {
                $keys[] = $ritit->getSubIterator($depth)->key();
            }
            $result[join('.', $keys)] = $leafValue;
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function projectHasAMeatFile($folder = null)
    {
        $folder = $folder ?? getcwd() . DIRECTORY_SEPARATOR;
        return file_exists($folder . $this->meatFilename());
    }

    /**
     * @return string
     */
    protected function meatFilename()
    {
        return 'meat.json';
    }


    /**
     * @return mixed
     */
    protected function getMeatFileConfiguration()
    {

        return json_decode(file_get_contents($this->meatFilename()), true);
    }

    /**
     * @return bool
     */
    protected function addMeatFileConfiguration()
    {
        if (!$this->projectHasAMeatFile()) {
            return false;
        } else {
            $this->addConfig($this->getMeatFileConfiguration());
        }

        return true;
    }
}