<?php
/**
 * Set of php utils forked from Fuelphp framework
 */

namespace Velocite;

/**
 * Format class tests
 *
 * @group Core
 * @group File
 */
class FileTest extends \PHPUnit\Framework\TestCase
{
    protected static $testRootDir;

    public static function setUpBeforeClass() : void
    {
        $content           = 'Which witch switched the Swiss wristwatches?';
        $long_str          = str_repeat($content, 1237);
        self::$testRootDir = APPPATH . '/fixtures';
        shell_exec('mkdir -p ' . self::$testRootDir);
        shell_exec('echo "' . $content . '" > ' . self::$testRootDir . '/test.txt');
        shell_exec('echo "' . $long_str . '" > ' . self::$testRootDir . '/test_long.txt');
        shell_exec( 'ln -s ' . self::$testRootDir . '/test.txt ' . self::$testRootDir . '/symlink' );
    }

    // Add more test cases for other methods here

    public static function tearDownAfterClass() : void
    {
        // Remove test directory and all its contents
        shell_exec('rm -rf ' . self::$testRootDir);
    }

    /**
     * Test File::instance
     *
     * @test
     *
     * @covers Velocite\File
     */
    public function testInstance() : void
    {
        $this->assertInstanceOf('Velocite\File\Area', File::instance());
    }

    /**
     * Test File::get
     *
     * @test
     *
     * @covers Velocite\File
     */
    public function test_get() : void
    {
        $path = self::$testRootDir . '/test.txt';
        $this->assertInstanceOf('Velocite\File\Handler\File', File::get($path));
    }

    /**
     * Test File::get
     *
     * @test
     *
     * @covers Velocite\File
     */
    public function test_get_not_exists() : void
    {
        $this->expectException('Velocite\FileAccessException');
        $path = self::$testRootDir . '/unexistingfile.txt';
        File::get($path);
    }

    /**
     * Test File::get_url
     *
     * @test
     *
     * @covers Velocite\File
     */
    public function test_get_url() : void
    {
        $path = self::$testRootDir . '/test.txt';
        $this->assertSame('https://example.com/fixtures/test.txt', File::get_url($path, [], 'default'));
    }

    /**
     * Test File::exists
     *
     * @test
     *
     * @covers Velocite\File
     */
    public function test_exists() : void
    {
        $path = self::$testRootDir . '/test.txt';
        $this->assertTrue(File::exists($path));
    }

    /**
     * Test File::create
     *
     * @test
     *
     * @covers Velocite\File
     */
    public function test_create() : void
    {
        $filename = 'testfile.txt';
        $filepath = self::$testRootDir . '/' . $filename;

        // Make sure file doesn't exist
        $this->assertFalse(File::exists($filepath));

        // Create file with contents
        $contents = 'Hello, world!';
        $result   = File::create(self::$testRootDir, $filename, $contents);

        $this->assertTrue($result);

        // Check if file exists
        $this->assertTrue(File::exists($filepath));

        // Check if file contents are correct
        $this->assertEquals($contents, file_get_contents($filepath));

        // Remove file
        unlink($filepath);
    }

    /**
     * Test File::create
     *
     * @test
     *
     * @covers Velocite\File
     * @covers Velocite\Arr
     * @covers Velocite\Config
     * @covers Velocite\Str
     */
    public function test_create_dir() : void
    {
        $basepath = self::$testRootDir . '';
        $name     = 'test-create-dir';
        $path     = $basepath . '/' . $name;
        File::create_dir($basepath, $name);
        $this->assertDirectoryExists($path);
        rmdir($path);
    }

    /**
     * Test File::read
     *
     * @test
     *
     * @covers Velocite\File
     * @covers Velocite\Arr
     * @covers Velocite\Config
     * @covers Velocite\Str
     */
    public function test_read() : void
    {
        $contents = "Test file\nSecond line\n";
        File::create(self::$testRootDir, 'test_read.txt', $contents);

        $this->assertEquals($contents, File::read(self::$testRootDir . DS . 'test_read.txt', true));

        ob_start();
        File::read(self::$testRootDir . DS . 'test_read.txt');
        $output = ob_get_clean();

        $this->assertEquals($contents, $output);
    }

    /**
     * Test File::read_dir
     *
     * @test
     *
     * @covers Velocite\File
     * @covers Velocite\Arr
     * @covers Velocite\Config
     * @covers Velocite\Str
     */
    public function test_read_dir() : void
    {
        $path = self::$testRootDir . '';
        $this->assertContains('test.txt', File::read_dir($path));
    }

    /**
     * Test File::update
     *
     * @test
     *
     * @covers Velocite\File
     * @covers Velocite\Arr
     * @covers Velocite\Config
     * @covers Velocite\Str
     */
    public function test_update() : void
    {
        $basepath = self::$testRootDir . '';
        $name     = 'test-update.txt';
        $contents = 'Test update file';
        $path     = $basepath . '/' . $name;
        File::create($basepath, $name, 'Original contents');
        File::update($basepath, $name, $contents);
        $this->assertFileExists($path);
        $this->assertEquals($contents, file_get_contents($path));
        unlink($path);
    }

    /**
     * Test File::append
     *
     * @test
     *
     * @covers Velocite\File
     * @covers Velocite\Arr
     * @covers Velocite\Config
     * @covers Velocite\Str
     */
    public function test_append() : void
    {
        $basepath = self::$testRootDir . '';
        $name     = 'test-append.txt';
        $contents = 'Test append file';
        $path     = $basepath . '/' . $name;
        File::create($basepath, $name, 'Original contents');
        File::append($basepath, $name, $contents);
        $this->assertFileExists($path);
        $this->assertEquals("Original contents{$contents}", file_get_contents($path));
        unlink($path);
    }

