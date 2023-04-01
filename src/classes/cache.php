<?php
/**
 * Set of php utils forked from Fuelphp framework
 */

namespace Velocite;

class Cache
{
    /**
     * Loads any default caching settings when available
     */
    public static function _init() : void
    {
        Config::load('cache', true);
    }

    /**
     * Creates a new cache instance.
     *
     * @param mixed             $identifier The identifier of the cache, can be anything but empty
     * @param string|array|null $config     Either an array of settings or the storage driver to be used
     *
     * @return Cache\Storage\Driver The new cache object
     */
    public static function forge( mixed $identifier, string|array|null $config = [] ) : Cache\Storage\Driver
    {
        // load the default config
        $defaults = Config::get('cache', []);

        // $config can be either an array of config settings or the name of the storage driver
        if ( ! empty($config) and ! is_array($config) and null !== $config)
        {
            $config = ['driver' => $config];
        }

        // Overwrite default values with given config
        $config = array_merge($defaults, (array) $config);

        if (empty($config['driver']))
        {
            throw new VelociteException('No cache driver given or no default cache driver set.');
        }

        $class = '\\Velocite\\Cache\\Storage\\' . ucfirst($config['driver']);

        // Convert the name to a string when necessary
        $identifier = call_user_func($class . '::stringify_identifier', $identifier);

        // Return instance of the requested cache object
        return new $class($identifier, $config);
    }

    /**
     * Front for writing the cache, ensures interchangeability of storage drivers. Actual writing
     * is being done by the _set() method which needs to be extended.
     *
     * @param mixed      $identifier   The identifier of the cache, can be anything but empty
     * @param mixed|null $contents     The content to be cached
     * @param integer    $expiration   The time in seconds until the cache will expire, =< 0 or null means no expiration
     * @param array      $dependencies Contains the identifiers of caches this one will depend on (not supported by all drivers!)
     *
     * @return Cache\Storage\Driver The new Cache object
     */
    public static function set(mixed $identifier, mixed $contents = null, ?int $expiration = null, array $dependencies = []) : void
    {
        $contents = Str::value($contents);

        $cache = static::forge($identifier);

        $cache->set($contents, $expiration, $dependencies);
    }

    /**
     * Does get() & set() in one call that takes a callback and it's arguments to generate the contents
     *
     * @param mixed    $identifier   The identifier of the cache, can be anything but empty
     * @param \Closure $callback     Valid PHP callback
     * @param array    $args         Arguments for the above function/method
     * @param int      $expiration   Cache expiration in seconds
     * @param array    $dependencies Contains the identifiers of caches this one will depend on (not supported by all drivers!)
     *
     * @return mixed
     */
    public static function call(mixed $identifier, \Closure $callback, array $args = [], ?int $expiration = null, array $dependencies = []) : mixed
    {
        $cache = static::forge($identifier);

        return $cache->call($callback, $args, $expiration, $dependencies);
    }

    /**
     * Front for reading the cache, ensures interchangeability of storage drivers. Actual reading
     * is being done by the _get() method which needs to be extended.
     *
     * @param mixed $identifier     The identifier of the cache, can be anything but empty
     * @param bool  $use_expiration
     *
     * @return mixed
     */
    public static function get(mixed $identifier, bool $use_expiration = true) : mixed
    {
        $cache = static::forge($identifier);

        return $cache->get($use_expiration);
    }

    /**
     * Frontend for deleting item from the cache, interchangeable storage methods. Actual operation
     * handled by delete() call on storage driver class
     *
     * @param mixed $identifier The identifier of the cache, can be anything but empty
     */
    public static function delete(mixed $identifier)
    {
        $cache = static::forge($identifier);

        return $cache->delete();
    }

    /**
     * Flushes the whole cache for a specific storage driver or just a part of it when $section is set
     * (might not work with all storage drivers), defaults to the default storage driver
     *
     * @param null|string $section
     * @param null|string $driver
     *
     * @return bool
     */
    public static function delete_all( ?string $section = null, ?string $driver = null) : bool
    {
        $config = $driver ? ['driver' => $driver] : [];

        $cache = static::forge('__NOT_USED__', $config);

        return $cache->delete_all($section);
    }
}
