<?php
/**
 * Set of php utils forked from Fuelphp framework
 */

namespace Velocite;

use Velocite\Exception\StoreException;

/**
 * Config class
 */
class Store
{
    /**
     * Loads a store file.
     *
     * @param string|array $locations
     * @param string       $file      string file
     * @param bool         $overwrite true for array_merge, false for \Arr::merge
     *
     * @throws StoreException
     *
     * @return array the (loaded) config array
     */
    public static function load( string|array $locations, string $file, bool $overwrite = false) : ?array
    {
        // storage for the config
        $store = [];

        // Config_Instance class
        $class = null;

        // Make sure location is an array
        ! is_array($locations) && $locations = [$locations];

        // Empty file
        if ( ! $file )
        {
            throw new StoreException(sprintf('No valid file provided.'));
        }

        $info = pathinfo($file);

        $type = $info['extension'] ?? 'php';

        $class = '\\Velocite\\Store\\' . ucfirst($type);

        if ( ! class_exists($class))
        {
            throw new StoreException(sprintf('Invalid store type "%s".', $type));
        }

        // Call init method if the class has some
        method_exists($class, '_init') and $class::_init();
        $class = new $class($info['filename']);

        // if we have a Config instance class?
        if (is_object($class))
        {
            // then load its config
            try
            {
                $store = $class->load( $locations, $overwrite );
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
    public static function save( string $location, string $file, $config ) : bool
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

        $class = '\\Velocite\\Store\\' . ucfirst($type);

        if ( ! class_exists($class))
        {
            throw new StoreException(sprintf('Invalid store type "%s".', $type));
        }

        $driver = new $class($file);

        return $driver->save($location, $config);
    }
}
