<?php
/**
 * Set of php utils forked from Fuelphp framework
 */

namespace Velocite;

use \PHPUnit\Framework\TestCase;

/**
 * Crypt class tests
 *
 * @group Core
 * @group Crypt
 */
class CryptTest extends TestCase
{
    private static $config_backup = [];

    private static $clear_text = 'This is a string to encrypt';

    private static $cipherkey = 'a8182a9b8f9231bd6eb092be0223f3b50e6bd26ee8d71d6ceccef8e9906cc59a';

    public static function setUpBeforeClass() : void
    {
        // load and store the current crypt config
        Config::load('crypt', true);
        static::$config_backup = Config::get('crypt', []);

        // create a predictable one so we can test
        Config::set('crypt.sodium.cipherkey', 'e9fb7405ce10a96c76a9d279d5260ce4cb9ceca8774beec90da6f61d8bd2b8af');

        // init the crypt class
        Crypt::_init();
    }

    public static function tearDownAfterClass() : void
    {
        Config::set('crypt', static::$config_backup);
        Crypt::_init();
    }

    /**
     * Test Crypt
     *
     * @test
     *
     * @covers Velocite\Crypt
     * @covers Velocite\Str
     */
    public function testEncodeDecode() : void
    {
        $encoded = Crypt::encode(static::$clear_text);
        $decoded = Crypt::decode($encoded);
        $this->assertEquals(static::$clear_text, $decoded);
    }

    /**
     * Test Crypt
     *
     * @test
     *
     * @covers Velocite\Crypt
     * @covers Velocite\Str
     */
    public function testEncodeDecodeWithKey() : void
    {
        $encoded = Crypt::encode(static::$clear_text, static::$cipherkey);
        $decoded = Crypt::decode($encoded, static::$cipherkey);
        $this->assertEquals(static::$clear_text, $decoded);
    }
}
