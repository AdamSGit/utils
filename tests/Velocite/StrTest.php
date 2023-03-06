<?php
/**
 * Set of php utils forked from Fuelphp framework
 */

namespace Velocite;

use PHPUnit\Framework\TestCase;

/**
 * Str class tests
 *
 * @group Core
 * @group Str
 */
class StrTest extends TestCase
{
    public static function truncate_provider()
    {
        return [
            ['abcdef', 4, 'abcd...', '...', false],
            ['abcdef', 6, 'abcdef', '...', false],
            ['abcdef', 6, 'abcdef', '', false],
            ['<b>abc</b>def', 4, '<b>abc</b>d...', '...', true],
            ['<b>abc</b>def', 6, '<b>abc</b>def', '...', true],
            ['<b>abc</b>def', 6, '<b>abc</b>def', '', true],
            ['<p>This is a long paragraph that should be truncated.</p>', 20, '<p>This is a long parag...</p>', '...', true],
        ];
    }

    /**
     * Test for Str::truncate()
     *
     * @covers Velocite\Str
     *
     * @dataProvider truncate_provider
     *
     * @param mixed $string
     * @param mixed $limit
     * @param mixed $expected
     * @param mixed $continuation
     * @param mixed $is_html
     */
    public function test_truncate($string, $limit, $expected, $continuation = '...', $is_html = false) : void
    {
        $this->assertEquals($expected, Str::truncate($string, $limit, $continuation, $is_html));
    }

    /**
     * Test for Str::increment()
     *
     * @covers Velocite\Str
     */
    public function test_increment() : void
    {
        $values = ['valueA', 'valueB', 'valueC'];

        for ($i = 0; $i < count($values); $i ++)
        {
            $output   = Str::increment($values[$i], $i);
            $expected = $values[$i] . '_' . $i;

            $this->assertEquals($expected, $output);
        }
    }

    /**
     * Test for Str::lower()
     *
     * @covers Velocite\Str
     */
    public function test_lower() : void
    {
        $output   = Str::strtolower('HELLO WORLD');
        $expected = 'hello world';

        $this->assertEquals($expected, $output);
    }

    /**
     * Test for Str::upper()
     *
     * @covers Velocite\Str
     */
    public function test_upper() : void
    {
        $output   = Str::strtoupper('hello world');
        $expected = 'HELLO WORLD';

        $this->assertEquals($expected, $output);
    }

    /**
     * Test for Str::lcfirst()
     *
     * @covers Velocite\Str
     */
    public function test_lcfirst() : void
    {
        $output   = Str::lcfirst('Hello World');
        $expected = 'hello World';

        $this->assertEquals($expected, $output);
    }

    /**
     * Test for Str::ucfirst()
     *
     * @covers Velocite\Str
     */
    public function test_ucfirst() : void
    {
        $output   = Str::ucfirst('hello world');
        $expected = 'Hello world';

        $this->assertEquals($expected, $output);
    }

    /**
     * Test for Str::ucwords()
     *
     * @covers Velocite\Str
     */
    public function test_ucwords() : void
    {
        $output   = Str::ucwords('hello world');
        $expected = 'Hello World';

        $this->assertEquals($expected, $output);
    }

    /**
     * Test for Str::random()
     *
     * @covers Velocite\Str
     */
    public function test_random_basic() : void
    {
        $str = Str::random('basic');
        $this->assertIsNumeric($str);
    }

