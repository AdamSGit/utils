<?php
/**
 * Set of php utils forked from Fuelphp framework
 */

namespace Velocite\Store;

use Velocite\Arr;
use Velocite\Finder;
use Velocite\StoreException;

/**
 * A base Store File class for File based stores.
 */
abstract class File implements StoreInterface
{
    use Vars;

    protected $file;

    protected $location;

    protected $vars = [];

    /**
     * Sets up the file to be parsed and variables
     *
     * @param string $file Config file name
     * @param array  $vars Variables to parse in the file
     */
    public function __construct(string|array $location, ?string $file = null, array $vars = [])
    {
        $this->location = is_array($location) ? $location : [$location];
        $this->file     = $file;
        $this->vars     = $vars;

        // Todo strpos check extension and remove it
    }

    /**
     * Loads the config file(s).
     *
     * @param bool $overwrite Whether to overwrite existing values
     * @param bool $cache     Whether to cache this path or not
     *
     * @return array the config array
     */
    public function load(bool $overwrite = false, bool $cache = true) : array
    {
        $paths  = $this->find_file($cache);
        $store = [];

        foreach ($paths as $path)
        {
            $store = array_merge($store, $this->load_file($path));
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
    public function save(array $contents) : bool
    {
        // get the formatted output
        $output = $this->export_format($contents);

        if ( ! $output)
        {
            return false;
        }

        if ( ! $path = Finder::search('config', $this->file, $this->ext))
        {
            if ($pos = strripos($this->file, '::'))
            {
                // get the namespace path
                if ($path = \Autoloader::namespace_path('\\' . ucfirst(substr($this->file, 0, $pos))))
                {
                    // strip the namespace from the filename
                    $this->file = substr($this->file, $pos+2);

                    // strip the classes directory as we need the module root
                    $path = substr($path, 0, -8) . 'config' . DS . $this->file . $this->ext;
                }
                else
                {
                    // invalid namespace requested
                    return false;
                }
            }
        }

        // absolute path requested?
        if ($this->file[0] === '/' or (isset($this->file[1]) and $this->file[1] === ':'))
        {
            $path = $this->file;
        }

        // make sure we have a fallback
        $path or $path = APPPATH . 'config' . DS . $this->file . $this->ext;

        $path = pathinfo($path);

        if ( ! is_dir($path['dirname']))
        {
            mkdir($path['dirname'], 0777, true);
        }

        $return = \File::update($path['dirname'], $path['basename'], $output);

        if ($return)
        {
            try
            {
                \Config::load('file', true);
                chmod($path['dirname'] . DS . $path['basename'], \Config::get('file.chmod.files', 0666));
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
     * @param bool $cache Whether to cache this path or not
     *
     * @throws StoreException
     *
     * @return array
     */
    protected function find_file(bool $cache = true) : array
    {
        $paths = [];

        foreach($this->location as $location)
        {
            $paths = Arr::merge($paths, Finder::search($location, $this->file, $this->ext, true, $cache));
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
