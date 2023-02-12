<?php
/**
 * Set of php utils forked from Fuelphp framework
 */

namespace Velocite;

use Velocite\Exception\DatabaseException;
use Velocite\Exception\VelociteException;

/**
 * Error handler class
 */
class Errorhandler
{
    public static $loglevel = \Fuel::L_ERROR;

    public static $levels = [
        0                   => 'Error',
        E_ERROR             => 'Fatal Error',
        E_WARNING           => 'Warning',
        E_PARSE             => 'Parsing Error',
        E_NOTICE            => 'Notice',
        E_CORE_ERROR        => 'Core Error',
        E_CORE_WARNING      => 'Core Warning',
        E_COMPILE_ERROR     => 'Compile Error',
        E_COMPILE_WARNING   => 'Compile Warning',
        E_USER_ERROR        => 'User Error',
        E_USER_WARNING      => 'User Warning',
        E_USER_NOTICE       => 'User Notice',
        E_STRICT            => 'Runtime Notice',
        E_RECOVERABLE_ERROR => 'Runtime Recoverable error',
        E_DEPRECATED        => 'Runtime Deprecated code usage',
        E_USER_DEPRECATED   => 'User Deprecated code usage',
    ];

    public static $fatal_levels = [E_PARSE, E_ERROR, E_USER_ERROR, E_COMPILE_ERROR];

    public static $non_fatal_cache = [];

    /**
     * Native PHP shutdown handler
     *
     * @return string
     */
    public static function shutdown_handler() : string
    {
        $last_error = error_get_last();

        // Only show valid fatal errors
        if ($last_error and in_array($last_error['type'], static::$fatal_levels))
        {
            $severity = static::$levels[$last_error['type']];
            $error    = new \ErrorException($last_error['message'], $last_error['type'], 0, $last_error['file'], $last_error['line']);
            logger(static::$loglevel, $severity . ' - ' . $last_error['message'] . ' in ' . $last_error['file'] . ' on line ' . $last_error['line'], ['exception' => $error]);

            if (VELOCITE_ENV != Velocite::PRODUCTION)
            {
                static::show_php_error($error);
            }
            else
            {
                static::show_production_error($error);
            }

            exit(1);
        }
    }

    /**
     * PHP Exception handler
     *
     * @param \Throwable $e the exception
     *
     * @return bool
     */
    public static function exception_handler(\Throwable $e) : bool
    {
        // make sure we've got something useful passed
        if ($e instanceof \Throwable)
        {
            if (method_exists($e, 'handle'))
            {
                return $e->handle();
            }

            $severity = ( ! isset(static::$levels[$e->getCode()])) ? $e->getCode() : static::$levels[$e->getCode()];
            logger(static::$loglevel, $severity . ' - ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine(), ['exception' => $e]);

            if (VELOCITE_ENV != Velocite::PRODUCTION)
            {
                static::show_php_error($e);
            }
            else
            {
                static::show_production_error($e);
            }
        }
        else
        {
            die('Something was passed to the Exception handler that was neither an Error or an Exception !!!');
        }

        return true;
    }

    /**
     * PHP Error handler
     *
     * @param int    $severity the severity code
     * @param string $message  the error message
     * @param string $filepath the path to the file throwing the error
     * @param int    $line     the line number of the error
     *
     * @return bool whether to continue with execution
     */
    public static function error_handler(int $severity, string $message, string $filepath, int $line) : bool
    {
        // don't do anything if error reporting is disabled
        if (error_reporting() !== 0)
        {
            $fatal = (bool) ( ! in_array($severity, \Config::get('errors.continue_on', [])));

            if ($fatal)
            {
                throw new VelociteException($message, $severity, 0, $filepath, $line);
            }


            // non-fatal, recover from the error
            $e = new VelociteException($message, $severity, 0, $filepath, $line);
            $e->recover();
        }

        return true;
    }

    /**
     * Shows a small notice error, only when not in production or when forced.
     * This is used by several libraries to notify the developer of certain things.
     *
     * @param string $msg         the message to display
     * @param bool   $always_show whether to force display the notice or not
     *
     * @return void
     */
    public static function notice(string $msg, bool $always_show = false) : void
    {
        $trace = array_merge(['file' => '(unknown)', 'line' => '(unknown)'], \Arr::get(debug_backtrace(), 1));
        logger(\Fuel::L_DEBUG, 'Notice - ' . $msg . ' in ' . $trace['file'] . ' on line ' . $trace['line']);

        if (\Fuel::$is_test or ( ! $always_show and (VELOCITE_ENV == Velocite::PRODUCTION or \Config::get('errors.notices', true) === false)))
        {
            return;
        }

        $data['message']    = $msg;
        $data['type']	      = 'Notice';
        $data['filepath']   = \Fuel::clean_path($trace['file']);
        $data['line']	      = $trace['line'];
        $data['function']   = $trace['function'];

        echo \View::forge('errors' . DS . 'php_short', $data, false);
    }

    /**
     * Shows an error.  It will stop script execution if the error code is not
     * in the errors.continue_on whitelist.
     *
     * @param Exception $e the exception to show
     *
     * @return void
     */
    protected static function show_php_error(Exception $e) : void
    {
        $fatal = (bool) ( ! in_array($e->getCode(), \Config::get('errors.continue_on', [])));
        $data  = static::prepare_exception($e, $fatal);

        $error_string = $data['severity'] . ' - ' . $data['message'] . ' in ' . \Fuel::clean_path($data['filepath']) . ' on line ' . $data['error_line'];

        if ( ! $fatal )
        {
            static::$non_fatal_cache[] = $data;
        }

        if (\Fuel::$is_cli)
        {
            \Cli::write(\Cli::color($error_string, 'red'));

            if (\Config::get('cli_backtrace'))
            {
                \Cli::write('Stack trace:');
                \Cli::write(\Debug::backtrace($e->getTrace()));
            }

            if ( ! $fatal)
            {
                return;
            }

            exit(1);
        }

        if ($fatal)
        {
            $data['non_fatal'] = static::$non_fatal_cache;

            exit($error_string);
        }

        try
        {
            echo \View::forge('errors' . DS . 'php_error', $data, false);
        }
        catch (\FuelException $e)
        {
            echo $e->getMessage() . '<br />';
        }
    }

    /**
     * Shows the errors/production view and exits.  This only gets
     * called when an error occurs in production mode.
     *
     * @param mixed $e
     *
     * @return void
     */
    protected static function show_production_error($e) : void
    {
        // when we're on CLI, always show the php error
        if (\Fuel::$is_cli)
        {
            static::show_php_error($e);

            return;
        }

        echo 'internal server error';
        exit(1);
    }

    protected static function prepare_exception($e, $fatal = true)
    {
        $data                   = [];
        $data['type']		         = get_class($e);
        $data['severity']	      = $e->getCode();
        $data['message']	       = $e->getMessage();
        $data['filepath']	      = $e->getFile();
        $data['error_line']     = $e->getLine();
        $data['backtrace']      = $e->getTrace();

        // support for additional DB info
        if ($e instanceof DatabaseException and $e->getDbCode())
        {
            $data['severity'] .= ' (' . $e->getDbCode() . ')';
        }

        $data['severity'] = ( ! isset(static::$levels[$data['severity']])) ? $data['severity'] : static::$levels[$data['severity']];

        foreach ($data['backtrace'] as $key => $trace)
        {
            if ( ! isset($trace['file']))
            {
                unset($data['backtrace'][$key]);
            }
        }

        $data['orig_filepath'] = $data['filepath'];
        $data['filepath']      = \Fuel::clean_path($data['filepath']);

        return $data;
    }
}
