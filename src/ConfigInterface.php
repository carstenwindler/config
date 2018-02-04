<?php

namespace CarstenWindler\Config;

interface ConfigInterface
{
    public function get(string $path);

    public function has(string $path): bool;
}
