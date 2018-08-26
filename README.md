# Config

Drop dead simple config package. Slim, tested, works from PHP 7.1 upwards, and won't add any other dependencies to your codebase.

I know I should have used a better name like "Configuary" or "Configaroo", probably I will change it soon ;-)

## Why one more config package?

This package started as even simplier config class I added to a project once, and I kept using and improving it. Add some point, I decided to publish it as a package. There are good reasons for using other (bigger) packages for sure, but if you  just need a configuration class that won't add any overhead to your project, it might be the right choice.

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

```php
// set the folder /app/root/config as your configuration folder
$config = new Config('/app/root/config');
// initialize the Config class with the array loaded from file /app/root/config/main_config.php
$config->init('main_config.php');
// additionally, merge the config from /app/root/config/environment/develop.php
$config->add('environment/develop.php');
```

#### Load from array

```php
$myConfiguration = [
	'key' => 'value'
]

$config = (new Config)->setConfig($myConfiguration);

$myOtherConfiguration = [
	'key2' => 'value2'
]

$config->mergeConfig($myOtherConfiguration);
```

#### Set config directly

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

$config = new Config();
$config->setConfig($myConfiguration);

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

This can reveal problems in your configuartion, however you should probably not enable it on production environments. It is meant to be used locally or during CI builds.

## Troubleshooting

1. What, no yml support out of the box? Are you kidding?
	No. Yml is a great format, but you might not use it in your project. You can still use YAML loaders like mustangostang/spyc to load the yml as array and pass it over to Config.

## TODO

1. Align function names (e.g. add() and mergeConfig() do basically the same, just from different sources)