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
    /**
     * @var string constant used for when in testing mode
     */
    public const TEST = 'test';

    /**
     * @var string constant used for when in development
     */
    public const DEVELOPMENT = 'development';

    /**
     * @var string constant used for when in production
     */
    public const PRODUCTION = 'production';

    /**
     * @var string constant used for when testing the app in a staging env
     */
    public const STAGING = 'staging';

    public static function init ($config) : void
    {
        if ( ! defined ('APPPATH') and empty($config['app_path']) )
        {
            throw new Exception('app path need to be provided when initialising Velocite');
        }

        // Define app path
        ! defined ('APPPATH') and define('APPPATH', $config['app_path']);

        // Define env
        ! defined ('VELOCITE_ENV') and define('VELOCITE_ENV', $config['env'] ?? 'development');

        set_exception_handler(static function ($e) {
            return Errorhandler::exception_handler($e);
        });

        set_error_handler(static function ($severity, $message, $filepath, $line) {
            return Errorhandler::error_handler($severity, $message, $filepath, $line);
        });

        // Init package classes
        Config::load('config');
        Lang::_init();
        Finder::_init();
        Inflector::_init();
    }
}
