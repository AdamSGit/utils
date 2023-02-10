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
class Test_Lang extends \PHPUnit\Framework\TestCase
{
    /**
     * Test for Lang::get()
     *
     * @test
     */
    public function test_line() : void
    {
        Lang::load('test');
        $output   = Lang::get('hello', ['name' => 'Bob']);
        $expected = 'Hello there Bob!';
        $this->assertEquals($expected, $output);
    }

    /**
     * Test for Lang::get()
     *
     * @test
     */
    public function test_line_invalid() : void
    {
        Lang::load('test');
        $output   = Lang::get('non_existant_hello', ['name' => 'Bob']);
        $expected = false;
        $this->assertEquals($expected, $output);
    }

    /**
     * Test for Lang::set()
     *
     * @test
     */
    public function test_set_return_true() : void
    {
        $output = Lang::set('testing_set_valid', 'Ahoy :name!');
        $this->assertNull($output);
    }

    /**
     * Test for Lang::set()
     *
     * @test
     */
    public function test_set() : void
    {
        Lang::set('testing_set_valid', 'Ahoy :name!');
        $output   = Lang::get('testing_set_valid', ['name' => 'Bob']);
        $expected = 'Ahoy Bob!';
        $this->assertEquals($expected, $output);
    }
}
