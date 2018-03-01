<?php

namespace CarstenWindler\Config;

interface ConfigInterface
{
    public function get(string $path, $default = null);

    public function has(string $path): bool;
}
