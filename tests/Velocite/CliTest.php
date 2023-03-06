<?php
/**
 * Set of php utils forked from Fuelphp framework
 */

namespace Velocite;

use PHPUnit\Framework\TestCase;

/**
 * Cli class tests
 *
 * @group Core
 * @group Cli
 */
class CliTest extends TestCase
{
    public static $args_prev;

    public $stream;

    public static function stderr_provider()
    {
        return [
            ['foo', 'foo'],
            ['bar', 'bar'],
            ['baz', 'baz'],
        ];
    }

    public static function stdout_provider()
    {
        return [
            ['foo', 'foo'],
            ['bar', 'bar'],
            ['baz', 'baz'],
        ];
    }

    public static function color_provider()
    {
        return [
            ['red', 'foo', "\033[0;31mfoo\033[0m"],
            ['green', 'bar', "\033[0;32mbar\033[0m"],
            ['yellow', 'baz', "\033[1;33mbaz\033[0m"],
        ];
    }

    /**
     * @test
     *
     * @covers Velocite\Cli
     */
    public function test_readline_support() : void
    {
        $this->assertEquals(extension_loaded('readline'), Cli::$readline_support);
    }

    /**
     * Test Cli::option
     *
     * @test
     *
     * @covers Velocite\Cli
     */
    public function test_options() : void
    {
        $_SERVER['argc'] = 3;
        $_SERVER['argv'] = ['test.php', 'arg1', 'arg2'];
        Cli::_init_options();
        $this->assertEquals('arg1', Cli::option(1));
        $this->assertEquals('arg2', Cli::option(2));
    }

    /**
     * Test Cli::option
     *
     * @test
     *
     * @covers Velocite\Cli
     * @covers Velocite\Str
     */
    public function test_option_returns_default_value_when_option_not_defined() : void
    {
        $_SERVER['argc'] = 1;
        $_SERVER['argv'] = ['test.php'];
        Cli::_init_options();
        $this->assertEquals(null, Cli::option('foo'));
        $this->assertEquals('bar', Cli::option('foo', 'bar'));
    }

    /**
     * Test Cli::option
     *
     * @test
     *
     * @covers Velocite\Cli
     */
    public function test_set_option_sets_value_when_value_is_not_null() : void
    {
        Cli::set_option('foo', 'bar');
        $this->assertEquals('bar', Cli::option('foo'));
    }

    /**
     * Test Cli::option
     *
     * @test
     *
     * @covers Velocite\Cli
     * @covers Velocite\Str
     */
    public function test_set_option_unsets_value_when_value_is_null() : void
    {
        $_SERVER['argc'] = 2;
        $_SERVER['argv'] = ['test.php', 'foo=bar'];
        Cli::_init_options();
        Cli::set_option('foo', null);

        $this->assertNull(Cli::option('foo'));
    }

    /**
     * Test Cli::input
     *
     * @test
     *
     * @covers Velocite\Cli
     * @covers Velocite\Str
     */
    public function test_input() : void
    {
        // Cant test input with readline, as we cant mock php://stdin stream for it
        $original              = Cli::$readline_support;
        Cli::$readline_support = false;

        $expected = 'Test Input';

        fputs($this->stream, $expected);
        rewind($this->stream);
        $read = Cli::input();

        $this->assertSame($expected, $read);

        Cli::$readline_support = $original;
    }

