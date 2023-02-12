<?php
/**
 * Set of php utils forked from Fuelphp framework
 */

namespace Velocite\Store;

/**
 * PHP store file parser
 */
class Php extends File
{
    /**
     * @var bool whether or not opcache is in use
     */
    protected static $uses_opcache = false;

    /**
     * @var bool whether or not we need to flush the opcode cache after a save
     */
    protected static $flush_needed = false;

    /**
     * @var string the extension used by this store file parser
     */
    protected $ext = '.php';

    /**
     * check the status of any opcache mechanism in use
     */
    public static function _init() : void
    {
        // do we have Opcache active?
        static::$uses_opcache = function_exists('opcache_invalidate');

        // determine if we have an opcode cache active
        static::$flush_needed = static::$uses_opcache;
    }

    /**
     * Formats the output and saved it to disk.
     *
     * @param $contents $contents    store array to save
     *
     * @return bool \File::update result
     */
    public function save(array $contents) : bool
    {
        // store the current filename
        $file = $this->file;

        // save it
        $return = parent::save($contents);

        // existing file? saved? and do we need to flush the opcode cache?
        if ($file == $this->file and $return and static::$flush_needed)
        {
            if ($this->file[0] !== '/' and ( ! isset($this->file[1]) or $this->file[1] !== ':'))
            {
                // locate the file
                $file = \Finder::search('config', $this->file, $this->ext);
            }

            // make sure we have a fallback
            $file or $file = APPPATH . 'config' . DS . $this->file . $this->ext;

            // flush the opcode caches that are active
            static::$uses_opcache and opcache_invalidate($file, true);
        }

        return $return;
    }

    /**
     * Loads in the given file and parses it.
     *
     * @param string $file File to load
     *
     * @return array
     */
    protected function load_file(string $file) : array
    {
        return include $file;
    }

    /**
     * Returns the formatted store file contents.
     *
     * @param array $contents store array
     *
     * @return string formatted store file contents
     */
    protected function export_format(array $contents) : string
    {
        $output = <<<CONF
            <?php

            CONF;
        $output .= 'return ' . str_replace(['array (' . PHP_EOL, '\'' . APPPATH, '\'' . DOCROOT, '\'' . COREPATH, '\'' . PKGPATH], ['array(' . PHP_EOL, 'APPPATH.\'', 'DOCROOT.\'', 'COREPATH.\'', 'PKGPATH.\''], var_export($contents, true)) . ";\n";

        return $output;
    }
}
