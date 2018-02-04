<?php

namespace CarstenWindler\Config\Tests\Unit;

use CarstenWindler\Config\Config;
use CarstenWindler\Config\Exception\ConfigErrorException;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function test_init()
    {
        $config = (new Config)->init(__DIR__ . '/fixture/main.php');

        TestCase::assertInstanceOf(Config::class, $config);
    }

    public function test_throws_upon_init_if_file_is_missing()
    {
        $this->expectException(ConfigErrorException::class);

        (new Config)->init(__DIR__ . '/fixture/nope.php');
    }

    public function test_throws_upon_init_if_file_contains_no_array()
    {
        $this->expectException(ConfigErrorException::class);

        (new Config)->init(__DIR__ . '/fixture/wrong.php');
    }

    public function test_get_lvl1()
    {
        $config = (new Config)->init(__DIR__ . '/fixture/main.php');

        TestCase::assertEquals('value', $config->get('lvl1.test'));
    }

    public function test_get_lvl2()
    {
        $config = (new Config)->init(__DIR__ . '/fixture/main.php');

        TestCase::assertEquals('bar', $config->get('lvl1.lvl2.foo'));
    }

    public function test_get_lvl3()
    {
        $config = (new Config)->init(__DIR__ . '/fixture/main.php');

        TestCase::assertEquals('baz', $config->get('lvl1.lvl2.lvl3.foo'));
    }

    public function test_has()
    {
        $config = (new Config)->init(__DIR__ . '/fixture/main.php');

        TestCase::assertTrue($config->has('lvl1.lvl2.lvl3.foo'));
        TestCase::assertFalse($config->has('lvl1.lvl2.lvl3.bar'));
    }

    public function test_set_new_value()
    {
        $config = (new Config)->init(__DIR__ . '/fixture/main.php');

        TestCase::assertFalse($config->has('lvl1.lvl2.new'));

        $config->set('lvl1.lvl2.new', 'one');

        TestCase::assertEquals('one', $config->get('lvl1.lvl2.new'));
    }

    public function test_set_config()
    {
        $config = (new Config)->setConfig([ 'new' => 'value' ]);

        TestCase::assertEquals('value', $config->get('new'));
    }

    public function test_add()
    {
        $config = (new Config)
            ->init(__DIR__ . '/fixture/main.php')
            ->add(__DIR__ . '/fixture/merge_me.php');

        $result = $config->toArray();

        TestCase::assertEquals(
            [
                'lvl1' => [
                    'test' => 'value',
                    'lvl2' => [
                        'foo' => 'bar',
                        'lvl3' => [
                            'foo' => 'baz',
                        ],
                        'nice' => 'one'
                        ],
                ],
                'merge' => [
                    'me' => 'yes!',
                    'recursive' => [
                        'abso' => 'lutely',
                    ],
                ],
            ],
            $result
        );
    }

    public function test_merge_config()
    {
        $config = (new Config)->init(__DIR__ . '/fixture/main.php');

        TestCase::assertFalse($config->has('new'));

        $config->mergeConfig([ 'new' => 'value' ]);

        TestCase::assertEquals('value', $config->get('new'));
    }

    public function test_to_array()
    {
        $config = (new Config)->setConfig([ 'new' => 'value' ]);

        TestCase::assertEquals([ 'new' => 'value' ], $config->toArray());
    }

}
