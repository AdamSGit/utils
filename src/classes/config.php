<?php
/**
 * Set of php utils forked from Fuelphp framework
 */

namespace Velocite;

use Velocite\Exception\StoreException;
use Velocite\Exception\ConfigException;

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
     * Reset all static data from this class
     *
     * @return void
     */
    public static function _reset() : void
    {
        static::$loaded_files = [];
        static::$items        = [];
        static::$itemcache    = [];
    }

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
    public static function load(string $file, $group = null, bool $reload = false, bool $overwrite = false) : ?array
    {
        // storage for the config
        $config = [];

        // name of the config group
        $name = $group === true ? $file : ($group === null ? null : $group);

        if ( $name and str_contains($name, '.' ))
        {
            $name = explode('.', $name)[0];
        }

        // need to store flag
        $cache = ($group !== false);

        // process according to input type
        if ( empty($file) )
        {
            throw new ConfigException('Tried to load empty config');
        }

        // if we have this config in cache, load it
        if ( ! $reload and array_key_exists($file, static::$loaded_files) )
        {
            if ( $name !== null and isset(static::$items[$name]) )
            {
                // fetch the cached config
                return static::$items[$name];
            }

            return null;
        }

        static::$loaded_files[$file] = true;

        try
        {
            $config = Store::load( [Velocite::$config_dir . DS . Velocite::$env, Velocite::$config_dir], $file, $cache );
        }
        catch( StoreException $e )
        {
            throw new ConfigException(sprintf('Config file "%s" not found.', $file));
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
                if ( ! isset(static::$items[$name]) or $reload )
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
                    if ( str_starts_with($key, $name) )
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
     * Save a config array in store driver.
     *
     * @param string       $file   desired file name
     * @param string|array $config master config array key or config array
     * @param bool         $env    Should file be save in env config subdir, or config root
     *
     * @throws ConfigException
     *
     * @return bool false when config is empty or invalid else \File::update result
     */
    public static function save(string $file, string|array $config, bool $env = false ) : bool
    {
        if ( ! is_array($config))
        {
            if ( ! isset(static::$items[$config]) )
            {
                return false;
            }

            $config = static::$items[$config];
        }

        $path = APPPATH . DS . Velocite::$config_dir;

        if ($env)
        {
            $path .= DS . Velocite::$env;
        }

        return Store::save( $path, $file, $config);
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
        if ( array_key_exists($item, static::$itemcache) )
        {
            return static::$itemcache[$item];
        }

        $val = Arr::get(static::$items, $item);

        if ($val and $val !== $default and str_contains($item, '.'))
        {
            static::$itemcache[$item] = $val;
        }

        return $val ? Str::value($val) : $default;
    }

    /**
     * Sets a (dot notated) config item
     *
     * @param string $item  a (dot notated) config key
     * @param mixed  $value the config value
     */
    public static function set(string $item, $value) : void
    {
        str_contains($item, '.') && static::$itemcache[$item] = $value;
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
