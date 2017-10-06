<?php

function config($config, $default = null) {
    $handler = new \Meat\Cli\Helpers\ConfigurationHandler();
    if (is_array($config)) {
        return $handler->save($config);
    }
    return $handler->get($config) ?? $default;
}