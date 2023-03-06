<?php
/**
 * Set of php utils forked from Fuelphp framework
 */

namespace Velocite;

/**
 * Lang class tests
 *
 * @group Core
 * @group Lang
 */
class LangTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test Lang::land()
     *
     * @test
     *
     * @covers Velocite\Lang
     * @covers Velocite\Store
     * @covers Velocite\Arr
     * @covers Velocite\Config
     * @covers Velocite\Str
     */
    public function test_group() : void
    {
        Lang::_reset();

        Lang::load('test', 'somegroup');
        $output   = __('somegroup.hello', ['name' => 'Bob']);
        $expected = 'Hello there Bob!';
        $this->assertSame($expected, $output);
    }

    /**
     * Test Lang::land()
     *
     * @test
     *
     * @covers Velocite\Lang
     * @covers Velocite\Store
     * @covers Velocite\Arr
     * @covers Velocite\Config
     * @covers Velocite\Str
     */
    public function test_nested_lang() : void
    {
        Lang::_reset();

        Lang::load('nested/test', true);

        $output   = __('nested/test.hello', ['name' => 'Bob']);
        $expected = 'Hello there Bob!';
        $this->assertSame($expected, $output);
    }

    /**
     * Test Lang::get()
     *
     * @test
     *
     * @covers Velocite\Lang
     * @covers Velocite\Store
     * @covers Velocite\Arr
     * @covers Velocite\Config
     * @covers Velocite\Str
     */
    public function test_line() : void
    {
        Lang::_reset();

        Lang::load('test');
        $output   = __('hello', ['name' => 'Bob']);
        $expected = 'Hello there Bob!';
        $this->assertSame($expected, $output);

        // Test change language
        Lang::set_lang('fr', true);

        $output   = __('hello', ['name' => 'Bob']);
        $expected = 'Salut Bob !';
        $this->assertSame($expected, $output);
    }

    /**
     * Test Lang::get_plural()
     *
     * @test
     *
     * @covers Velocite\Lang
     * @covers Velocite\Store
     * @covers Velocite\Arr
     * @covers Velocite\Config
     * @covers Velocite\Str
     */
    public function test_plural() : void
    {
        Lang::_reset();

        Lang::load('test');
        $output   = __s('star', [], 0);
        $expected = 'One star';
        $this->assertSame($expected, $output);
        $output   = __s('star', [], 1);
        $expected = 'Two stars';
        $this->assertSame($expected, $output);
        $output   = __s('star', [], 2);
        $expected = 'A lot of stars';
        $this->assertSame($expected, $output);

        $output   = __s('bird', [], 23);
        $expected = 'Two birds';
        $this->assertSame($expected, $output);

        $output   =  __s('hello', ['name' => 'Bob'], 5);
        $expected = 'Hello there Bob!';
        $this->assertSame($expected, $output);
    }

    /**
     * Test Lang::get()
     *
     * @test
     *
     * @covers Velocite\Lang
     * @covers Velocite\Store
     * @covers Velocite\Arr
     * @covers Velocite\Config
     * @covers Velocite\Str
     */
    public function test_line_invalid() : void
    {
        Lang::_reset();

        Lang::load('test');
        $output   = Lang::get('non_existant_hello', ['name' => 'Bob']);
        $expected = false;
        $this->assertEquals($expected, $output);
    }

    /**
     * Test Lang::set()
     *
     * @test
     *
     * @covers Velocite\Lang
     * @covers Velocite\Store
     * @covers Velocite\Arr
     * @covers Velocite\Config
     * @covers Velocite\Str
     */
    public function test_set_return_true() : void
    {
        Lang::_reset();

        $output = Lang::set('testing_set_valid', 'Ahoy :name!');
        $this->assertNull($output);
    }

    /**
     * Test Lang::set()
     *
     * @test
     *
     * @covers Velocite\Lang
     * @covers Velocite\Store
     * @covers Velocite\Arr
     * @covers Velocite\Config
     * @covers Velocite\Str
     */
    public function test_set() : void
    {
        Lang::_reset();

        Lang::set('testing_set_valid', 'Ahoy :name!');
        $output   = Lang::get('testing_set_valid', ['name' => 'Bob']);
        $expected = 'Ahoy Bob!';
        $this->assertEquals($expected, $output);
    }

    /**
     * Test Lang::delete()
     *
     * @test
     *
     * @covers Velocite\Lang
     * @covers Velocite\Store
     * @covers Velocite\Arr
     * @covers Velocite\Config
     * @covers Velocite\Str
     */
    public function test_delete_and_save() : void
    {
        Lang::_reset();

        Lang::load('test', true);
        Lang::delete('test.hello');

        Lang::save('test', 'test');

        Lang::_reset();
        Lang::load('test', true);

        $default = 'default_value';
        $this->assertEquals($default, Lang::get('test.hello', ['name' => 'Bob'], $default));

        Lang::set('test.hello', 'Hello there :name!');
        Lang::save('test', 'test');
    }
}