    /**
     * Test File::get_permissions
     *
     * @test
     *
     * @covers Velocite\File
     */
    public function test_get_permissions() : void
    {
        $permissions = File::get_permissions(self::$testRootDir . '/test_long.txt');
        $this->assertSame('0644', $permissions);
    }

    /**
     * Test File::get_time
     *
     * @test
     *
     * @covers Velocite\File
     */
    public function test_get_time() : void
    {
        shell_exec('echo "File freshly created" > ' . self::$testRootDir . '/fresh.txt');
        $time         = File::get_time(self::$testRootDir . '/fresh.txt');
        $time_created = File::get_time(self::$testRootDir . '/fresh.txt', 'created');
        $this->assertSame(time(), $time);
        $this->assertSame(time(), $time_created);

        // Incorrect type
        $this->expectException('\\UnexpectedValueException');
        $time         = File::get_time(self::$testRootDir . '/fresh.txt', 'incorrect_type');
    }

    /**
     * Test File::get_permissions
     *
     * @test
     *
     * @covers Velocite\File
     */
    public function test_get_time_wrong_file() : void
    {
        $this->expectException('\\Velocite\\InvalidPathException');
        $time = File::get_time(self::$testRootDir . '/404.txt');
    }

    /**
     * Test File::get_size
     *
     * @test
     *
     * @covers Velocite\File
     */
    public function test_get_size() : void
    {
        $size = File::get_size(self::$testRootDir . '/test_long.txt');
        $this->assertSame(54429, $size);
    }

    /**
     * Test File::rename
     *
     * @test
     *
     * @covers Velocite\File
     */
    public function test_get_rename() : void
    {
        shell_exec('echo "File to rename" > ' . self::$testRootDir . '/rename.me');
        File::rename(self::$testRootDir . '/rename.me', self::$testRootDir . '/renamed');
        $this->assertTrue(File::exists(self::$testRootDir . '/renamed'));
    }

    /**
     * Test File::rename_dir
     *
     * @test
     *
     * @covers Velocite\File
     */
    public function test_get_rename_dir() : void
    {
        shell_exec('mkdir  ' . self::$testRootDir . '/testdir');
        shell_exec('echo "Hello world" > ' . self::$testRootDir . '/testdir/hello.txt');
        File::rename_dir(self::$testRootDir . '/testdir', self::$testRootDir . '/renamed_dir');
        $this->assertTrue(File::exists(self::$testRootDir . '/renamed_dir/hello.txt'));
    }

    /**
     * Test File::copy
     *
     * @test
     *
     * @covers Velocite\File
     */
    public function test_copy() : void
    {
        File::copy(self::$testRootDir . '/test.txt', self::$testRootDir . '/test_cpy.txt');
        $this->assertTrue(File::exists(self::$testRootDir . '/test_cpy.txt'));
    }

    /**
     * Test File::copy_dir
     *
     * @test
     *
     * @covers Velocite\File
     */
    public function test_get_copy_dir() : void
    {
        shell_exec('mkdir -p ' . self::$testRootDir . '/test/nested/dir');
        shell_exec('echo "Hello world" > ' . self::$testRootDir . '/test/nested/dir/hello.txt');
        File::copy_dir(self::$testRootDir . '/test/nested', self::$testRootDir . '/test/renamed');
        $this->assertTrue(File::exists(self::$testRootDir . '/test/renamed/dir/hello.txt'));
    }

    /**
     * Test File::delete
     *
     * @test
     *
     * @covers Velocite\File
     */
    public function test_delete() : void
    {
        shell_exec('echo "Hello world" > ' . self::$testRootDir . '/yamete_kudasai.txt');
        File::delete(self::$testRootDir . '/yamete_kudasai.txt');
        $this->assertFalse(File::exists(self::$testRootDir . '/yamete_kudasai.txt'));
    }

    /**
     * Test File::delete_dir
     *
     * @test
     *
     * @covers Velocite\File
     */
    public function test_get_delete_dir() : void
    {
        shell_exec('mkdir -p ' . self::$testRootDir . '/test/nested/dir');
        shell_exec('echo "Hello world" > ' . self::$testRootDir . '/test/nested/dir/hello.txt');
        File::delete_dir(self::$testRootDir . '/test/nested');
        $this->assertFalse(File::exists(self::$testRootDir . '/test/nested/dir/hello.txt'));
    }

    /**
     * Test File::symlink
     *
     * @test
     *
     * @covers Velocite\File
     */
    public function test_symlink() : void
    {
        File::symlink(self::$testRootDir . '/test.txt', self::$testRootDir . '/symlink.txt');
        $this->assertTrue(File::exists(self::$testRootDir . '/symlink.txt'));
    }

    /**
     * Test File::file_info
     *
     * @test
     *
     * @covers Velocite\File
     * @covers Velocite\Arr
     * @covers Velocite\Config
     * @covers Velocite\Str
     */
    public function test_file_info() : void
    {
        $this->assertIsArray( File::file_info(self::$testRootDir . '/symlink') );
    }

    /**
     * Test File::open_file
     *
     * @test
     *
     * @covers Velocite\File
     * @covers Velocite\Arr
     * @covers Velocite\Config
     * @covers Velocite\Str
     */
    public function test_open_file() : void
    {
        // Correct ressource
        $res = File::open_file(self::$testRootDir . '/test.txt', true, 'default');
        $this->assertIsResource( $res );
        File::close_file($res);
    }
}
