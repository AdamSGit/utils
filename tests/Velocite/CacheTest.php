<?php

namespace Velocite;

use PHPUnit\Framework\TestCase;

class CacheTest extends TestCase
{
    /**
     * Data provider
     *
     * @return array
     */
    public static function dataProvider() : array
    {
        $data =  [
            // Plain text handler
            ['test_key1', 'test_value1', null, []],
            ['test_key2', 12345, 3600, []],
            ['test_key3', 123.45, 3600, []],
            ['test_key4', ['value1', 'value2'], null, []],
            ['test_key5', ['key' => 'value'], 3600, []],
            ['test_key6', ['key1' => 'value1', 'key2' => ['subkey' => 'subvalue']], 3600, []],
            ['test_key7', ['key1' => 123, 'key2' => 456], null, []],
            ['test_key8', [['item1', 'item2'], ['item3', 'item4']], 3600, []],
            ['test_key9', new \stdClass(), 3600, []],
            ['test_key10', ['key' => new \stdClass()], null, []],
            ['test_key11', ['key1' => ['subkey1' => 'subvalue1', 'subkey2' => new \stdClass()]], 3600, []],
            ['test_key12', [['object' => new \stdClass()], ['integer' => 42]], 3600, []],
        ];

        $return = [];

        foreach ( ['file', 'apcu', 'redis', 'memcached'] as $storage )
        {
            $arr = $data;

            foreach ($arr as $k => $entry)
            {
                array_unshift ( $arr[$k], $storage);
            }

            $return = array_merge($return, $arr);
        }

        return $return;
    }

    /**
     * @test
     *
     * @covers Velocite\Cache
     * @covers Velocite\Arr
     * @covers Velocite\Config
     * @covers Velocite\Str
     */
    public function testForge() : void
    {
        $cache = Cache::forge('test_key', ['driver' => 'file']);
        $this->assertInstanceOf('Velocite\Cache\Storage\File', $cache);
    }

    /**
     * @test
     *
     * @covers Velocite\Cache
     * @covers Velocite\Arr
     * @covers Velocite\Config
     * @covers Velocite\Str
     * @covers Velocite\Store
     */
    public function testForgeWithDefaultDriver() : void
    {
        $cache = Cache::forge('test_key');
        $this->assertInstanceOf('\Velocite\Cache\Storage\File', $cache);
    }

    /**
     * @test
     *
     * @covers Velocite\Cache
     * @covers Velocite\Arr
     * @covers Velocite\Config
     * @covers Velocite\Str
     */
    public function testForgeThrowsExceptionWithInvalidDriver() : void
    {
        $this->expectException('TypeError');
        Cache::forge('test_key', ['driver' => 'invalid_driver']);
    }

    /**
     * @test
     *
     * @covers Velocite\Cache
     * @covers Velocite\Arr
     * @covers Velocite\Config
     * @covers Velocite\Str
     * @covers Velocite\Store
     * @covers Velocite\File
     *
     * @dataProvider dataProvider
     *
     * @param string $handler
     * @param string $identifier
     * @param mixed  $contents
     * @param ?int   $expiration
     * @param array  $dependencies
     */
    public function testCache(string $handler, string $identifier, $contents, $expiration, array $dependencies) : void
    {
        Config::set('cache.driver', $handler);

        // Test set()
        Cache::set($identifier, $contents, $expiration, $dependencies);

        // Test get()
        $retrievedContents = Cache::get($identifier);
        $this->assertEquals($contents, $retrievedContents);

        // Test call()
        $callback = static function ($input) {
            return $input;
        };
        $result = Cache::call($identifier, $callback, [$contents], $expiration, $dependencies);
        $this->assertEquals($contents, $result);

        // Test delete()
        Cache::delete($identifier);

        try
        {
            $retrievedContents = Cache::get($identifier, false);
        }
        catch (CacheNotFoundException $e)
        {
            $this->assertSame('not found', $e->getMessage());
        }

        // Test delete_all()
        Cache::set($identifier, $contents, $expiration, $dependencies);
        Cache::delete_all();

        try
        {
            $retrievedContents = Cache::get($identifier, false);
        }
        catch (CacheNotFoundException $e)
        {
            $this->assertSame('not found', $e->getMessage());
        }
    }
}
