<?php
/**
 * Set of php utils forked from Fuelphp framework
 */

namespace Velocite\Store;

use Velocite\Format;

/**
 * Yaml Config file parser
 */
class Yml extends File
{
    /**
     * @var string the extension used by this yaml file parser
     */
    protected $ext = '.yml';

    /**
     * Loads in the given file and parses it.
     *
     * @param string $file File to load
     *
     * @return array
     */
    protected function load_file(string $file) : array
    {
        $contents = file_get_contents($file);

        return Format::forge($contents, 'yaml')->to_array();
    }

    /**
     * Returns the formatted config file contents.
     *
     * @param array $contents config array
     *
     * @return string formatted config file contents
     */
    protected function export_format(array $contents) : string
    {
        return Format::forge($contents)->to_yaml();
    }
}
