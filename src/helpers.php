<?php

use Meat\Cli\Helpers\ProjectConfigurationHelper;

function config($config, $default = null) {
    $handler = new \Meat\Cli\Helpers\ConfigurationHandler();
    if (is_array($config)) {
        return $handler->save($config);
    }
    return $handler->get($config) ?? $default;
}

function project_config($key, $default = null) {
    $config = ProjectConfigurationHelper::getInstance();
    return $config->get($key) ?? $default;
}

function get_project_assets_compilation_script($env = 'dev') {
    return project_config('assets.drivers.' . project_config('assets.driver') . '.' . $env);
}

function has_meatfile() {
    $config = ProjectConfigurationHelper::getInstance();
    return $config->projectHasAMeatFile();
}