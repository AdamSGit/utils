<?php
/**
 * Bootstrap velocite package
 */

// Environment
! defined('VELOCITE_ENV') and define('VELOCITE_ENV', $_SERVER['VELOCITE_ENV'] ?? 'development');

// Make sure app path is defined
if (VELOCITE_ENV === 'test')
{
    ! defined ('APPPATH') and define('APPPATH', __DIR__);
}
else
{
    defined('APPPATH') or die('Velocite : APPPATH constant must be declared before composer autoload and point to application root path');
}

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

if ( ! function_exists('velocite_load_file') )
{
    /**
     * Includes the given file and returns the results.
     *
     * @param   string  the path to the file
     *
     * @return mixed the results of the include
     */
    function velocite_load_file (string $file) : mixed
    {
        return include $file;
    }
}
