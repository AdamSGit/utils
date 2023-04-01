<?php
/**
 * Set of php utils forked from Fuelphp framework
 */

namespace Velocite\Cache\Storage;

class Redis extends Driver
{
    /**
     * @const  string  Tag used for opening & closing cache properties
     */
    public const PROPS_TAG = 'Velocite_Cache_Properties';

    /**
     * @var Redis storage for the redis object
     */
    protected static $redis = false;

    /**
     * @var array driver specific configuration
     */
    protected $config = [];

    // ---------------------------------------------------------------------

    public function __construct($identifier, $config)
    {
        parent::__construct($identifier, $config);

        $this->config = $config['redis'] ?? [];

        // make sure we have a redis id
        $this->config['cache_id'] = $this->_validate_config('cache_id', $this->config['cache_id'] ?? 'velocite');

        // check for an expiration override
        $this->expiration = $this->_validate_config('expiration', $this->config['expiration'] ?? $this->expiration);

        // make sure we have a redis database configured
        $this->config['database'] = $this->_validate_config('database', $this->config['database'] ?? 'default');

        if (static::$redis === false)
        {
            // get the redis database instance
            try
            {
                static::$redis = \Velocite\Redis\Db::instance($this->config['database']);
            }
            catch (\Exception $e)
            {
                throw new \Velocite\VelociteException('Can not connect to the Redis engine. The error message says "' . $e->getMessage() . '".');
            }

            // get the redis version
            preg_match('/redis_version:(.*?)\n/', static::$redis->info(), $info);

            if (version_compare(trim($info[1]), '1.2') < 0)
            {
                throw new \Velocite\VelociteException('Version 1.2 or higher of the Redis NoSQL engine is required to use the redis cache driver.');
            }
        }
    }

    // ---------------------------------------------------------------------

    /**
     * Check if other caches or files have been changed since cache creation
     *
     * @param   array
     *
     * @return bool
     */
    public function check_dependencies(array $dependencies) : bool
    {
        foreach ($dependencies as $dep)
        {
            // get the section name and identifier
            $sections = explode('.', $dep);

            if (count($sections) > 1)
            {
                $identifier = array_pop($sections);
                $sections   = '.' . implode('.', $sections);
            }
            else
            {
                $identifier = $dep;
                $sections   = '';
            }

            // get the cache index
            $index                    = static::$redis->get($this->config['cache_id'] . ':index:' . $sections);
            null === $index or $index = $this->_unserialize($index);

            // get the key from the index
            $key = isset($index[$identifier][0]) ? $index[$identifier] : false;

            // key found and newer?
            if ($key === false or $key[1] > $this->created)
            {
                return false;
            }
        }

        return true;
    }

    /**
     * Delete Cache
     */
    public function delete() : void
    {
        // get the key for the cache identifier
        $key = $this->_get_key(true);

        // delete the key from the redis server
        if ($key and static::$redis->del($key) === false)
        {
            // do something here?
        }

        $this->reset();
    }

    /**
     * Purge all caches
     *
     * @param ?string $section limit purge to subsection
     *
     * @return bool
     */
    public function delete_all( ?string $section = null ) : bool
    {
        // determine the section index name
        $section = empty($section) ? '' : '.' . $section;

        // get the directory index
        $index                    = static::$redis->get($this->config['cache_id'] . ':dir:');
        null === $index or $index = $this->_unserialize($index);

        if (is_array($index))
        {
            if ( ! empty($section))
            {
                // limit the delete if we have a valid section
                $dirs = [];

                foreach ($index as $entry)
                {
                    if ($entry == $section or strpos($entry, $section . '.') === 0)
                    {
                        $dirs[] = $entry;
                    }
                }
            }
            else
            {
                // else delete the entire contents of the cache
                $dirs = $index;
            }

            // loop through the selected indexes
            foreach ($dirs as $dir)
            {
                // get the stored cache entries for this index
                $list = static::$redis->get($this->config['cache_id'] . ':index:' . $dir);

                if (null === $list)
                {
                    $list = [];
                }
                else
                {
                    $list = $this->_unserialize($list);
                }

                // delete all stored keys
                foreach ($list as $item)
                {
                    static::$redis->del($item[0]);
                }

                // and delete the index itself
                static::$redis->del($this->config['cache_id'] . ':index:' . $dir);
            }

            // update the directory index
            static::$redis->set($this->config['cache_id'] . ':dir:', $this->_serialize(array_diff($index, $dirs)));
        }

        return true;
    }

