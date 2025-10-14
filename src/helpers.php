<?php

/**
 * Global helper functions for dependency injection container
 *
 * These functions provide convenient access to the DI container
 * for both new and legacy code.
 */

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
