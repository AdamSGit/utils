<?php
/**
 * Set of php utils forked from Fuelphp framework
 */

namespace Velocite;

use PHPUnit\Framework\TestCase;

/**
 * Inflector class tests
 *
 * @group Core
 * @group Inflector
 */
class InflectorTest extends TestCase
{
    public static function ordinalize_provider()
    {
        return [
            [1, 'st'],
            [21, 'st'],
            [2, 'nd'],
            [22, 'nd'],
            [3, 'rd'],
            [23, 'rd'],
            [4, 'th'],
            [24, 'th'],
            [111, 'th'],
            [112, 'th'],
            [113, 'th'],
        ];
    }

    public static function tableize_provider()
    {
        return [
            ['\\Model\\User', 'users'],
            ['\\Model\\Person', 'people'],
            ['\\Model\\Mouse', 'mice'],
            ['\\Model\\Ox', 'oxen'],
            ['\\Model\\Matrix', 'matrices'],
            ['Model_User', 'users'],
        ];
    }

    public static function get_namespace_provider()
    {
        return [
            ['\\Model\\User', 'Model\\'],
            ['\\Fuel\\Core\\Inflector', 'Fuel\\Core\\'],
            ['Model_User', ''],
        ];
    }

    /**
     * Test for Inflector::ordinalize()
     *
     * @covers Velocite\Inflector
     *
     * @dataProvider ordinalize_provider
     *
     * @param mixed $number
     * @param mixed $ending
     */
    public function test_ordinalize($number, $ending) : void
    {
        $this->assertEquals($number . $ending, Inflector::ordinalize($number));
    }

    /**
     * Test for Inflector::ordinalize()
     *
     * @covers Velocite\Inflector
     */
    public function test_ordinalize_of_string() : void
    {
        $this->assertEquals('Foo', Inflector::ordinalize('Foo'));
    }

    /**
     * Test for Inflector::ascii()
     *
     * @covers Velocite\Inflector
     * @covers Velocite\Arr
     * @covers Velocite\Str
     * @covers Velocite\Config
     * @covers Velocite\Store
     */
    public function test_ascii() : void
    {
        $output   = Inflector::ascii('Inglés');
        $expected = 'Ingles';
        $this->assertEquals($expected, $output);
    }

    /**
     * Test for Inflector::camelize()
     *
     * @covers Velocite\Inflector
     */
    public function test_camelize() : void
    {
        $output   = Inflector::camelize('apples_and_oranges');
        $expected = 'ApplesAndOranges';
        $this->assertEquals($expected, $output);
    }

    /**
     * Test for Inflector::classify()
     *
     * @covers Velocite\Inflector
     * @covers Velocite\Str
     */
    public function test_classify() : void
    {
        $output   = Inflector::classify('fuel_users');
        $expected = 'Fuel_User';
        $this->assertEquals($expected, $output);
    }

    /**
     * Test for Inflector::demodulize()
     *
     * @covers Velocite\Inflector
     */
    public function test_demodulize() : void
    {
        $output   = Inflector::demodulize('Uri::main()');
        $expected = 'main()';
        $this->assertEquals($expected, $output);
    }

    /**
     * Test for Inflector::denamespace()
     *
     * @covers Velocite\Inflector
     */
    public function test_denamespace() : void
    {
        $this->assertEquals(Inflector::denamespace('Fuel\\SomeClass'), 'SomeClass');
        $this->assertEquals(Inflector::denamespace('\\SomeClass'), 'SomeClass');
        $this->assertEquals(Inflector::denamespace('SomeClass'), 'SomeClass');
        $this->assertEquals(Inflector::denamespace('SomeClass\\'), 'SomeClass');
    }

    /**
     * Test for Inflector::foreign_key()
     *
     * @covers Velocite\Inflector
     * @covers Velocite\Str
     */
    public function test_foreign_key() : void
    {
        $output   = Inflector::foreign_key('Inflector');
        $expected = 'inflector_id';
        $this->assertEquals($expected, $output);

        $output   = Inflector::foreign_key('Inflector', false);
        $expected = 'inflectorid';
        $this->assertEquals($expected, $output);
    }

    /**
     * Test for Inflector::foreign_key()
     *
     * @covers Velocite\Inflector
     * @covers Velocite\Str
     */
    public function test_foreign_key_with_model_prefx() : void
    {
        $this->assertEquals('inflector_id', Inflector::foreign_key('Model_Inflector'));
    }

    /**
     * Test for Inflector::friendly_title()
     *
     * @covers Velocite\Inflector
     * @covers Velocite\Str
     * @covers Velocite\Arr
     * @covers Velocite\Config
     * @covers Velocite\Store
     */
    public function test_friendly_title() : void
    {
        $output   = Inflector::friendly_title('Fuel is a community driven PHP 5 web framework.');
        $expected = 'Fuel-is-a-community-driven-PHP-5-web-framework';
        $this->assertEquals($expected, $output);
    }

