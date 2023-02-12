<?php
/**
 * Set of php utils forked from Fuelphp framework
 */

namespace Velocite;

class StoreException extends \Exception
{
}

/**
 * Config class
 */
class Store
{
    /**
     * Loads a store file.
     *
     * @param string|array      $location
     * @param string            $file      string file
     *
     * @throws StoreException
     *
     * @return array the (loaded) config array
     */
    public static function load($location, $file, $cache = true) : ?array
    {
        // storage for the config
        $store = [];

        // Config_Instance class
        $class = null;

        $info = pathinfo($file);

        $type = $info['extension'] ?? 'php';

        $class = '\\Velocite\\Store\\' . ucfirst($type);

        if (class_exists($class))
        {
            // Call init method if the class has some
            method_exists($class, '_init') and $class::_init();
            $class = new $class($location, $info['filename']);
        }
        else
        {
            throw new StoreException(sprintf('Invalid store type "%s".', $type));
        }

        // no arguments?
        if ( ! $file )
        {
            throw new StoreException(sprintf('No valid store file argument given'));
        }

        // if we have a Config instance class?
        if (is_object($class))
        {
            // then load its config
            try
            {
                $store = $class->load( $cache );
            }
            catch (StoreException $e)
            {
                $store = null;
            }
        }

        // return the fetched config
        return $store;
    }

    /**
     * Save a config array to disc.
     *
     * @param string       $file   desired file name
     * @param string|array $config master config array key or config array
     *
     * @throws StoreException
     *
     * @return bool false when config is empty or invalid else \File::update result
     */
    public static function save(string $file, $config) : bool
    {
        if ( ! is_array($config))
        {
            if ( ! isset(static::$items[$config]))
            {
                return false;
            }
            $config = static::$items[$config];
        }

        $info = pathinfo($file);
        $type = 'php';

        if (isset($info['extension']))
        {
            $type = $info['extension'];
            // Keep extension when it's an absolute path, because the finder won't add it
            if ($file[0] !== '/' and $file[1] !== ':')
            {
                $file = substr($file, 0, -(strlen($type) + 1));
            }
        }

        $class = '\\Velocite\\Config\\' . ucfirst($type);

        if ( ! class_exists($class))
        {
            throw new StoreException(sprintf('Invalid config type "%s".', $type));
        }

        $driver = new $class($file);

        return $driver->save($config);
    }

    /**
     * Returns a (dot notated) config setting
     *
     * @param string $item    name of the config item, can be dot notated
     * @param mixed  $default the return value if the item isn't found
     *
     * @return mixed the config setting or default if not found
     */
    public static function get(string $item, $default = null) : mixed
    {
        if (array_key_exists($item, static::$items))
        {
            return static::$items[$item];
        }
        elseif ( ! array_key_exists($item, static::$itemcache))
        {
            // cook up something unique
            $miss = new \stdClass();

            $val = Arr::get(static::$items, $item, $miss);

            // so we can detect a miss here...
            if ($val === $miss)
            {
                return $default;
            }

            static::$itemcache[$item] = $val;
        }

        return Str::value(static::$itemcache[$item]);
    }

    /**
     * Sets a (dot notated) config item
     *
     * @param string $item  a (dot notated) config key
     * @param mixed  $value the config value
     */
    public static function set(string $item, $value) : void
    {
        strpos($item, '.') === false or static::$itemcache[$item] = $value;
        Arr::set(static::$items, $item, $value);
    }

    /**
     * Deletes a (dot notated) config item
     *
     * @param string $item a (dot notated) config key
     *
     * @return array|bool the Arr::delete result, success boolean or array of success booleans
     */
    public static function delete(string $item)
    {
        if (isset(static::$itemcache[$item]))
        {
            unset(static::$itemcache[$item]);
        }

        return Arr::delete(static::$items, $item);
    }
}
