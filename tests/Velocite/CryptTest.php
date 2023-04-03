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

    /**
     * Test Crypt
     *
     * @test
     *
     * @covers Velocite\Crypt
     * @covers Velocite\Str
     */
    public function testEncodeDecodeLargeData() : void
    {
        $bigstr     = str_repeat('this is a crypto test of 200k or so of data', 5000);
        $bigstrhash = '391828747971d26de68550d935abaffa25f043795359417199ca39c09095dd11';
        $this->assertEquals($bigstrhash, hash('sha256', $bigstr));

        // Encrypt it without a key, hash shuld be random
        $test     = Crypt::encode($bigstr);
        $testhash = '14f14589617e34ccb320972b2a1997d3827a5182b26ea2a18ee0fca144c67abb';
        $this->assertNotEquals($testhash, hash('sha256', $test));

        // Decode it
        $output= Crypt::decode($test);
        $this->assertEquals($bigstr, $output);
    }
}
