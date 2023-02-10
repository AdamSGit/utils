<?php
/**
 * Set of php utils forked from Fuelphp framework
 */

namespace Velocite;

/**
 * Security class tests
 *
 * @group Core
 * @group Security
 */
class Test_Security extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests Security::htmlentities()
     *
     * @test
     */
    public function test_htmlentities_doublequote_and_ampersand() : void
    {
        $output   = Security::htmlentities('"H&M"');
        $expected = '&quot;H&amp;M&quot;';
        $this->assertEquals($expected, $output);
    }

    /**
     * Tests Security::htmlentities()
     *
     * @test
     */
    public function test_htmlentities_singlequote() : void
    {
        $output   = Security::htmlentities("'");
        $expected = '&#039;';
        $this->assertEquals($expected, $output);
    }

    /**
     * Tests Security::htmlentities()
     *
     * @test
     */
    public function test_htmlentities_charactor_references_no_double_encode() : void
    {
        $output   = Security::htmlentities('You must write & as &amp;');
        $expected = 'You must write &amp; as &amp;';
        $this->assertEquals($expected, $output);
    }

    /**
     * Tests Security::htmlentities()
     *
     * @test
     */
    public function test_htmlentities_charactor_references_double_encode() : void
    {
        $config = \Config::get('security.htmlentities_double_encode');
        \Config::set('security.htmlentities_double_encode', true);

        $output   = Security::htmlentities('You must write & as &amp;');
        $expected = 'You must write &amp; as &amp;amp;';
        $this->assertEquals($expected, $output);

        \Config::set('security.htmlentities_double_encode', $config);
    }

    /**
     * Tests Security::htmlentities()
     *
     * @test
     */
    public function test_htmlentities_double_encode() : void
    {
        $output   = Security::htmlentities('"H&M"');
        $output   = Security::htmlentities($output);
        $expected = '&quot;H&amp;M&quot;';
        $this->assertEquals($expected, $output);
    }

    /**
     * Tests Security::clean()
     *
     * @test
     */
    public function test_clean() : void
    {
        // test correct recursive cleaning
        $input = [
            [' level1 '],
            [
                [' level2 '],
                [
                    [' level3 '],
                    [
                        [' level4 '],
                    ],
                ],
            ],
        ];

        $expected = [
            ['level1'],
            [
                ['level2'],
                [
                    ['level3'],
                    [
                        ['level4'],
                    ],
                ],
            ],
        ];

        $output = Security::clean($input, ['trim']);
        $this->assertEquals($expected, $output);
    }
}
