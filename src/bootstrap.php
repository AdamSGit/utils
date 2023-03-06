<?php

declare(strict_types=1);

// Directory sepataror shortcut
! defined('DS') and define('DS', DIRECTORY_SEPARATOR);
// Carriage return shortcut
! defined('CRLF') and define('CRLF', chr(13) . chr(10));

/*
 * Do we have access to mbstring?
 * We need this in order to work with UTF-8 strings
 */
if ( ! defined('MBSTRING'))
{
    // we do not support mb function overloading
    if (ini_get('mbstring.func_overload'))
    {
        die('Your PHP installation is configured to overload mbstring functions. This is not supported by Velocite package');
    }

    define('MBSTRING', function_exists('mb_get_info'));
}

/*
 * A shorthand function for Lang::get()
 *
 * @param	mixed	The string to translate
 * @param	array	The parameters
 * @return	string
 */
if ( ! function_exists('__'))
{
    function __(string $line, array $params = [], $default = null, ?string $language = null)
    {
        return Velocite\Lang::get($line, $params, $default, $language);
    }
}

/*
 * A shorthand function for Lang::get_plural()
 *
 * @param	mixed	The string to translate
 * @param	array	The parameters
 * @count	integer	The elements count
 * @return	string
 */
if ( ! function_exists('__s'))
{
    function __s(string $line, array $params = [], $count = 0, $default = null, ?string $language = null)
    {
        return Velocite\Lang::get_plural($line, $params, $count, $default, $language);
    }
}