    /**
     * Test for Str::random()
     *
     * @covers Velocite\Str
     */
    public function test_random_alnum() : void
    {
        $str = Str::random('alnum', 8);
        $this->assertEquals(8, strlen($str));
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]+$/', $str);
    }

    /**
     * Test for Str::random()
     *
     * @covers Velocite\Str
     */
    public function test_random_numeric() : void
    {
        $str = Str::random('numeric', 6);
        $this->assertEquals(6, strlen($str));
        $this->assertMatchesRegularExpression('/^[0-9]+$/', $str);
    }

    /**
     * Test for Str::random()
     *
     * @covers Velocite\Str
     */
    public function test_random_nozero() : void
    {
        $str = Str::random('nozero', 5);
        $this->assertEquals(5, strlen($str));
        $this->assertMatchesRegularExpression('/^[1-9]+$/', $str);
    }

    /**
     * Test for Str::random()
     *
     * @covers Velocite\Str
     */
    public function test_random_alpha() : void
    {
        $str = Str::random('alpha', 10);
        $this->assertEquals(10, strlen($str));
        $this->assertMatchesRegularExpression('/^[a-zA-Z]+$/', $str);
    }

    /**
     * Test for Str::random()
     *
     * @covers Velocite\Str
     */
    public function test_random_distinct() : void
    {
        $str = Str::random('distinct', 7);
        $this->assertEquals(7, strlen($str));
        $this->assertMatchesRegularExpression('/^[2345679ACDEFHJKLMNPRSTUVWXYZ]+$/', $str);
    }

    /**
     * Test for Str::random()
     *
     * @covers Velocite\Str
     */
    public function test_random_hexdec() : void
    {
        $str = Str::random('hexdec', 12);
        $this->assertEquals(12, strlen($str));
        $this->assertMatchesRegularExpression('/^[0-9a-f]+$/', $str);
    }

    /**
     * Test for Str::random()
     *
     * @covers Velocite\Str
     */
    public function test_random_unique() : void
    {
        $str1 = Str::random('unique');
        $str2 = Str::random('unique');
        $this->assertNotEquals($str1, $str2);
    }

    /**
     * Test for Str::random()
     *
     * @covers Velocite\Str
     */
    public function test_random_sha_1() : void
    {
        $str = Str::random('sha1');
        $this->assertEquals(40, strlen($str));
        $this->assertMatchesRegularExpression('/^[0-9a-f]+$/', $str);
    }

    /**
     * Test for Str::random()
     *
     * @covers Velocite\Str
     */
    public function test_random_uuid() : void
    {
        $str = Str::random('uuid');
        $this->assertEquals(36, strlen($str));
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $str);
    }

    /**
     * Test for Str::is_json()
     *
     * @covers Velocite\Str
     */
    public function test_is_json() : void
    {
        $values = ['fuelphp', 'is' => ['awesome' => true]];

        $string = json_encode($values);
        $this->assertTrue(Str::is_json($string));

        $string = serialize($values);
        $this->assertFalse(Str::is_json($string));
    }

    /**
     * Test for Str::is_xml()
     *
     * @covers Velocite\Str
     *
     * @requires extension libxml
     */
    public function test_is_xml() : void
    {
        $valid_xml = '<?xml version="1.0" encoding="UTF-8"?>
					<phpunit colors="true" stopOnFailure="false" bootstrap="bootstrap_phpunit.php">
						<php>
							<server name="doc_root" value="../../"/>
							<server name="app_path" value="fuel/app"/>
							<server name="core_path" value="fuel/core"/>
							<server name="package_path" value="fuel/packages"/>
						</php>
					</phpunit>';

        $invalid_xml = '<?xml version="1.0" encoding="UTF-8"?>
					<phpunit colors="true" stopOnFailure="false" bootstrap="bootstrap_phpunit.php">
						<php>
							<server name="doc_root" value="../../"/>
							<server name="app_path" value="fuel/app"/>
							<server name="core_path" value="fuel/core"/>
							<server name="package_path" value="fuel/packages"/>
						</
					</phpunit>';

        $this->assertTrue(Str::is_xml($valid_xml));
        $this->assertFalse(Str::is_xml($invalid_xml));
    }

    /**
     * Test for Str::is_serialized()
     *
     * @covers Velocite\Str
     * @covers Velocite\Arr
     * @covers Velocite\Config
     */
    public function test_is_serialized() : void
    {
        $values = ['fuelphp', 'is' => ['awesome' => true]];

        $string = json_encode($values);
        $this->assertFalse(Str::is_serialized($string));

        $string = serialize($values);
        $this->assertTrue(Str::is_serialized($string));
    }

    /**
     * Test for Str::is_html()
     *
     * @covers Velocite\Str
     */
    public function test_is_html() : void
    {
        $html          = '<div class="row"><div class="span12"><strong>FuelPHP</strong> is a simple, flexible, <i>community<i> driven PHP 5.3 web framework based on the best ideas of other frameworks with a fresh start.</p>';
        $simple_string = strip_tags($html);

        $this->assertTrue(Str::is_html($html));
        $this->assertFalse(Str::is_html($simple_string));
    }

    /**
     * Test for Str::starts_with()
     *
     * @covers Velocite\Str
     */
    public function test_starts_with() : void
    {
        $string = 'HELLO WORLD';

        $output = Str::starts_with($string, 'HELLO');
        $this->assertTrue($output);

        $output = Str::starts_with($string, 'hello');
        $this->assertFalse($output);

        $output = Str::starts_with($string, 'hello', true);
        $this->assertTrue($output);
    }

    /**
     * Test for Str::ends_with()
     *
     * @covers Velocite\Str
     */
    public function test_ends_with() : void
    {
        $string = 'HELLO WORLD';

        $output = Str::ends_with($string, 'WORLD');
        $this->assertTrue($output);

        $output = Str::ends_with($string, 'world');
        $this->assertFalse($output);

        $output = Str::ends_with($string, 'world', true);
        $this->assertTrue($output);
    }

    /**
     * Test for Str::alternator()
     *
     * @covers Velocite\Str
     */
    public function test_alternator() : void
    {
        $alt = Str::alternator('one', 'two', 'three');

        $output   = $alt();
        $expected = 'one';
        $this->assertEquals($output, $expected);

        $output   = $alt(false);
        $expected = 'two';
        $this->assertEquals($output, $expected);

        $output   = $alt();
        $expected = 'two';
        $this->assertEquals($output, $expected);

        $output   = $alt();
        $expected = 'three';
        $this->assertEquals($output, $expected);

        $output   = $alt();
        $expected = 'one';
        $this->assertEquals($output, $expected);
    }

    /**
     * Test for Str::tr()
     *
     * @covers Velocite\Str
     */
    public function test_tr_with_empty_string() : void
    {
        $this->assertEquals('', Str::tr(''));
    }

    /**
     * Test for Str::tr()
     *
     * @covers Velocite\Str
     */
    public function test_tr_with_no_params() : void
    {
        $this->assertEquals('Hello world!', Str::tr('Hello world!'));
    }

    /**
     * Test for Str::tr()
     *
     * @covers Velocite\Str
     */
    public function test_tr_with_params() : void
    {
        $this->assertEquals('Hello, John!', Str::tr('Hello, :name!', [ 'name' => 'John' ]));
        $this->assertEquals('The answer is 42.', Str::tr('The answer is :number.', [ 'number' => 42 ]));
        $this->assertEquals('One: 1, Two: 2, Three: 3', Str::tr('One: :one, Two: :two, Three: :three', [ 'one' => 1, 'two' => 2, 'three' => 3 ]));
    }

    /**
     * Test for Str::tr()
     *
     * @covers Velocite\Str
     */
    public function test_tr_with_colon_prefix() : void
    {
        $this->assertEquals('Hello, John!', Str::tr('Hello, :name!', [ ':name' => 'John' ]));
    }

    /**
     * Test for Str::tr()
     *
     * @covers Velocite\Str
     */
    public function test_tr_with_invalid_params() : void
    {
        $this->assertEquals('Hello world!', Str::tr('Hello world!', [ 'name' => 'John' ]));
    }

    /**
     * Test for Str::tr()
     *
     * @covers Velocite\Str
     */
    public function test_diverse_str_methods() : void
    {
        $haystack = 'The quick brown fox jumps over the lazy dog.';
        $needle   = 'brown';
        $offset   = 10;
        $this->assertSame($offset, Str::strpos($haystack, $needle, $offset));
        $this->assertSame($offset, Str::strrpos($haystack, $needle, $offset));
        $this->assertSame($offset, Str::stripos($haystack, $needle, $offset));
        $this->assertSame($offset, Str::strripos($haystack, $needle, $offset));
        $this->assertSame('The quick ', Str::strstr($haystack, $needle, $offset));
        $this->assertSame('The quick ', Str::stristr($haystack, $needle, $offset));
        $this->assertSame('The quick ', Str::strrchr($haystack, $needle, $offset));
        $this->assertSame(1, Str::substr_count($haystack, $needle));
    }

    /**
     * Test for Str::tr()
     *
     * @covers Velocite\Str
     */
    public function test_strpos_with_encoding() : void
    {
        $haystack = 'Thé quick brówn fôx jumps över the lazy dog.';
        $needle   = 'b';
        $offset   = 10;
        $encoding = 'UTF-8';

        $actual = Str::strpos($haystack, $needle, $offset, $encoding);
        $this->assertSame($offset, $actual);
    }

    /**
     * Test for Str::tr()
     *
     * @covers Velocite\Str
     */
    public function test_strpos_not_found() : void
    {
        $haystack = 'The quick brown fox jumps over the lazy dog.';
        $needle   = 'cat';
        $offset   = 0;
        $actual   = Str::strpos($haystack, $needle, $offset);
        $this->assertFalse($actual);
    }
}
