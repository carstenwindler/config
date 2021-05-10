<?php

namespace CarstenWindler\Config;

interface ConfigInterface
{
    public function get(string $key, $default = null);

    public function has(string $key): bool;
}