    /**
     * Test Cli::prompt
     *
     * @test
     *
     * @covers Velocite\Cli
     * @covers Velocite\Str
     * @covers Velocite\Arr
     */
    public function test_prompt() : void
    {
        // Cant test input with readline, as we cant mock php://stdin stream for it
        $original              = Cli::$readline_support;
        Cli::$readline_support = false;

        $stream = fopen('php://temp', 'w');
        Cli::set_stdin($stream);

        fputs($stream, 'y');
        rewind($stream);
        $read = Cli::prompt('Are you ready?', ['y', 'n'], true);

        $this->assertSame('y', $read);

        fclose($stream);
        $stream = fopen('php://temp', 'w');
        Cli::set_stdin($stream);

        fputs($stream, 'y');
        rewind($stream);
        $read = Cli::prompt(['y', 'n']);

        $this->assertSame('y', $read);

        fclose($stream);
        $stream = fopen('php://temp', 'w');
        Cli::set_stdin($stream);

        fputs($stream, "\n");
        rewind($stream);
        $read = Cli::prompt('Are you ready?', 'yes');

        $this->assertSame('yes', $read);

        fclose($stream);
        $stream = fopen('php://temp', 'w');
        Cli::set_stdin($stream);

        fputs($stream, 'Yes');
        rewind($stream);
        $read = Cli::prompt('Are you ready?');

        $this->assertSame('Yes', $read);

        Cli::set_stdin($this->stream);
        fclose($stream);
        Cli::$readline_support = $original;
    }

    /**
     * Test Cli::write
     *
     * @test
     *
     * @covers Velocite\Cli
     * @covers Velocite\Str
     */
    public function test_write_with_string() : void
    {
        Cli::write('test message');
        rewind($this->stream);
        $this->assertEquals("test message\n", stream_get_contents($this->stream));
    }

    /**
     * Test Cli::write
     *
     * @test
     *
     * @covers Velocite\Cli
     * @covers Velocite\Str
     */
    public function test_write_with_array() : void
    {
        Cli::write(['line 1', 'line 2']);
        rewind($this->stream);
        $this->assertEquals("line 1\nline 2\n", stream_get_contents($this->stream));
    }

    /**
     * Test Cli::write
     *
     * @test
     *
     * @covers Velocite\Cli
     * @covers Velocite\Str
     */
    public function test_write_with_foreground() : void
    {
        Cli::write('test message', 'green');
        rewind($this->stream);
        $coloredMessage = "\033[0;32mtest message\033[0m\n";
        $this->assertEquals($coloredMessage, stream_get_contents($this->stream));
    }

    /**
     * Test Cli::write
     *
     * @test
     *
     * @covers Velocite\Cli
     * @covers Velocite\Str
     */
    public function test_write_with_background() : void
    {
        Cli::write('test message', null, 'yellow');
        rewind($this->stream);
        $coloredMessage = "\033[43mtest message\033[0m\n";
        $this->assertEquals($coloredMessage, stream_get_contents($this->stream));
    }

    /**
     * Test Cli::write
     *
     * @test
     *
     * @covers Velocite\Cli
     * @covers Velocite\Str
     */
    public function test_write_with_foreground_and_background() : void
    {
        Cli::write('test message', 'red', 'yellow');
        rewind($this->stream);
        $coloredMessage = "\033[0;31m\033[43mtest message\033[0m\n";

        $this->assertEquals($coloredMessage, stream_get_contents($this->stream));
    }

    /**
     * Test Cli::write
     *
     * @test
     *
     * @covers Velocite\Cli
     * @covers Velocite\Str
     */
    public function test_write_with_invalid_forground() : void
    {
        $this->expectException('Velocite\Exception\CliException');
        Cli::write('test message', 'invalidcolor');
    }

    /**
     * Test Cli::write
     *
     * @test
     *
     * @covers Velocite\Cli
     * @covers Velocite\Str
     */
    public function test_write_with_invalid_background() : void
    {
        $this->expectException('Velocite\Exception\CliException');
        Cli::write('test message', null, 'invalidcolor');
    }

    /**
     * Test Cli::write
     *
     * @test
     *
     * @covers Velocite\Cli
     * @covers Velocite\Str
     */
    public function test_write_with_nocolor() : void
    {
        $original     = Cli::$nocolor;
        Cli::$nocolor = true;

        Cli::write('test message', null, 'invalidcolor');
        rewind($this->stream);

        $this->assertEquals("test message\n", stream_get_contents($this->stream));

        Cli::$nocolor = $original;
    }

    /**
     * Test Cli::error
     *
     * @test
     *
     * @covers Velocite\Cli
     * @covers Velocite\Str
     */
    public function test_error() : void
    {
        Cli::error('Damn, this is a very concerning error.');
        rewind($this->stream);
        $this->assertEquals("\033[1;31mDamn, this is a very concerning error.\033[0m\n", stream_get_contents($this->stream));
    }

