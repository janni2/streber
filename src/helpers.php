<?php

/**
 * Global helper functions for dependency injection container
 *
 * These functions provide convenient access to the DI container
 * for both new and legacy code.
 */

use Streber\Config;
use Streber\Container;

if (!function_exists('container')) {
    /**
     * Get the DI container instance
     *
     * @return Container
     */
    function container(): Container
    {
        return Container::getInstance();
    }
}

if (!function_exists('service')) {
    /**
     * Get a service from the container
     *
     * @param string $id Service identifier
     * @return mixed Service instance
     */
    function service(string $id)
    {
        return Container::getInstance()->get($id);
    }
}

if (!function_exists('config')) {
    /**
     * Get configuration value or Config instance
     *
     * When called without arguments, returns the Config instance.
     * When called with a key, returns the configuration value.
     *
     * @param string|null $key Configuration key
     * @param mixed $default Default value if key doesn't exist
     * @return mixed Config instance or configuration value
     */
    function config(?string $key = null, $default = null)
    {
        $container = Container::getInstance();

        // Register config service if not already registered
        if (!$container->has('config')) {
            $container->setFactory('config', function () {
                return new Config();
            });
        }

        $configService = $container->get('config');

        // If no key provided, return Config instance
        if ($key === null) {
            return $configService;
        }

        // Return specific config value
        return $configService->get($key, $default);
    }
}
