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
    // public static $loglevel = \Fuel::L_ERROR;

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

    /**
     * Native PHP shutdown handler
     *
     * @return string
     */
    public static function shutdown_handler() : void
    {
        $last_error = error_get_last();

        // Only show valid fatal errors
        if ($last_error and in_array($last_error['type'], static::$fatal_levels))
        {
            $severity = static::$levels[$last_error['type']];
            $error    = new \ErrorException($last_error['message'], $last_error['type'], 0, $last_error['file'], $last_error['line']);
            // logger(static::$loglevel, $severity . ' - ' . $last_error['message'] . ' in ' . $last_error['file'] . ' on line ' . $last_error['line'], ['exception' => $error]);

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
        if ( ! $e instanceof \Throwable )
        {
            die('Something was passed to the Exception handler that was not throwable');
        }

        if (method_exists($e, 'handle'))
        {
            return $e->handle();
        }

        $severity = ( ! isset(static::$levels[$e->getCode()])) ? $e->getCode() : static::$levels[$e->getCode()];
        // logger(static::$loglevel, $severity . ' - ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine(), ['exception' => $e]);

        if (VELOCITE_ENV != Velocite::PRODUCTION)
        {
            static::show_php_error($e);
        }
        else
        {
            static::show_production_error($e);
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
            $fatal = (bool) ( ! in_array($severity, Config::get('errors.continue_on', [])));

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
        $trace = array_merge(['file' => '(unknown)', 'line' => '(unknown)'], Arr::get(debug_backtrace(), 1));
        // logger(\Fuel::L_DEBUG, 'Notice - ' . $msg . ' in ' . $trace['file'] . ' on line ' . $trace['line']);

        if ( in_array(VELOCITE_ENV, [Velocite::TEST, Velocite::PRODUCTION]) or Config::get('errors.notices', true) === false)
        {
            return;
        }

        $data['message']        = $msg;
        $data['severity']	      = static::$levels[E_NOTICE];
        $data['type']	          = 'Notice';
        $data['filepath']       = Velocite::clean_path($trace['file']);
        $data['line']	          = $trace['line'];
        $data['function']       = $trace['function'];

        echo static::format_string($data);
    }

    /**
     * Shows an error.  It will stop script execution if the error code is not
     * in the errors.continue_on whitelist.
     *
     * @param \Throwable $e the exception to show
     *
     * @return void
     */
    protected static function show_php_error(\Throwable $e) : void
    {
        $fatal = (bool) ( ! in_array($e->getCode(), Config::get('errors.continue_on', [])));
        $data  = static::prepare_exception($e, $fatal);

        if (Velocite::is_cli() and ! Config::get('cli_backtrace'))
        {
            unset($data['backtrace']);
        }

        switch(Config::get('errors.display_format'))
        {
            case 'xml':
                $error = Format::forge($data)->to_xml();

                break;

            case 'json':
                $error = Format::forge($data)->to_json();

                break;

            case 'print_r':
                $error = print_r($data, true);

                break;

            default:
                $error = static::format_string($data);

                break;
        }

        if (Velocite::is_cli())
        {
            Cli::write(Cli::color($error, 'red'));

            if ( ! $fatal)
            {
                return;
            }

            exit(1);
        }

        if ( Velocite::$env === Velocite::PRODUCTION or ! $fatal)
        {
            return;
        }

        exit($error);
    }

    /**
     * Shows the errors/production view and exits.  This only gets
     * called when an error occurs in production mode.
     *
     * @param \Throwable $e
     *
     * @return void
     */
    protected static function show_production_error(\Throwable $e) : void
    {
        // when we're on CLI, always show the php error
        if (Velocite::is_cli())
        {
            static::show_php_error($e);

            return;
        }

        echo 'internal server error';
        exit(1);
    }

    /**
     * Prepare exception data
     *
     * @param \Throwable $e     The throwable
     * @param boolean    $fatal Is the exception fatal
     *
     * @return array Formatted exception data
     */
    protected static function prepare_exception(\Throwable $e, bool $fatal = true) : array
    {
        $data                   = [];
        $data['type']		         = get_class($e);
        $data['severity']	      = $e->getCode();
        $data['message']	       = $e->getMessage();
        $data['filepath']	      = $e->getFile();
        $data['line']           = $e->getLine();
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
        $data['filepath']      = Velocite::clean_path($data['filepath']);

        return $data;
    }

    /**
     * Format exception string
     *
     * @param array $data Exception data
     *
     * @return string Formatted error string
     */
    protected static function format_string(array $data, bool $include_trace = true) : string
    {
        extract($data);
        $filepath     = Velocite::clean_path($filepath);
        $error_string = "{$severity} - {$type} : « {$message} » in {$filepath} on line {$line}";

        if ($include_trace and ! empty($data['backtrace']))
        {
            $error_string .= "\nTrace:\n";

            foreach ($data['backtrace'] as $line)
            {
                $error_string .= "{$line['file']} on line {$line['line']}\n";
            }
        }

        return $error_string;
    }
}
