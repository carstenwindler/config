# Config

[![Latest Stable Version](https://poser.pugx.org/carstenwindler/config/v/stable)](https://packagist.org/packages/carstenwindler/config)
[![Build Status](https://travis-ci.org/carstenwindler/config.svg?branch=master)](https://travis-ci.org/carstenwindler/config)
[![Minimum PHP Version](https://img.shields.io/packagist/php-v/carstenwindler/config.svg)](https://php.net/)
[![License](https://poser.pugx.org/carstenwindler/config/license)](https://packagist.org/packages/carstenwindler/config)
[![Total Downloads](https://poser.pugx.org/carstenwindler/config/downloads)](https://packagist.org/packages/carstenwindler/config)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/carstenwindler/config/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/carstenwindler/config/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/carstenwindler/config/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/carstenwindler/config/?branch=master)
## About this package

Drop dead simple config package. Use it to bootstrap and access your configuration settings within your PHP application.

Slim, tested, works from PHP 7.3 upwards, and won't add any other dependencies to your codebase.

### Requirements

* PHP 7.3 (or above)

Yep, that's it.

### Installation

Via composer:

```bash
$ composer require carstenwindler/config
```

## Why one more config package?

This package started as even simpler config class I added to a project once, and I kept using and improving it. Add some point, I decided to publish it as a package. There are good reasons for using other (bigger) packages for sure, but if you  just need a configuration class that won't add any overhead to your project, it might be the right choice.

### Cascading configs

The basic idea behind this Config package is the idea of "Cascading configs": you don't lpad the whole config at once, but build it up step by step.

```
main.php
\- environment/develop.php
 \- markets/my_market.php
```

You start with a "main configuration" which is contains basic configuration for all purposes, and then you add more (or overwrite previous) configuration depending on the environment you are. For example, on the local development environment you might want to use different versions of web services than on production.

#### Simple "array dot" notation

Unbeatable fast and easy is to use the "array dot" notation to address configuration items (e.g. ```'lvl1.lvl2.lvl3.foo'```).

This has a big drawback though - the config is addressed using strings, which can lead to "rotting configuration" easily. Checkout below "Strict mode" section on how to fight that.

## Usage

### Initialization

There are 3 ways to set the configuration.

#### Load from file

Often enough, application configurations are simply stored in files, so this is the standard use case for Config.

Config uses PHP arrays as the only configuration format. To store arrays in files, you could e.g. use this notation:

```php
<?php

return [
    'my_config' => [
        'key1' => 'value1',
        'key2' => 'value2',
    ]
];

```

> Why just PHP arrays? Because they are simple, powerful, fast and supported out of the box. If you want or need to store your configurations in e.g. YAML format, you would need to add that functionality yourself by simply loading the YAML, convert it to an array and pass it over to Config.

```php
// load config from file with full path
$config = (new Config)->addConfigFile('/app/root/config/main_config.php');
```

```php
// set the folder /app/root/config as your configuration folder
$config = (new Config)->setConfigPath('/app/root/config');
// load config from file /app/root/config/main_config.php
$config->addConfigFile('main_config.php');
// additionally, merge the config from /app/root/config/environment/develop.php
$config->addConfigFile('environment/develop.php');
```

#### Load from array

Typically you would use this way to setup the config during tests.

```php
$myConfiguration = [
	'key' => 'value'
];

$config = (new Config)->addConfigArray($myConfiguration);

$myOtherConfiguration = [
	'key2' => 'value2'
];

$config->mergeConfig($myOtherConfiguration);
```

#### Set config directly

Typically you would use this way to setup the config during tests.

```php
$config = (new Config)->set('my.config.value.one', 12345);
```

### Retrieve and manipulate configuration during runtime

```php
$myConfiguration = [
    'lvl1' => [
        'test' => 'value',
        'lvl2' => [
            'foo' => 'wrong',
            'lvl3' => [
                'foo' => 'baz',
            ]
        ],
    ],
];

$config = (new Config)->setConfigArray($myConfiguration);

echo $config->get('lvl1.lvl2.lvl3.foo'); // echoes "baz"
echo $config->set('lvl1.lvl2.foo', 'bar');
echo $config->get('lvl1.lvl2.foo'); // echoes "bar"
```

You can specify defaults in case the desired value does not exist:

```php
echo $config->get('lvl1.lvl2.lvl3.nope', 'foo'); // echoes "foo"
```

Otherwise the return value will be null

```php
is_null($config->get('lvl1.lvl2.lvl3.nope')); // true
```

See section "Strict mode" below for more options!

## Strict mode

To avoid using config items in your code base which do not exist, you can turn on the "strict mode". In case a desired value does not exist, Config will throw an exception instead of returning null.

This can reveal problems in your configuration, however you **should not enable it on production environments**. It is meant to be used locally or during CI builds.

## Troubleshooting

1. What, no yml support out of the box? Are you kidding?
	No. Yml is a great format, but you might not use it in your project. You can still use YAML loaders like mustangostang/spyc to load the yml as array and pass it over to Config.

## TODO

1. Align function names (e.g. add() and mergeConfig() do basically the same, just from different sources)