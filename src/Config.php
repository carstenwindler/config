<?php

declare(strict_types=1);

namespace CarstenWindler\Config;

use CarstenWindler\Config\Exception\ConfigErrorException;
use CarstenWindler\Config\Exception\ConfigKeyNotSetException;

class Config implements ConfigInterface
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var string
     */
    private $configPath;

    /**
     * @var bool
     */
    private $strictMode = false;

    public function __construct(string $configPath = '', array $configArray = [])
    {
        // TODO check for trailing slash
        $this->configPath = $configPath;
        $this->config = $configArray;
    }

    /**
     * Set Config into strict mode, which means: if config keys are NOT set, Config will not
     * return the default value, but throw an exception.
     *
     * @return Config
     */
    public function useStrictMode(): Config
    {
        $this->strictMode = true;

        return $this;
    }

    private function load(string $file): array
    {
        if (!empty($this->configPath)) {
            $file = $this->configPath . '/' . $file;
        }

        if (!file_exists($file)) {
            throw new ConfigErrorException('Config ' . $file . ' not found');
        }

        $configArray = include($file);

        if (!is_array($configArray)) {
            throw new ConfigErrorException('Config ' . $file . ' contains no array');
        }

        return $configArray;
    }

    public function init(string $file): Config
    {
        $this->setConfig($this->load($file));

        return $this;
    }

    public function add(string $file): Config
    {
        $this->mergeConfig($this->load($file));

        return $this;
    }

    public function set(string $key, $value): Config
    {
        $loc = &$this->config;

        foreach (explode('.', $key) as $step) {
            $loc = &$loc[$step];
        }

        $loc = $value;

        return $this;
    }

    public function setConfig(array $config): Config
    {
        $this->config = $config;
        return $this;
    }

    public function mergeConfig(array $config): Config
    {
        $this->config = $this->arrayMergeRecursiveDistinct($this->config, $config);
        return $this;
    }

    private function getFromConfig(string $key)
    {
        $loc = &$this->config;

        foreach (explode('.', $key) as $step) {
            $loc = &$loc[$step];
        }

        return $loc;
    }

    public function get(string $key, $default = null)
    {
        $item = $this->getFromConfig($key);

        if ($item === null) {
            if ($this->strictMode) {
                throw new ConfigKeyNotSetException('Config key ' . $key . ' not set');
            }

            return $default;
        }

        return $item;
    }

    public function has(string $key): bool
    {
        return (boolean) $this->getFromConfig($key);
    }

    public function toArray(): array
    {
        return $this->config;
    }

    private function arrayMergeRecursiveDistinct(array &$array1, array &$array2): array
    {
        $merged = $array1;

        foreach ($array2 as $key => &$value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged [$key] = $this->arrayMergeRecursiveDistinct($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }
}
