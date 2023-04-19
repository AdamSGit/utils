<?php
/**
 * Set of php utils forked from Fuelphp framework
 */

namespace Velocite;

require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'bootstrap.php';

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

    /**
     * @var int No logging
     */
    public const L_NONE = 0;

    /**
     * @var int Log everything
     */
    public const L_ALL = 99;

    /**
     * @var int Log debug massages and below
     */
    public const L_DEBUG = 100;

    /**
     * @var int Log info massages and below
     */
    public const L_INFO = 200;

    /**
     * @var int Log warning massages and below
     */
    public const L_WARNING = 300;

    /**
     * @var int Log errors only
     */
    public const L_ERROR = 400;

    /**
     * @var string The Velocite environment static attribute
     */
    public static $env = Velocite::DEVELOPMENT;

    /**
     * @var string App config location
     */
    public static $config_dir = '';

    /**
     * @var string App lang location
     */
    public static $lang_dir = '';

    /**
     * Velocite init method with config options
     *
     * @param array $config
     *
     * @return void
     */
    public static function init (array $config = []) : void
    {
        if ( ! defined ('APPPATH') and empty($config['app_path']) )
        {
            throw new Exception('app path need to be provided when initialising Velocite');
        }

        // Define app path
        ! defined ('APPPATH') and define('APPPATH', $config['app_path']);

        // Set config dir
        static::$config_dir = $config['config_dir'] ?? 'config';

        // Set lang dir
        static::$lang_dir = $config['lang_dir'] ?? 'lang';

        // Define env
        ! defined ('VELOCITE_ENV') and define('VELOCITE_ENV', $config['env'] ?? static::DEVELOPMENT);

        static::$env = VELOCITE_ENV;

        set_exception_handler(static function ($e) {
            return Errorhandler::exception_handler($e);
        });

        set_error_handler(static function ($severity, $message, $filepath, $line) {
            return Errorhandler::error_handler($severity, $message, $filepath, $line);
        });

        if (static::is_cli())
        {
            Cli::_init();
        }

        // Init package classes
        Config::load('config');
        Lang::_init();
        Finder::_init();
        Inflector::_init();
        Format::_init();
        File::_init();
        Cache::_init();
        Crypt::_init();
        Num::_init();
        Image::_init();

        // Always load config and lang
        $always_load = Config::get('always_load');

        if ( ! empty ($always_load['config']) )
        {
            foreach ($always_load['config'] as $config => $config_group)
            {
                Config::load((is_int($config) ? $config_group : $config), (is_int($config) ? true : $config_group));
            }
        }

        if ( ! empty ($always_load['language']) )
        {
            foreach ($always_load['language'] as $lang => $lang_group)
            {
                Lang::load((is_int($lang) ? $lang_group : $lang), (is_int($lang) ? true : $lang_group));
            }
        }
    }

    /**
     * Is Velocite running on the command line?
     *
     * @return boolean
     */
    public static function is_cli() : bool
    {
        return defined('STDIN');
    }

    /**
     * Cleans a file path so that it does not contain absolute file paths.
     *
     * @param   string  the filepath
     * @param string $path
     *
     * @return string the clean path
     */
    public static function clean_path(string $path) : string
    {
        // framework default paths
        $paths = [
            'APPPATH/' => APPPATH,
        ];

        // storage for the search/replace strings
        $search  = [];
        $replace = [];

        // additional paths configured than need cleaning
        $extra = Config::get('security.clean_paths', []);

        foreach ($paths + $extra as $r => $s)
        {
            $search[]  = rtrim($s, DS) . DS;
            $replace[] = rtrim($r, DS) . DS;
        }

        // clean up and return it
        return str_ireplace($search, $replace, $path);
    }
}