    /**
     * Test Cli::error
     *
     * @test
     *
     * @covers Velocite\Cli
     * @covers Velocite\Str
     */
    public function test_error_array() : void
    {
        Cli::error(['Damn, this is a', 'very concerning error.']);
        rewind($this->stream);
        $this->assertEquals("\033[1;31mDamn, this is a\nvery concerning error.\033[0m\n", stream_get_contents($this->stream));
    }

    /**
     * Test Cli::new_line
     *
     * @test
     *
     * @covers Velocite\Cli
     * @covers Velocite\Str
     */
    public function test_new_line() : void
    {
        Cli::new_line(4);
        rewind($this->stream);
        $this->assertEquals("\n\n\n\n", stream_get_contents($this->stream));
    }

    /**
     * Test Cli::clear_screen
     *
     * @test
     *
     * @covers Velocite\Cli
     * @covers Velocite\Str
     */
    public function test_clear_screen() : void
    {
        Cli::clear_screen();
        rewind($this->stream);
        $this->assertEquals("\x1B[H\x1B[2J", stream_get_contents($this->stream));
    }

    /**
     * Test Cli::beep
     *
     * @test
     *
     * @covers Velocite\Cli
     * @covers Velocite\Str
     */
    public function test_beep() : void
    {
        // Use output buffering to capture the console output
        Cli::beep(2);
        rewind($this->stream);

        // Assert that the console output contains two beep sounds
        $this->assertEquals(str_repeat("\x07", 2), stream_get_contents($this->stream));
    }

    /**
     * Test Cli::wait
     *
     * @test
     *
     * @covers Velocite\Cli
     * @covers Velocite\Str
     */
    // public function test_wait_0_sec() : void
    // {
    //     // Cant test input with readline, as we cant mock php://stdin stream for it
    //     $original              = Cli::$readline_support;
    //     Cli::$readline_support = false;

    //     // Use output buffering to capture the console output
    //     $time_start = microtime(true);

    //     fputs($this->stream, "\n");
    //     rewind($this->stream);

    //     Cli::wait(0);
    //     rewind($this->stream);

    //     // Assert that the console output contains two beep sounds
    //     $this->assertEquals(Cli::$wait_msg . "\n", stream_get_contents($this->stream));
    //     $this->assertLessThan(0.1, microtime(true) - $time_start);

    //     Cli::$readline_support = $original;
    // }

    /**
     * Test Cli::wait
     *
     * @test
     *
     * @covers Velocite\Cli
     * @covers Velocite\Str
     */
    // public function test_wait() : void
    // {
    //     // Use output buffering to capture the console output
    //     $time_start = microtime(true);

    //     Cli::wait(1);
    //     rewind($this->stream);

    //     // Assert that the console output contains two beep sounds
    //     $this->assertGreaterThan(1, microtime(true) - $time_start);
    // }

    /**
     * Test Cli::wait
     *
     * @test
     *
     * @covers Velocite\Cli
     * @covers Velocite\Str
     */
    // public function test_wait_with_countdown() : void
    // {
    //     // Use output buffering to capture the console output
    //     $time_start = microtime(true);

    //     Cli::wait(1, true);
    //     rewind($this->stream);

    //     // Assert that the console output contains two beep sounds
    //     $this->assertEquals("1... \n", stream_get_contents($this->stream));
    //     $this->assertGreaterThan(1, microtime(true) - $time_start);
    // }

    protected function setUp() : void
    {
        // Register stream wrapper "MockPhpStream" to "php://" protocol
        static::$args_prev = $_SERVER['argv'];

        $this->stream = fopen('php://memory', 'rw');
        Cli::set_stdout( $this->stream );
        Cli::set_stderr( $this->stream );
        Cli::set_stdin( $this->stream );
    }

    protected function tearDown() : void
    {
        fclose($this->stream);

        $_SERVER['argc'] = count(static::$args_prev);
        $_SERVER['argv'] = static::$args_prev;
        Cli::set_io();
    }
}
