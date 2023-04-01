<?php
/**
 * Set of php utils forked from Fuelphp framework
 */

namespace Velocite\Cache\Storage;

use Velocite\CacheExpiredException;
use Velocite\CacheNotFoundException;

abstract class Driver
{
    /**
     * @var array defines which class properties are gettable with get_... in the __call() method
     */
    protected static $_gettable = ['created', 'expiration', 'dependencies', 'identifier'];

    /**
     * @var array defines which class properties are settable with set_... in the __call() method
     */
    protected static $_settable = ['expiration', 'dependencies', 'identifier'];

    /**
     * @var string name of the content handler driver
     */
    protected $content_handler;

    /**
     * @var \Velocite\Cache\Handler\Driver handles and formats the cache's contents
     */
    protected $handler_object;

    /**
     * @var string the cache's name, either string or md5'd serialization of something else
     */
    protected $identifier;

    /**
     * @var int timestamp of creation of the cache
     */
    protected $created;

    /**
     * @var int timestamp when this cache will expire
     */
    protected $expiration;

    /**
     * @var array contains identifiers of other caches this one depends on
     */
    protected $dependencies = [];

    /**
     * @var mixed the contents of this
     */
    protected $contents;

    /**
     * @var string loaded driver
     */
    protected $driver;

    /**
     * Converts the identifier to a string when necessary:
     * A int is just converted to a string, all others are serialized and then md5'd
     *
     * @param mixed $identifier
     *
     * @throws \Velocite\Exception
     *
     * @return string
     */
    public static function stringify_identifier($identifier) : string
    {
        // Identifier may not be empty, but can be false or 0
        if ($identifier === '' || $identifier === null)
        {
            throw new \Velocite\Exception('The identifier cannot be empty, must contain a value of any kind other than null or an empty string.');
        }

        // In case of string or int just return it as a string
        if (is_string($identifier) || is_int($identifier))
        {
            // cleanup to only allow alphanumeric chars, dashes, dots & underscores
            if (preg_match('/^([a-z0-9_\.\-]*)$/iuD', $identifier) === 0)
            {
                throw new \Velocite\Exception('Cache identifier can only contain alphanumeric characters, underscores, dashes & dots.');
            }

            return (string) $identifier;
        }
        // In case of array, bool or object return the md5 of the $identifier's serialization


        return '_hashes.' . md5(serialize($identifier));
    }

    /**
     * Allows for default getting and setting
     *
     * @param   string
     * @param   array
     * @param mixed $method
     * @param mixed $args
     *
     * @return void|mixed
     */
    public function __call($method, $args = [])
    {
        // Allow getting any properties set in static::$_gettable
        if (substr($method, 0, 3) == 'get')
        {
            $name = substr($method, 4);

            if (in_array($name, static::$_gettable))
            {
                return $this->{$name};
            }


            throw new \BadMethodCallException('This property doesn\'t exist or can\'t be read.');
        }
        // Allow setting any properties set in static::$_settable
        elseif (substr($method, 0, 3) == 'set')
        {
            $name = substr($method, 4);

            if (in_array($name, static::$_settable))
            {
                $this->{$name} = @$args[0];
            }
            else
            {
                throw new \BadMethodCallException('This property doesn\'t exist or can\'t be set.');
            }

            return $this;
        }
        else
        {
            throw new \BadMethodCallException('Illegal method call: ' . $method);
        }
    }

    /**
     * Default constructor, any extension should either load this first or act similar
     *
     * @param string $identifier the identifier for this cache
     * @param array  $config     additional config values
     */
    public function __construct(string $identifier, array $config)
    {
        $this->identifier = $identifier;

        // fetch options from config and set them
        $this->expiration       = array_key_exists('expiration', $config) ? $config['expiration'] : \Velocite\Config::get('cache.expiration', null);
        $this->dependencies     = array_key_exists('dependencies', $config) ? $config['dependencies'] : [];
        $this->content_handler  = array_key_exists('content_handler', $config) ? new $config['content_handler']() : null;
        $this->driver           = array_key_exists('driver', $config) ? $config['driver'] : 'file';
    }

    /**
     * Should delete this cache instance, should also run reset() afterwards
     */
    abstract public function delete();

    /**
     * Flushes the whole cache for a specific storage type or just a part of it when $section is set
     * (might not work with all storage drivers), defaults to the default storage type
     *
     * @param  string
     */
    abstract public function delete_all( string $section) : bool;

    /**
     * Should check all dependencies against the creation timestamp.
     * This is static to make it possible in the future to check dependencies from other storages then the current one,
     * though I don't have a clue yet how to make that possible.
     *
     * @param array $dependencies
     *
     * @return bool either true or false on any failure
     */
    abstract public function check_dependencies(array $dependencies) : bool;

    /**
     * Resets all properties except for the identifier, should be run by default when a delete() is triggered
     */
    public function reset() : void
    {
        $this->contents			     = null;
        $this->created			      = null;
        $this->expiration		    = null;
        $this->dependencies		  = [];
        $this->content_handler	= null;
        $this->handler_object	 = null;
    }

