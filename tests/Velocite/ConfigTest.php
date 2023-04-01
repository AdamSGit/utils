<?php

namespace Velocite;

use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public static function config_file_provider ()
    {
        return [
            ['test'],
            ['test.json'],
            ['test.yml'],
        ];
    }

    /**
     * Test Config::load
     *
     * @test
     *
     * @dataProvider config_file_provider
     *
     * @covers Velocite\Config
     * @covers Velocite\Store
     */
    public function test_load_empty_config_throws_exception() : void
    {
        $this->expectException('\Velocite\ConfigException');
        Config::load('');
    }

    /**
     * Test Config::load
     *
     * @test
     *
     * @covers Velocite\Config
     * @covers Velocite\Store
     * @covers Velocite\Arr
     */
    public function test_load_non_existent_config_file_return_null() : void
    {
        $return = Config::load('non-existent-file');
        $this->assertNull($return);
    }

    /**
     * Test Config::load
     *
     * @test
     *
     * @covers Velocite\Config
     * @covers Velocite\Store
     * @covers Velocite\Arr
     */
    public function test_unsupported_format_throw_exception() : void
    {
        $this->expectException('\Velocite\ConfigException');
        Config::load('config.pdf');
    }

    /**
     * Test Config::load
     *
     * @test
     *
     * @dataProvider config_file_provider
     *
     * @covers Velocite\Config
     * @covers Velocite\Store
     * @covers Velocite\Arr
     * @covers Velocite\Format
     * @covers Velocite\Store\File
     * @covers Velocite\Store\Php
     * @covers Velocite\Store\Json
     * @covers Velocite\Store\Yml
     *
     * @param string $file
     */
    public function test_reload_with_reload_or_group_return_null(string $file) : void
    {
        $config  = Config::load($file);
        $config2 = Config::load($file);
        $this->assertNull($config2);
    }

    /**
     * Test Config::load
     *
     * @test
     *
     * @dataProvider config_file_provider
     *
     * @covers Velocite\Config
     * @covers Velocite\Store
     * @covers Velocite\Arr
     * @covers Velocite\Format
     * @covers Velocite\Store\File
     * @covers Velocite\Store\Php
     * @covers Velocite\Store\Json
     * @covers Velocite\Store\Yml
     *
     * @param string $file
     */
    public function test_load_config_with_no_group_stores_in_master_config(string $file) : void
    {
        Config::load($file, null, true, false);

        $this->assertArrayHasKey('array', Config::$items);
    }

    /**
     * Test Config::load
     *
     * @test
     *
     * @dataProvider config_file_provider
     *
     * @covers Velocite\Config
     * @covers Velocite\Store
     * @covers Velocite\Arr
     * @covers Velocite\Str
     * @covers Velocite\Format
     * @covers Velocite\Store\File
     * @covers Velocite\Store\Php
     * @covers Velocite\Store\Json
     * @covers Velocite\Store\Yml
     *
     * @param string $file
     */
    public function test_config_get(string $file) : void
    {
        Config::_reset();
        Config::load($file, true);

        // if ($file == 'test.json')
        // {
        //     exit(var_dump(Config::$items));
        // }
        $this->assertIsArray(Config::get('test.array'));
    }

    /**
     * Test Config::load
     *
     * @test
     *
     * @dataProvider config_file_provider
     *
     * @covers Velocite\Config
     * @covers Velocite\Store
     * @covers Velocite\Arr
     * @covers Velocite\Str
     * @covers Velocite\Format
     * @covers Velocite\Store\File
     * @covers Velocite\Store\Php
     * @covers Velocite\Store\Json
     * @covers Velocite\Store\Yml
     *
     * @param string $file
     */
    public function test_config_get_env(string $file) : void
    {
        Config::_reset();
        Config::load('testenv', true);
        $this->assertIsArray(Config::get('testenv.array'));
    }

    /**
     * Test Config::load
     *
     * @test
     *
     * @dataProvider config_file_provider
     *
     * @covers Velocite\Config
     * @covers Velocite\Store
     * @covers Velocite\Arr
     * @covers Velocite\Str
     * @covers Velocite\Format
     * @covers Velocite\Store\File
     * @covers Velocite\Store\Php
     * @covers Velocite\Store\Json
     * @covers Velocite\Store\Yml
     *
     * @param string $file
     */
    public function test_config_get_default(string $file) : void
    {
        Config::_reset();
        Config::load($file, true);
        $this->assertIsArray(Config::get('test.nonexistant', ['foo' => 'bar']));
    }

    /**
     * Test Config::load
     *
     * @test
     *
     * @dataProvider config_file_provider
     *
     * @covers Velocite\Config
     * @covers Velocite\Store
     * @covers Velocite\Arr
     * @covers Velocite\Str
     * @covers Velocite\Format
     * @covers Velocite\Store\File
     * @covers Velocite\Store\Php
     * @covers Velocite\Store\Json
     * @covers Velocite\Store\Yml
     *
     * @param string $file
     */
    public function test_config_set_non_existant_value(string $file) : void
    {
        Config::_reset();
        Config::load($file, true);
        Config::set('test.nonexistant', ['foo' => 'bar']);
        $this->assertIsArray(Config::get('test.nonexistant'));
    }

    /**
     * Test Config::load
     *
     * @test
     *
     * @dataProvider config_file_provider
     *
     * @covers Velocite\Config
     * @covers Velocite\Store
     * @covers Velocite\Arr
     * @covers Velocite\Str
     * @covers Velocite\Format
     * @covers Velocite\Store\File
     * @covers Velocite\Store\Php
     * @covers Velocite\Store\Json
     * @covers Velocite\Store\Yml
     *
     * @param string $file
     */
    public function test_config_set_existant_value(string $file) : void
    {
        Config::_reset();
        Config::load($file, true);
        $this->assertIsArray(Config::get('test.array'));
        Config::set('test.array', null);
        $this->assertNull(Config::get('test.array'));
    }

    /**
     * Test Config::load
     *
     * @test
     *
     * @dataProvider config_file_provider
     *
     * @covers Velocite\Config
     * @covers Velocite\Store
     * @covers Velocite\Arr
     * @covers Velocite\Str
     * @covers Velocite\Format
     * @covers Velocite\Store\File
     * @covers Velocite\Store\Php
     * @covers Velocite\Store\Json
     * @covers Velocite\Store\Yml
     *
     * @param string $file
     */
    public function test_config_delete(string $file) : void
    {
        Config::_reset();
        Config::load($file, true);
        $this->assertIsArray(Config::get('test.array'));
        Config::delete('test.array');
        $this->assertNull(Config::get('test.array'));
    }

    /**
     * Test Config::load
     *
     * @test
     *
     * @dataProvider config_file_provider
     *
     * @covers Velocite\Config
     * @covers Velocite\Store
     * @covers Velocite\Arr
     * @covers Velocite\Str
     * @covers Velocite\Format
     * @covers Velocite\Store\File
     * @covers Velocite\Store\Php
     * @covers Velocite\Store\Json
     * @covers Velocite\Store\Yml
     * @covers Velocite\File
     *
     * @param string $file
     */
    public function test_config_save(string $file) : void
    {
        Config::_reset();
        Config::load($file, true);
        $this->assertNull(Config::get('test.set_test'));
        Config::set('test.set_test', 'foo');
        $this->assertSame('foo', Config::get('test.set_test'));
        Config::save($file, 'test');
        Config::_reset();
        Config::load($file, true);
        $this->assertSame('foo', Config::get('test.set_test'));
        Config::delete('test.set_test');
        Config::save($file, 'test');
        Config::_reset();
        Config::load($file, true);
        $this->assertNull(Config::get('test.set_test'));
    }

    /*
     * Test Config::load
     *
     * @test
     *
     * @dataProvider config_file_provider
     *
     * @covers Velocite\Config
     * @covers Velocite\Store
     * @covers Velocite\Arr
     * @covers Velocite\Str
     * @covers Velocite\Format
     * @covers Velocite\Store\File
     * @covers Velocite\Store\Php
     * @covers Velocite\Store\Json
     * @covers Velocite\Store\Yml
     *
     * @param string $file
     */
    // public function test_config_save_env(string $file) : void
    // {
    //     $file = str_replace('test', 'testenv', $file);
    //     Config::_reset();
    //     Config::load($file, true);
    //     $this->assertNull(Config::get('testenv.set_test'));
    //     Config::set('testenv.set_test', 'foo');
    //     $this->assertSame('foo', Config::get('testenv.set_test'));
    //     Config::save($file, 'testenv', true);
    //     Config::_reset();
    //     Config::load($file, true);
    //     $this->assertSame('foo', Config::get('testenv.set_test'));
    //     Config::delete('testenv.set_test');
    //     Config::save($file, 'testenv', true);
    //     Config::_reset();
    //     Config::load($file, true);
    //     $this->assertNull(Config::get('testenv.set_test'));
    // }
}
