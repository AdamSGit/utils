<?php

namespace Velocite\Utils;

class SecurityException extends \DomainException
{
}

/**
 * Security Class
 */
class Security
{
    /**
     * Cleans the request URI
     *
     * @param string $uri    uri to clean
     * @param bool   $strict whether to remove relative directories
     *
     * @return array|mixed
     */
    public static function clean_uri(string $uri, bool $strict = false)
    {
        $filters = \Config::get('security.uri_filter', []);
        $filters = is_array($filters) ? $filters : [$filters];

        $strict and $uri = str_replace(['//', '../'], '/', $uri);

        return static::clean($uri, $filters);
    }

    /**
     * Cleans the global $_GET, $_POST and $_COOKIE arrays
     */
    public static function clean_input() : void
    {
        $_GET		  = static::clean($_GET);
        $_POST		 = static::clean($_POST);
        $_COOKIE	= static::clean($_COOKIE);
    }

    /**
     * Generic variable clean method
     *
     * @param mixed  $var
     * @param mixed  $filters
     * @param string $type
     *
     * @return array|mixed
     */
    public static function clean($var, $filters = null, string $type = 'security.input_filter')
    {
        // deal with objects that can be sanitized
        if ($var instanceof \Sanitization)
        {
            $var->sanitize();
        }

        // deal with array's or array emulating objects
        elseif (is_array($var) or ($var instanceof \Traversable and $var instanceof \ArrayAccess))
        {
            // recurse on array values
            foreach ($var as $key => $value)
            {
                $var[$key] = static::clean($value, $filters, $type);
            }
        }

        // deal with all other variable types
        else
        {
            null === $filters and $filters = \Config::get($type, []);
            $filters                       = is_array($filters) ? $filters : [$filters];

            foreach ($filters as $filter)
            {
                // is this filter a callable local function?
                if (is_string($filter) and is_callable(static::class . '::' . $filter))
                {
                    $var = static::$filter($var);
                }

                // is this filter a callable function?
                elseif (is_callable($filter))
                {
                    $var = call_user_func($filter, $var);
                }

                // assume it's a regex of characters to filter
                else
                {
                    $var = preg_replace('#[' . $filter . ']#ui', '', $var);
                }
            }
        }

        return $var;
    }

    public static function strip_tags($value)
    {
        if ( ! is_array($value))
        {
            $value = filter_var(strip_tags($value), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        }
        else
        {
            foreach ($value as $k => $v)
            {
                $value[$k] = static::strip_tags($v);
            }
        }

        return $value;
    }

    public static function htmlentities($value, $flags = null, $encoding = 'UTF-8', $double_encode = null)
    {
        static $already_cleaned = [];

        null === $flags         and $flags                 = \Config::get('security.htmlentities_flags', ENT_QUOTES);
        null === $double_encode and $double_encode         = \Config::get('security.htmlentities_double_encode', false);

        // Nothing to escape for non-string scalars, or for already processed values
        if (null === $value or is_bool($value) or is_int($value) or is_float($value) or in_array($value, $already_cleaned, true))
        {
            return $value;
        }

        if (is_string($value))
        {
            $value = htmlentities($value, $flags, $encoding, $double_encode);
        }
        elseif (is_object($value) and $value instanceof \Sanitization)
        {
            $value->sanitize();

            return $value;
        }
        elseif (is_array($value) or ($value instanceof \Iterator and $value instanceof \ArrayAccess))
        {
            // Add to $already_cleaned variable when object
            is_object($value) and $already_cleaned[] = $value;

            foreach ($value as $k => $v)
            {
                $value[$k] = static::htmlentities($v, $flags, $encoding, $double_encode);
            }
        }
        elseif ($value instanceof \Iterator or get_class($value) == 'stdClass')
        {
            // Add to $already_cleaned variable
            $already_cleaned[] = $value;

            foreach ($value as $k => $v)
            {
                $value->{$k} = static::htmlentities($v, $flags, $encoding, $double_encode);
            }
        }
        elseif (is_object($value))
        {
            // Check if the object is whitelisted and return when that's the case
            foreach (\Config::get('security.whitelisted_classes', []) as $class)
            {
                if (is_a($value, $class))
                {
                    // Add to $already_cleaned variable
                    $already_cleaned[] = $value;

                    return $value;
                }
            }

            // Throw exception when it wasn't whitelisted and can't be converted to String
            if ( ! method_exists($value, '__toString'))
            {
                throw new \RuntimeException('Object class "' . get_class($value) . '" could not be converted to string or ' . 'sanitized as ArrayAccess. Whitelist it in security.whitelisted_classes in app/config/config.php ' . 'to allow it to be passed unchecked.');
            }

            $value = static::htmlentities((string) $value, $flags, $encoding, $double_encode);
        }

        return $value;
    }
}
