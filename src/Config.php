<?php declare(strict_types=1);

namespace CarstenWindler\Config;

use CarstenWindler\Config\Exception\ConfigErrorException;
use CarstenWindler\Config\Exception\ConfigKeyNotSetException;

class Config implements ConfigInterface
{
    private $config = [];
    private $configPath;
    private $strictMode = false;

    public function setConfigPath(string $configPath): Config
    {
        $this->configPath = $configPath;

        return $this;
    }

    /**
     * Set Config into strict mode, which means: if config keys are NOT set, Config will not
     * return the default value, but throw an exception.
     *
     * You should not activate this in production, but use it in local dev and for CI to
     * identify setup problems.
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

    public function addConfigFile(string $file): Config
    {
        $this->addConfigArray($this->load($file));

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

    public function addConfigArray(array $config): Config
    {
        $this->config = $this->arrayMergeRecursiveDistinct($this->config, $config);
        return $this;
    }

    public function get(string $key, $default = null)
    {
        $loc = &$this->config;

        foreach (explode('.', $key) as $step) {
            if (array_key_exists($step, $loc)) {
                $loc = &$loc[$step];
                continue;
            }

            if ($this->strictMode) {
                throw new ConfigKeyNotSetException('Config key ' . $key . ' not set');
            }

            return $default;
        }

        return $loc;
    }

    public function has(string $key): bool
    {
        $loc = &$this->config;

        foreach (explode('.', $key) as $step) {
            if (!array_key_exists($step, $loc)) {
                return false;
            }

            $loc = &$loc[$step];
        }

        return true;
    }

    public function getFullConfig(): array
    {
        return $this->config;
    }

    /**
     * @deprecated will be removed with version 2.x, use getFullConfig() instead
     * @return array
     */
    public function toArray(): array
    {
        return $this->getFullConfig();
    }

    public function clear(): Config
    {
        $this->config = [];

        return $this;
    }

    private function arrayMergeRecursiveDistinct(array &$array1, array &$array2): array
    {
        $merged = $array1;

        foreach ($array2 as $key => &$value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged [$key] = $this->arrayMergeRecursiveDistinct($merged[$key], $value);
                continue;
            }

            $merged[$key] = $value;
        }

        return $merged;
    }
}
