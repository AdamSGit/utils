<?php
/**
 * Set of php utils forked from Fuelphp framework
 */

namespace Velocite\Store;

use Velocite\StoreException;

/**
 * Memcached store parser
 */
class Memcached implements StoreInterface
{
    use Vars;

    /**
     * @var array of driver config defaults
     */
    protected static $config = [
        'identifier' => 'config',
        'servers'    => [
            ['host' => '127.0.0.1', 'port' => 11211, 'weight' => 100],
        ],
    ];

    /**
     * @var \Memcached storage for the memcached object
     */
    protected static $memcached = false;

    // --------------------------------------------------------------------

    protected $identifier;

    protected $ext = '.mem';

    /**
     * driver initialisation
     *
     * @throws StoreException
     */
    public static function _init() : void
    {
        static::$config = array_merge(static::$config, \Config::get('config.memcached', []));

        if (static::$memcached === false)
        {
            // do we have the PHP memcached extension available
            if ( ! class_exists('Memcached') )
            {
                throw new StoreException('Memcached config storage is required, but your PHP installation doesn\'t have the Memcached extension loaded.');
            }

            // instantiate the memcached object
            static::$memcached = new \Memcached();

            // add the configured servers
            static::$memcached->addServers(static::$config['servers']);

            // check if we can connect to all the server(s)
            $added = static::$memcached->getStats();

            foreach (static::$config['servers'] as $server)
            {
                $server = $server['host'] . ':' . $server['port'];

                if ( ! isset($added[$server]) or $added[$server]['pid'] == -1)
                {
                    throw new StoreException('Memcached config storage is required, but there is no connection possible. Check your configuration.');
                }
            }
        }
    }

    /**
     * Sets up the file to be parsed and variables
     *
     * @param string $identifier Config identifier name
     * @param array  $vars       Variables to parse in the data retrieved
     */
    public function __construct(?string $identifier = null, array $vars = [])
    {
        $this->identifier = $identifier;

        $this->vars = [
            'APPPATH'  => APPPATH
        ] + $vars;
    }

    /**
     * Loads the config file(s).
     *
     * @param bool $overwrite Whether to overwrite existing values
     * @param bool $cache     this parameter will ignore in this implement
     *
     * @return array the config array
     */
    public function load(bool $overwrite = false, bool $cache = true) : array
    {
        // fetch the config data from the Memcached server
        $result = static::$memcached->get(static::$config['identifier'] . '_' . $this->identifier);

        return $result === false ? [] : $result;
    }

    /**
     * Gets the default group name.
     *
     * @return string
     */
    public function group() : string
    {
        return $this->identifier;
    }

    /**
     * Formats the output and saved it to disc.
     *
     * @param $contents $contents    config array to save
     *
     * @throws StoreException
     */
    public function save($contents) : void
    {
        // write it to the memcached server
        if (static::$memcached->set(static::$config['identifier'] . '_' . $this->identifier, $contents, 0) === false)
        {
            throw new StoreException('Memcached returned error code "' . static::$memcached->getResultCode() . '" on write. Check your configuration.');
        }
    }
}