    /**
     * Front for writing the cache, ensures interchangeability of storage engines. Actual writing
     * is being done by the _set() method which needs to be extended.
     *
     * @param mixed   $contents     The content to be cached
     * @param integer $expiration   The time in seconds until the cache will expire, =< 0 or null means no expiration
     * @param array   $dependencies array of names on which this cache depends for
     */
    final public function set($contents = null, ?int $expiration = null, array $dependencies = []) : void
    {
        $contents = \Velocite\Str::value($contents);
        // save the current expiration
        $current_expiration = $this->expiration;

        // Use either the given value or the class property
        if ( null !== $contents)
        {
            $this->set_contents($contents);
        }
        $this->expiration	  = ($expiration !== null) ? $expiration : $this->expiration;
        $this->dependencies	= ( ! empty($dependencies)) ? $dependencies : $this->dependencies;

        $this->created = time();

        // Create expiration timestamp when other then null
        if ( null !== $this->expiration)
        {
            if ( ! is_numeric($this->expiration))
            {
                throw new \InvalidArgumentException('Expiration must be a valid number.');
            }
            $this->expiration = $this->created + (int) ($this->expiration);
        }

        // Convert dependency identifiers to string when set
        $this->dependencies = ( ! is_array($this->dependencies)) ? [$this->dependencies] : $this->dependencies;

        if ( ! empty( $this->dependencies ) )
        {
            foreach ($this->dependencies as $key => $id)
            {
                $this->dependencies[$key] = $this->stringify_identifier($id);
            }
        }

        // Turn everything over to the storage specific method
        $this->_set();

        // restore the expiration
        $this->expiration = $current_expiration;
    }

    /**
     * Front for reading the cache, ensures interchangeability of storage engines. Actual reading
     * is being done by the _get() method which needs to be extended.
     *
     * @param bool $use_expiration
     *
     * @throws CacheExpiredException
     * @throws CacheNotFoundException
     *
     * @return \Velocite\Cache\Storage\Driver
     */
    final public function get(bool $use_expiration = true) : mixed
    {
        if ( ! $this->_get())
        {
            throw new CacheNotFoundException('not found');
        }

        if ($use_expiration)
        {
            if ( null !== $this->expiration and $this->expiration < 0)
            {
                $this->delete();
                throw new CacheExpiredException('expired');
            }

            // Check dependencies and handle as expired on failure
            if ( ! $this->check_dependencies($this->dependencies))
            {
                $this->delete();
                throw new CacheExpiredException('expired');
            }
        }

        return $this->get_contents();
    }

    /**
     * Does get() & set() in one call that takes a callback and it's arguments to generate the contents
     *
     * @param string|array $callback     Valid PHP callback
     * @param array        $args         Arguments for the above function/method
     * @param int|null     $expiration   Cache expiration in seconds
     * @param array        $dependencies Contains the identifiers of caches this one will depend on
     *
     * @return mixed
     */
    final public function call($callback, array $args = [], ?int $expiration = null, array $dependencies = []) : mixed
    {
        try
        {
            $this->get();
        }
        catch (CacheNotFoundException $e)
        {
            // Create the contents
            $contents = call_user_func_array($callback, $args);

            $this->set($contents, $expiration, $dependencies);
        }

        return $this->get_contents();
    }

    /**
     * Set the contents with optional handler instead of the default
     *
     * @param   mixed
     * @param   string
     * @param mixed      $contents
     * @param null|mixed $handler
     *
     * @return \Velocite\Cache\Storage\Driver
     */
    public function set_contents($contents, $handler = null) : \Velocite\Cache\Storage\Driver
    {
        $this->contents = $contents;
        $this->set_content_handler($handler);
        $this->contents = $this->handle_writing($contents);

        return $this;
    }

    /**
     * Fetches contents
     *
     * @return mixed
     */
    public function get_contents() : mixed
    {
        return $this->handle_reading($this->contents);
    }

    /**
     * Gets a specific content handler
     *
     * @param   string
     * @param null|mixed $handler
     *
     * @return \Velocite\Cache\Handler\Driver
     */
    public function get_content_handler($handler = null) : \Velocite\Cache\Handler\Driver
    {
        if ( ! empty($this->handler_object))
        {
            return $this->handler_object;
        }

        // When not yet set, use $handler or detect the preferred handler (string = string, otherwise serialize)
        if (empty($this->content_handler) && empty($handler))
        {
            if ( ! empty($handler))
            {
                $this->content_handler = $handler;
            }

            if (is_string($this->contents))
            {
                $this->content_handler = \Velocite\Config::get('cache.string_handler', 'plaintext');
            }
            else
            {
                $type                  = is_object($this->contents) ? get_class($this->contents) : gettype($this->contents);
                $this->content_handler = \Velocite\Config::get('cache.' . $type . '_handler', 'serialized');
            }
        }

        $class                = '\\Velocite\\Cache\\Handler\\' . ucfirst($this->content_handler);
        $this->handler_object = new $class();

        return $this->handler_object;
    }

    /**
     * Abstract method that should take care of the storage engine specific reading. Needs to set the object properties:
     * - created
     * - expiration
     * - dependencies
     * - contents
     * - content_handler
     *
     * @return bool success of the operation
     */
    abstract protected function _get() : bool;

    /**
     * Abstract method that should take care of the storage engine specific writing. Needs to write the object properties:
     * - created
     * - expiration
     * - dependencies
     * - contents
     * - content_handler
     */
    abstract protected function _set();

    /**
     * Decides a content handler that makes it possible to write non-strings to a file
     *
     * @param   string
     * @param mixed $handler
     *
     * @return \Velocite\Cache\Storage\Driver
     */
    protected function set_content_handler($handler) : \Velocite\Cache\Storage\Driver
    {
        $this->handler_object  = null;
        $this->content_handler = (string) $handler;

        return $this;
    }

    /**
     * Converts the contents the cachable format
     *
     * @param $contents
     *
     * @return string
     */
    protected function handle_writing($contents) : string
    {
        return $this->get_content_handler()->writable($contents);
    }

    /**
     * Converts the cachable format to the original value
     *
     * @param $contents
     *
     * @return mixed
     */
    protected function handle_reading($contents) : mixed
    {
        return $this->get_content_handler()->readable($contents);
    }
}
