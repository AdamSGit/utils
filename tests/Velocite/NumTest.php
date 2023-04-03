<?php
/**
 * Set of php utils forked from Fuelphp framework
 */

namespace Velocite;

use \PHPUnit\Framework\TestCase;

/**
 * Numeric helper tests
 */
class NumTest extends TestCase
{
    /**
     * Test Num::bytes()
     *
     * @test
     *
     * @covers Velocite\Num
     * @covers Velocite\Arr
     */
    public function testBytes() : void
    {
        $output   = Num::bytes('200K');
        $expected = '204800';

        $this->assertEquals($expected, $output);
    }

    /**
     * Test Num::bytes()
     *
     * @test
     *
     * @covers Velocite\Num
     */
    public function testBytesException() : void
    {
        $this->expectException('\\Velocite\\VelociteException');

        $output = Num::bytes('invalid');
    }

    /**
     * Test Num::format_bytes()
     *
     * @test
     *
     * @covers Velocite\Num
     */
    public function testFormatBytes() : void
    {
        $output   = Num::format_bytes('204800');
        $expected = '200 KB';

        $this->assertEquals($expected, $output);

        $this->expectException('InvalidArgumentException');
        $output = Num::format_bytes('invalid');
    }

    /**
     * Test Num::quantity()
     *
     * @test
     *
     * @covers Velocite\Num
     */
    public function testQuantity() : void
    {
        // Return the same
        $output   = Num::quantity('100');
        $expected = '100';

        $this->assertEquals($expected, $output);

        $output   = Num::quantity('7500');
        $expected = '8K';

        $this->assertEquals($expected, $output);

        $output   = Num::quantity('1500000');
        $expected = '2M';

        $this->assertEquals($expected, $output);

        $output   = Num::quantity('1000000000');
        $expected = '1B';

        $this->assertEquals($expected, $output);

        // Get current localized decimal separator
        $locale_conv   = localeconv();
        $decimal_point = $locale_conv['decimal_point'] ?? '.';

        $output   = Num::quantity('7500', 1);
        $expected = '7' . $decimal_point . '5K';

        $this->assertEquals($expected, $output);
    }

    /**
     * Test Num::Format()
     *
     * @test
     *
     * @covers Velocite\Num
     */
    public function testFormat() : void
    {
        $output   = Num::format('1234567890', '(000) 000-0000');
        $expected = '(123) 456-7890';

        $this->assertEquals($expected, $output);

        $output = Num::format(null, '(000) 000-0000');
        $this->assertNull($output);

        $output   = Num::format('1234567890', null);
        $expected = '1234567890';

        $this->assertEquals($expected, $output);
    }

    /**
     * Test Num::mask_string()
     *
     * @test
     *
     * @covers Velocite\Num
     */
    public function testMaskString() : void
    {
        $output   = Num::mask_string('1234567812345678', '**** - **** - **** - 0000', ' -');
        $expected = '**** - **** - **** - 5678';

        $this->assertEquals($expected, $output);

        // Return the same
        $output   = Num::mask_string('1234567812345678');
        $expected = '1234567812345678';

        $this->assertEquals($expected, $output);
    }
}
