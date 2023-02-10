<?php
/**
 * Set of php utils forked from Fuelphp framework
 */

namespace Velocite;

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

/**
 * Init class of velocite package
 */
final class Velocite
{
    public static function init ($config) : void
    {
        if ( ! defined ('VELOCITE_APPPATH') and empty($config['app_path']) )
        {
            throw new Exception('app path need to be provided when initialising Velocite');
        }

        // Define app path
        ! defined ('VELOCITE_APPPATH') and define('VELOCITE_APPPATH', $config['app_path']);

        // Define env
        ! defined ('VELOCITE_ENV') and define('VELOCITE_ENV', $config['env'] ?? 'development');

        // Init package classes
        Finder::_init();
        Inflector::_init();
    }
}