    // ---------------------------------------------------------------------

    /**
     * Translates a given identifier to a valid redis key
     *
     * @param   string
     * @param mixed $identifier
     *
     * @return string
     */
    protected function identifier_to_key( $identifier ) : string
    {
        return $this->config['cache_id'] . ':' . $identifier;
    }

    /**
     * Prepend the cache properties
     *
     * @return string
     */
    protected function prep_contents() : string
    {
        $properties = [
            'created'          => $this->created,
            'expiration'       => $this->expiration,
            'dependencies'     => $this->dependencies,
            'content_handler'  => $this->content_handler,
        ];
        $properties = '{{' . static::PROPS_TAG . '}}' . json_encode($properties) . '{{/' . static::PROPS_TAG . '}}';

        return $properties . $this->contents;
    }

    /**
     * Remove the prepended cache properties and save them in class properties
     *
     * @param   string
     * @param mixed $payload
     *
     * @throws \UnexpectedValueException
     */
    protected function unprep_contents($payload) : void
    {
        $properties_end = strpos($payload, '{{/' . static::PROPS_TAG . '}}');

        if ($properties_end === false)
        {
            throw new \UnexpectedValueException('Cache has bad formatting');
        }

        $this->contents = substr($payload, $properties_end + strlen('{{/' . static::PROPS_TAG . '}}'));
        $props          = substr(substr($payload, 0, $properties_end), strlen('{{' . static::PROPS_TAG . '}}'));
        $props          = json_decode($props, true);

        if ($props === null)
        {
            throw new \UnexpectedValueException('Cache properties retrieval failed');
        }

        $this->created          = $props['created'];
        $this->expiration       = null === $props['expiration'] ? null : (int) ($props['expiration'] - time());
        $this->dependencies     = $props['dependencies'];
        $this->content_handler  = $props['content_handler'];
    }

    /**
     * Save a cache, this does the generic pre-processing
     *
     * @return bool success
     */
    protected function _set() : bool
    {
        // get the key for the cache identifier
        $key = $this->_get_key();

        // write the cache
        static::$redis->set($key, $this->prep_contents());

        if ( ! empty($this->expiration))
        {
            static::$redis->expireat($key, $this->expiration);
        }

        // update the index
        $this->_update_index($key);

        return true;
    }

    /**
     * Load a cache, this does the generic post-processing
     *
     * @return bool success
     */
    protected function _get() : bool
    {
        // get the key for the cache identifier
        $key = $this->_get_key();

        // fetch the cache data from the redis server
        $payload = static::$redis->get($key);

        // Prevent to pass null payload to unprep_contents, which would result to an error
        if (null === $payload)
        {
            return false;
        }

        try
        {
            $this->unprep_contents($payload);
        }
        catch (\UnexpectedValueException $e)
        {
            return false;
        }

        return true;
    }

    /**
     * validate a driver config value
     *
     * @param string $name  name of the config variable to validate
     * @param mixed  $value value
     *
     * @return mixed
     */
    protected function _validate_config(string $name, $value) : mixed
    {
        switch ($name)
        {
            case 'database':
                // do we have a database config
                if (empty($value) or ! is_string($value))
                {
                    $value = 'default';
                }

                break;

            case 'cache_id':
                if (empty($value) or ! is_string($value))
                {
                    $value = 'velocite';
                }

                break;

            case 'expiration':
                if (empty($value) or ! is_numeric($value))
                {
                    $value = null;
                }

                break;

            default:
                break;
        }

        return $value;
    }

