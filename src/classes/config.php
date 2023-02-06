<?php
/**
 * Set of php utils forked from Fuelphp framework
 */

namespace Velocite\Utils;

class ConfigException extends \Exception
{
}

/**
 * Config class
 */
class Config
{
    /**
     * @var array array of loaded files
     */
    public static $loaded_files = [];

    /**
     * @var array the master config array
     */
    public static $items = [];

    /**
     * @var array the dot-notated item cache
     */
    protected static $itemcache = [];

    /**
     * Loads a config file.
     *
     * @param mixed $file      string file | config array | Config_Interface instance
     * @param mixed $group     null for no group, true for group is filename, false for not storing in the master config
     * @param bool  $reload    true to force a reload even if the file is already loaded
     * @param bool  $overwrite true for array_merge, false for Arr::merge
     *
     * @throws ConfigException
     *
     * @return array the (loaded) config array
     */
    public static function load($file, $group = null, bool $reload = false, bool $overwrite = false) : ?array
    {
        // storage for the config
        $config = [];

        // Config_Instance class
        $class = null;

        // name of the config group
        $name = $group === true ? $file : ($group === null ? null : $group);

        // need to store flag
        $cache = ($group !== false);

        // process according to input type
        if ( ! empty($file))
        {
            // we've got a config filename
            if (is_string($file))
            {
                // if we have this config in cache, load it
                if ( ! $reload and
                    array_key_exists($file, static::$loaded_files))
                {
                    if ($group !== false and $name !== null and isset(static::$items[$name]))
                    {
                        // fetch the cached config
                        $config = static::$items[$name];
                    }
                    else
                    {
                        // no config fetched
                        $config = null;
                    }

                    // we don't want to cache this config later!
                    $cache = false;
                }

                // if not, construct a Config instance and load it
                else
                {
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

                    $class = '\\Velocite\\Utils\\Config\\' . ucfirst($type);

                    if (class_exists($class))
                    {
                        static::$loaded_files[$file] = true;
                        $class                       = new $class($file);
                    }
                    else
                    {
                        throw new ConfigException(sprintf('Invalid config type "%s".', $type));
                    }
                }
            }

            // we've got an array of config data
            elseif (is_array($file))
            {
                $config = $file;
            }

            // we've got an already created Config instance class
            elseif ($file instanceof Config_Interface)
            {
                $class = $file;
            }

            // don't know what we got, bail out
            else
            {
                throw new ConfigException(sprintf('Invalid config file argument'));
            }

            // if we have a Config instance class?
            if (is_object($class))
            {
                // then load its config
                try
                {
                    $config = $class->load($overwrite, ! $reload);
                }
                catch (ConfigException $e)
                {
                    $config = null;
                }

                // and update the group if needed
                if ($group === true)
                {
                    $name = $class->group();
                }
            }
        }

        // no arguments?
        else
        {
            throw new ConfigException(sprintf('No valid config file argument given'));
        }

        // do we have a valid config loaded and do we need to cache it?
        if ( ! empty($config) and $cache)
        {
            // do we need to load it in the global config?
            if ($name === null)
            {
                static::$items     = $reload ? $config : ($overwrite ? array_merge(static::$items, $config) : Arr::merge(static::$items, $config));
                static::$itemcache = [];
            }

            // or in a named config
            else
            {
                if ( ! isset(static::$items[$name]) or $reload)
                {
                    static::$items[$name] = [];
                }

                if ($overwrite)
                {
                    Arr::set(static::$items, $name, array_merge(Arr::get(static::$items, $name, []), $config));
                }
                else
                {
                    Arr::set(static::$items, $name, Arr::merge(Arr::get(static::$items, $name, []), $config));
                }

                foreach (static::$itemcache as $key => $value)
                {
                    if (strpos($key, $name) === 0)
                    {
                        unset(static::$itemcache[$key]);
                    }
                }
            }
        }

        // return the fetched config
        return $config;
    }

    /**
     * Save a config array to disc.
     *
     * @param string       $file   desired file name
     * @param string|array $config master config array key or config array
     *
     * @throws ConfigException
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

        $class = '\\Config_' . ucfirst($type);

        if ( ! class_exists($class))
        {
            throw new ConfigException(sprintf('Invalid config type "%s".', $type));
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
