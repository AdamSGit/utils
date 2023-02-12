<?php
/**
 * Set of php utils forked from Fuelphp framework
 */

namespace Velocite\Store;

/**
 * INI Config file parser
 */
class Ini extends File
{
    /**
     * @var string the extension used by this ini file parser
     */
    protected $ext = '.ini';

    /**
     * Loads in the given file and parses it.
     *
     * @param string $file File to load
     *
     * @return array
     */
    protected function load_file(string $file) : array
    {
        $contents = $this->parse_vars(file_get_contents($file));

        return parse_ini_string($contents, true);
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
        return $this->buildOutputString($contents);
    }

    /**
     * Generated the output of the ini file, suitable for echo'ing or
     * writing back to the ini file.
     *
     * @param array $array array of ini data
     *
     * @return string
     */
    protected function buildOutputString(array $array, array $parent = []) : string
    {
        $returnValue = '';

        foreach ($array as $key => $value)
        {
            if (is_array($value)) // Subsection case
            {
                // Merge all the sections into one array
                if (is_int($key))
                {
                    $key++;
                }
                $subSection = array_merge($parent, (array) $key);
                // Add section information to the output
                if (Arr::is_assoc($value))
                {
                    if (count($subSection) > 1)
                    {
                        $returnValue .= PHP_EOL;
                    }
                    $returnValue .= '[' . implode(':', $subSection) . ']' . PHP_EOL;
                }
                // Recursively traverse deeper
                $returnValue .= $this->buildOutputString($value, $subSection);
                $returnValue .= PHP_EOL;
            }
            elseif (isset($value))
            {
                $returnValue .= "{$key}=" . (is_bool($value) ? var_export($value, true) : $value) . PHP_EOL;
            } // Plain key->value case
        }

        return count($parent) ? $returnValue : rtrim($returnValue) . PHP_EOL;
    }
}