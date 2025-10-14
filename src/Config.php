<?php

declare(strict_types=1);

namespace Streber;

/**
 * Configuration service wrapper
 *
 * Provides object-oriented access to configuration while maintaining
 * backward compatibility with the legacy global $g_config array.
 *
 * @package Streber
 */
class Config
{
    /**
     * Configuration data
     */
    private array $data = [];

    /**
     * Constructor
     *
     * @param array $initialData Initial configuration data
     */
    public function __construct(array $initialData = [])
    {
        $this->data = $initialData;

        // Load from global config if exists (backward compatibility)
        if (isset($GLOBALS['g_config']) && is_array($GLOBALS['g_config'])) {
            $this->data = array_merge($this->data, $GLOBALS['g_config']);
        }
    }

    /**
     * Get a configuration value
     *
     * @param string $key Configuration key
     * @param mixed $default Default value if key doesn't exist
     * @return mixed Configuration value
     */
    public function get(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Set a configuration value
     *
     * @param string $key Configuration key
     * @param mixed $value Configuration value
     */
    public function set(string $key, $value): void
    {
        $this->data[$key] = $value;

        // Keep global config in sync (backward compatibility)
        if (isset($GLOBALS['g_config'])) {
            $GLOBALS['g_config'][$key] = $value;
        }
    }

    /**
     * Check if a configuration key exists
     *
     * @param string $key Configuration key
     * @return bool True if key exists
     */
    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }

    /**
     * Get all configuration data
     *
     * @return array All configuration data
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * Merge configuration data
     *
     * @param array $data Configuration data to merge
     */
    public function merge(array $data): void
    {
        $this->data = array_merge($this->data, $data);

        // Keep global config in sync (backward compatibility)
        if (isset($GLOBALS['g_config'])) {
            $GLOBALS['g_config'] = array_merge($GLOBALS['g_config'], $data);
        }
    }

    /**
     * Get a configuration value (magic method)
     *
     * Allows accessing config like: $config->APP_NAME
     *
     * @param string $key Configuration key
     * @return mixed Configuration value
     */
    public function __get(string $key)
    {
        return $this->get($key);
    }

    /**
     * Set a configuration value (magic method)
     *
     * Allows setting config like: $config->APP_NAME = 'Streber'
     *
     * @param string $key Configuration key
     * @param mixed $value Configuration value
     */
    public function __set(string $key, $value): void
    {
        $this->set($key, $value);
    }

    /**
     * Check if a configuration key exists (magic method)
     *
     * @param string $key Configuration key
     * @return bool True if key exists
     */
    public function __isset(string $key): bool
    {
        return $this->has($key);
    }
}
