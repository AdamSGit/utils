<?php
/**
 * Set of php utils forked from Fuelphp framework
 */

namespace Velocite\Store;

Trait Vars
{
    protected $vars = [];

    /**
     * Parses a string using all of the previously set variables.  Allows you to
     * use something like %APPPATH% in non-PHP files.
     *
     * @param string $string String to parse
     *
     * @return string
     */
    protected function parse_vars(string $string) : string
    {
        foreach ($this->vars as $var => $val)
        {
            $string = str_replace("%{$var}%", $val, $string);
        }

        return $string;
    }

    /**
     * Replaces vars to their string counterparts.
     *
     * @param array $array array to be prepped
     *
     * @return array prepped array
     */
    protected function prep_vars(array &$array) : void
    {
        static $replacements;

        if ( ! isset($replacements))
        {
            foreach ($this->vars as $i => $v)
            {
                $replacements['#^(' . preg_quote($v) . '){1}(.*)?#'] = '%' . $i . '%$2';
            }
        }

        foreach ($array as $i => $value)
        {
            if (is_string($value))
            {
                $array[$i] = preg_replace(array_keys($replacements), array_values($replacements), $value);
            }
            elseif (is_array($value))
            {
                $this->prep_vars($array[$i]);
            }
        }
    }
}
