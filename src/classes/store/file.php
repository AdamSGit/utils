<?php
/**
 * Set of php utils forked from Fuelphp framework
 */

namespace Velocite\Store;

use Velocite\Arr;
use Velocite\Config;
use Velocite\Finder;
use Velocite\Exception\StoreException;

/**
 * A base Store File class for File based stores.
 */
abstract class File implements StoreInterface
{
    protected $file;

    /**
     * Sets up the file to be parsed and variables
     *
     * @param string $file Config file name
     * @param array  $vars Variables to parse in the file
     */
    public function __construct( ?string $file = null, array $vars = [])
    {
        $this->file     = $file;
    }

    /**
     * Loads the config file(s).
     *
     * @param array $locations
     *
     * @return array the config array
     */
    public function load( array $locations, bool $overwrite = false ) : array
    {
        $paths  = $this->find_file($locations);
        $store  = [];

        foreach ($paths as $path)
        {
            $store = $overwrite ?
                array_merge($store, $this->load_file($path)) :
                Arr::merge($store, $this->load_file($path));
        }

        return $store;
    }

    /**
     * Gets the default group name.
     *
     * @return string
     */
    public function group() : string
    {
        return $this->file;
    }

    /**
     * Formats the output and saved it to disc.
     *
     * @param array $contents config array to save
     *
     * @return bool \File::update result
     */
    public function save( string $path, array $contents) : bool
    {
        Config::load('file', true);

        // get the formatted output
        $output = $this->export_format($contents);

        if ( ! $output)
        {
            return false;
        }

        $path = $path . DS . $this->file . $this->ext;
        $path = pathinfo($path);

        if ( ! is_dir($path['dirname']))
        {
            mkdir($path['dirname'], Config::get('file.chmod.folders', 0777), true);
        }

        $return = \Velocite\File::update($path['dirname'], $path['basename'], $output);

        if ($return)
        {
            try
            {
                chmod($path['dirname'] . DS . $path['basename'], Config::get('file.chmod.files', 0666));
            }
            catch (\PhpErrorException $e)
            {
                // if we get something else then a chmod error, bail out
                if (substr($e->getMessage(), 0, 8) !== 'chmod():')
                {
                    throw new $e();
                }
            }
        }

        return $return;
    }

    /**
     * Finds the given config files
     *
     * @param array $location
     * @param bool  $cache    Whether to cache this path or not
     *
     * @throws StoreException
     *
     * @return array
     */
    protected function find_file( array $locations ) : array
    {
        $paths = [];

        foreach ($locations as $location)
        {
            $paths = Arr::merge($paths, Finder::search($location, $this->file, $this->ext, true, false));
        }

        if (empty($paths))
        {
            throw new StoreException(sprintf('File "%s" does not exist.', $this->file));
        }

        return array_reverse($paths);
    }

    /**
     * Must be implemented by child class. Gets called for each file to load.
     *
     * @param string $file the path to the file
     */
    abstract protected function load_file(string $file);

    /**
     * Must be implemented by child class. Gets called when saving a config file.
     *
     * @param array $contents config array to save
     *
     * @return string formatted output
     */
    abstract protected function export_format(array $contents) : string;
}
