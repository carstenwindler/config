<?php

declare(strict_types=1);

namespace CarstenWindler\Config;

use CarstenWindler\Config\Exception\ConfigErrorException;

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

    public function __construct(string $configPath = '', array $configArray = [])
    {
        // TODO check for trailing slash
        $this->configPath = $configPath;
        $this->config = $configArray;
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

    public function set(string $path, $value): Config
    {
        $loc = &$this->config;

        foreach (explode('.', $path) as $step) {
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

    public function get(string $path, $default = null)
    {
        $loc = &$this->config;

        foreach (explode('.', $path) as $step) {
            $loc = &$loc[$step];
        }

        if (!$loc) {
            return $default;
        }

        return $loc;
    }

    public function has(string $path): bool
    {
        return (boolean) $this->get($path);
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