    /**
     * Test for Inflector::friendly_title()
     *
     * @covers Velocite\Inflector
     * @covers Velocite\Str
     * @covers Velocite\Arr
     * @covers Velocite\Config
     * @covers Velocite\Store
     */
    public function test_friendly_title_sep() : void
    {
        $output   = Inflector::friendly_title('Fuel is a community driven PHP 5 web framework.', '_');
        $expected = 'Fuel_is_a_community_driven_PHP_5_web_framework';
        $this->assertEquals($expected, $output);
    }

    /**
     * Test for Inflector::friendly_title()
     *
     * @covers Velocite\Inflector
     * @covers Velocite\Str
     * @covers Velocite\Arr
     * @covers Velocite\Config
     * @covers Velocite\Store
     */
    public function test_friendly_title_lowercase() : void
    {
        $output   = Inflector::friendly_title('Fuel is a community driven PHP 5 web framework.', '-', true);
        $expected = 'fuel-is-a-community-driven-php-5-web-framework';
        $this->assertEquals($expected, $output);
    }

    /**
     * Test for Inflector::friendly_title()
     *
     * @covers Velocite\Inflector
     * @covers Velocite\Str
     * @covers Velocite\Arr
     * @covers Velocite\Config
     * @covers Velocite\Store
     */
    public function test_friendly_title_non_ascii() : void
    {
        $output   = Inflector::friendly_title('وقود هو مجتمع مدفوعة إطار شبكة الإنترنت');
        $expected = '';
        $this->assertEquals($expected, $output);
    }

    /**
     * Test for Inflector::friendly_title()
     *
     * @covers Velocite\Inflector
     * @covers Velocite\Str
     * @covers Velocite\Arr
     * @covers Velocite\Config
     * @covers Velocite\Store
     */
    public function test_friendly_title_allow_non_ascii() : void
    {
        $output   = Inflector::friendly_title('وقود هو مجتمع مدفوعة إطار شبكة الإنترنت', '-', false, true);
        $expected = 'وقود-هو-مجتمع-مدفوعة-إطار-شبكة-الإنترنت';
        $this->assertEquals($expected, $output);
    }

    /**
     * Test for Inflector::humanize()
     *
     * @covers Velocite\Inflector
     * @covers Velocite\Str
     */
    public function test_humanize() : void
    {
        $output   = Inflector::humanize('apples_and_oranges');
        $expected = 'Apples and oranges';
        $this->assertEquals($expected, $output);
    }

    /**
     * Test for Inflector::is_countable()
     *
     * @covers Velocite\Inflector
     * @covers Velocite\Str
     */
    public function test_is_countable() : void
    {
        $output = Inflector::is_countable('fish');
        $this->assertFalse($output);

        $output = Inflector::is_countable('apple');
        $this->assertTrue($output);
    }

    /**
     * Test for Inflector::pluralize()
     *
     * @covers Velocite\Inflector
     * @covers Velocite\Str
     */
    public function test_pluralize() : void
    {
        $output   = Inflector::pluralize('apple');
        $expected = 'apples';
        $this->assertEquals($expected, $output);

        $output   = Inflector::pluralize('apple', 1);
        $expected = 'apple';
        $this->assertEquals($expected, $output);
    }

    /**
     * Test for Inflector::pluralize()
     *
     * @covers Velocite\Inflector
     * @covers Velocite\Str
     */
    public function test_pluralize_uncountable() : void
    {
        $this->assertEquals('equipment', Inflector::pluralize('equipment'));
    }

    /**
     * Test for Inflector::singularize()
     *
     * @covers Velocite\Inflector
     * @covers Velocite\Str
     */
    public function test_singularize() : void
    {
        $output   = Inflector::singularize('apples');
        $expected = 'apple';
        $this->assertEquals($expected, $output);
    }

    /**
     * Test for Inflector::singularize()
     *
     * @covers Velocite\Inflector
     * @covers Velocite\Str
     */
    public function test_singularize_uncountable() : void
    {
        $this->assertEquals('equipment', Inflector::singularize('equipment'));
    }

    /**
     * Test for Inflector::tableize()
     *
     * @covers Velocite\Inflector
     * @covers Velocite\Str
     *
     * @dataProvider tableize_provider
     *
     * @param mixed $class
     * @param mixed $table
     */
    public function test_tableize($class, $table) : void
    {
        $this->assertEquals(Inflector::tableize($class), $table);
    }

    /**
     * Test for Inflector::get_namespace()
     *
     * @covers Velocite\Inflector
     *
     * @dataProvider get_namespace_provider
     *
     * @param mixed $class
     * @param mixed $namespace
     */
    public function test_get_namespace($class, $namespace) : void
    {
        $this->assertEquals(Inflector::get_namespace($class), $namespace);
    }

    /**
     * Test for Inflector::underscore()
     *
     * @covers Velocite\Inflector
     * @covers Velocite\Str
     */
    public function test_underscore() : void
    {
        $output   = Inflector::underscore('ApplesAndOranges');
        $expected = 'apples_and_oranges';
        $this->assertEquals($expected, $output);
    }
}
