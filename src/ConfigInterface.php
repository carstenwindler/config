<?php

namespace CarstenWindler\Config;

interface ConfigInterface
{
    public function clear(): Config;

    public function get(string $path, $default = null);

    public function getFullConfig(): array;

    public function has(string $path): bool;
}
