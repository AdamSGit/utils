<?php
/**
 * Set of php utils forked from Fuelphp framework
 */

 namespace Velocite\Exception;

/**
 * Exception class for standard PHP errors, this will make them catchable
 */
class VelociteException extends \ErrorException
{
    public static $count = 0;

    public static $loglevel = \Fuel::L_ERROR;

    /**
     * Allow the error handler from recovering from error types defined in the config
     */
    public function recover() : void
    {
        // handle the error based on the config and the environment we're in
        if (static::$count <= \Config::get('errors.throttle', 10))
        {
            if (\Fuel::$env != \Fuel::PRODUCTION and ($this->code & error_reporting()) == $this->code)
            {
                static::$count++;
                \Errorhandler::exception_handler($this);
            }
            else
            {
                logger(static::$loglevel, $this->code . ' - ' . $this->message . ' in ' . $this->file . ' on line ' . $this->line);
            }
        }
        elseif (\Fuel::$env != \Fuel::PRODUCTION
                and static::$count                        == (\Config::get('errors.throttle', 10) + 1)
                and ($this->severity & error_reporting()) == $this->severity)
        {
            static::$count++;
            \Errorhandler::notice('Error throttling threshold was reached, no more full error reports are shown.', true);
        }
    }
}