    /**
     * get's the redis key belonging to the cache identifier
     *
     * @param bool $remove if true, remove the key retrieved from the index
     *
     * @return string
     */
    protected function _get_key(bool $remove = false) : string
    {
        // get the current index information
        list($identifier, $sections, $index) = $this->_get_index();
        $index                               = $index === null ? [] : $index = $this->_unserialize($index);

        // get the key from the index
        $key = $index[$identifier][0] ?? false;

        if ($remove === true)
        {
            if ( $key !== false )
            {
                unset($index[$identifier]);
                static::$redis->set($this->config['cache_id'] . ':index:' . $sections, $this->_serialize($index));
            }
        }
        else
        {
            // create a new key if needed
            $key === false and $key = $this->_new_key();
        }

        return $key;
    }

    /**
     * generate a new unique key for the current identifier
     *
     * @return string
     */
    protected function _new_key() : string
    {
        $key = '';

        while (strlen($key) < 32)
        {
            $key .= mt_rand(0, mt_getrandmax());
        }

        return md5($this->config['cache_id'] . '_' . uniqid($key, true));
    }

    /**
     * Get the section index
     *
     * @return array containing the identifier, the sections, and the section index
     */
    protected function _get_index() : array
    {
        // get the section name and identifier
        $sections = explode('.', $this->identifier);

        if (count($sections) > 1)
        {
            $identifier = array_pop($sections);
            $sections   = '.' . implode('.', $sections);
        }
        else
        {
            $identifier = $this->identifier;
            $sections   = '';
        }

        // get the cache index and return it
        return [$identifier, $sections, static::$redis->get($this->config['cache_id'] . ':index:' . $sections)];
    }

    /**
     * Update the section index
     *
     * @param  string  cache key
     * @param mixed $key
     */
    protected function _update_index($key) : void
    {
        // get the current index information
        list($identifier, $sections, $index) = $this->_get_index();
        $index                               = $index === null ? [] : $index = $this->_unserialize($index);

        // store the key in the index and write the index back
        $index[$identifier] = [$key, $this->created];

        static::$redis->set($this->config['cache_id'] . ':index:' . $sections, $this->_serialize($index));

        // get the directory index
        $index = static::$redis->get($this->config['cache_id'] . ':dir:');
        $index = $index === null ? [] : $index = $this->_unserialize($index);

        if (is_array($index))
        {
            if ( ! in_array($sections, $index))
            {
                $index[] = $sections;
            }
        }
        else
        {
            $index = [$sections];
        }

        // update the directory index
        static::$redis->set($this->config['cache_id'] . ':dir:', $this->_serialize($index));
    }

    /**
     * Serialize an array
     *
     * This function first converts any slashes found in the array to a temporary
     * marker, so when it gets unserialized the slashes will be preserved
     *
     * @param   array
     * @param mixed $data
     *
     * @return string
     */
    protected function _serialize($data) : string
    {
        if (is_array($data))
        {
            foreach ($data as $key => $val)
            {
                if (is_string($val))
                {
                    $data[$key] = str_replace('\\', '{{slash}}', $val);
                }
            }
        }
        else
        {
            if (is_string($data))
            {
                $data = str_replace('\\', '{{slash}}', $data);
            }
        }

        return serialize($data);
    }

    /**
     * Unserialize
     *
     * This function unserializes a data string, then converts any
     * temporary slash markers back to actual slashes
     *
     * @param   array
     * @param mixed $data
     *
     * @return mixed
     */
    protected function _unserialize( mixed $data ) : mixed
    {
        $data = @unserialize(stripslashes($data));

        if (is_array($data))
        {
            foreach ($data as $key => $val)
            {
                if (is_string($val))
                {
                    $data[$key] = str_replace('{{slash}}', '\\', $val);
                }
            }

            return $data;
        }

        return (is_string($data)) ? str_replace('{{slash}}', '\\', $data) : $data;
    }
}
