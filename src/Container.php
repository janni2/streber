<?php

declare(strict_types=1);

namespace Streber;

use RuntimeException;

/**
 * Simple service container for dependency injection
 *
 * This container provides a gradual migration path from global variables
 * to proper dependency injection. It supports both new DI-based code and
 * legacy global variable access.
 *
 * @package Streber
 */
class Container
{
    /**
     * Singleton instance
     */
    private static ?Container $instance = null;

    /**
     * Registered services
     */
    private array $services = [];

    /**
     * Service factories (lazy loading)
     */
    private array $factories = [];

    /**
     * Singleton services (instantiated once)
     */
    private array $singletons = [];

    /**
     * Private constructor for singleton pattern
     */
    private function __construct()
    {
    }

    /**
     * Get singleton instance
     */
    public static function getInstance(): Container
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Register a service instance
     *
     * @param string $id Service identifier
     * @param mixed $service Service instance
     */
    public function set(string $id, $service): void
    {
        $this->services[$id] = $service;
    }

    /**
     * Register a service factory (lazy loading)
     *
     * @param string $id Service identifier
     * @param callable $factory Factory function that creates the service
     * @param bool $singleton If true, service is created once and reused
     */
    public function setFactory(string $id, callable $factory, bool $singleton = true): void
    {
        $this->factories[$id] = $factory;
        if ($singleton) {
            $this->singletons[$id] = true;
        }
    }

    /**
     * Get a service from the container
     *
     * Supports backward compatibility with global variables
     *
     * @param string $id Service identifier
     * @return mixed Service instance
     * @throws RuntimeException If service not found
     */
    public function get(string $id)
    {
        // Check if service is directly registered
        if (isset($this->services[$id])) {
            return $this->services[$id];
        }

        // Check if factory exists
        if (isset($this->factories[$id])) {
            $factory = $this->factories[$id];

            // If singleton, create once and store
            if (isset($this->singletons[$id])) {
                $service = $factory($this);
                $this->services[$id] = $service;
                return $service;
            }

            // Non-singleton: create new instance each time
            return $factory($this);
        }

        // Backward compatibility: fallback to global variables
        return $this->getFromGlobals($id);
    }

    /**
     * Check if service exists
     *
     * @param string $id Service identifier
     * @return bool True if service exists
     */
    public function has(string $id): bool
    {
        return isset($this->services[$id])
            || isset($this->factories[$id])
            || $this->hasGlobal($id);
    }

    /**
     * Backward compatibility: Get service from global variables
     *
     * @param string $id Service identifier
     * @return mixed Global variable value
     * @throws RuntimeException If global not found
     */
    private function getFromGlobals(string $id)
    {
        // Map service IDs to global variable names
        $globalMap = [
            'auth' => 'auth',
            'page_handler' => 'PH',
            'config' => 'g_config',
        ];

        $globalName = $globalMap[$id] ?? $id;

        if (isset($GLOBALS[$globalName])) {
            return $GLOBALS[$globalName];
        }

        throw new RuntimeException("Service not found: {$id}");
    }

    /**
     * Check if global variable exists
     *
     * @param string $id Service identifier
     * @return bool True if global exists
     */
    private function hasGlobal(string $id): bool
    {
        $globalMap = [
            'auth' => 'auth',
            'page_handler' => 'PH',
            'config' => 'g_config',
        ];

        $globalName = $globalMap[$id] ?? $id;
        return isset($GLOBALS[$globalName]);
    }

    /**
     * Clear all services (useful for testing)
     */
    public function clear(): void
    {
        $this->services = [];
        $this->factories = [];
        $this->singletons = [];
    }

    /**
     * Reset singleton instance (useful for testing)
     */
    public static function resetInstance(): void
    {
        self::$instance = null;
    }
}
