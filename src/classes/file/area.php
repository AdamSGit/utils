<?php
/**
 * Set of php utils forked from Fuelphp framework
 */

namespace Velocite\File;

use Velocite\FileAccessException;

class Area
{
    /**
     * @var string path to basedir restriction, null for no restriction
     */
    protected ?string $basedir;

    /**
     * @var array array of allowed extensions, null for all
     */
    protected ?array $extensions;

    /**
     * @var string base url for files, null for not available
     */
    protected ?string $url;

    /**
     * @var bool whether or not to use file locks when doing file operations
     */
    protected bool $use_locks = false;

    /**
     * @var array contains file handler per file extension
     */
    protected array $file_handlers = [];

    /**
     * File methods to bind to this class
     *
     * @var array
     */
    protected array $file_methods = [
        'create',
        'create_dir',
        'read',
        'read_dir',
        'rename',
        'rename_dir',
        'copy',
        'copy_dir',
        'delete',
        'delete_dir',
        'update',
        'get_permissions',
        'get_time',
        'get_size',
    ];

    /**
     * Factory for area objects
     *
     * @param	array
     *
     * @return \Velocite\File\Area
     */
    public static function forge(array $config = []) : \Velocite\File\Area
    {
        return new static($config);
    }

    /* -------------------------------------------------------------------------------------
     * Allow all File methods to be used from an area directly
     * ------------------------------------------------------------------------------------- */

    public function __call($name, $args)
    {
        if ( in_array($name, static::$file_methods) )
        {
            $args[] = $this;

            return call_user_func_array("\\Velocite\\File::{$name}", $args);
        }
    }

    protected function __construct(array $config = [])
    {
        foreach ($config as $key => $value)
        {
            if (property_exists($this, $key))
            {
                $this->{$key} = $value;
            }
        }

        if ( ! empty($this->basedir))
        {
            $this->basedir = realpath($this->basedir) ?: $this->basedir;
        }
    }

    /**
     * Handler factory for given path
     *
     * @param string $path    path to file or directory
     * @param array  $config  optional config
     * @param array  $content
     *
     * @throws FileAccessException  when outside basedir restriction or disallowed file extension
     * @throws OutsideAreaException
     *
     * @return \Velocite\File\Handler\File
     */
    public function get_handler(string $path, array $config = [], array $content = []) : \Velocite\File\Handler\File
    {
        $path = $this->get_path($path);

        if (is_file($path))
        {
            $info = pathinfo($path);

            // deal with path names without an extension
            isset($info['extension']) or $info['extension'] = '';

            // check file extension
            if ( ! empty($this->extensions) && ! in_array($info['extension'], $this->extensions))
            {
                throw new FileAccessException('File operation not allowed: disallowed file extension.');
            }

            // create specific handler when available
            if (array_key_exists($info['extension'], $this->file_handlers))
            {
                $class = '\\' . ltrim($this->file_handlers[$info['extension']], '\\');

                return $class::forge($path, $config, $this);
            }

            return \Velocite\File\Handler\File::forge($path, $config, $this);
        }
        elseif (is_dir($path))
        {
            return \File_Handler_Directory::forge($path, $config, $this, $content);
        }

        // still here? path is invalid
        throw new FileAccessException('Invalid path for file or directory.');
    }

    /**
     * Does this area use file locks?
     *
     * @return bool
     */
    public function use_locks() : bool
    {
        return $this->use_locks;
    }

    /**
     * Are the shown extensions limited, and if so to which?
     *
     * @return array
     */
    public function extensions() : ?array
    {
        return $this->extensions;
    }

    /**
     * Translate relative path to real path, throws error when operation is not allowed
     *
     * @param string $path
     *
     * @throws FileAccessException  when outside basedir restriction or disallowed file extension
     * @throws OutsideAreaException
     *
     * @return string
     */
    public function get_path(string $path) : string
    {
        $pathinfo = is_dir($path) ? ['dirname' => $path, 'extension' => null, 'basename' => ''] : pathinfo($path);

        // make sure we have a dirname to work with
        isset($pathinfo['dirname']) or $pathinfo['dirname'] = '';

        // do we have a basedir, and is the path already prefixed by the basedir? then just deal with the double dots...
        if ( ! empty($this->basedir) && substr($pathinfo['dirname'], 0, strlen($this->basedir)) == $this->basedir)
        {
            $pathinfo['dirname'] = realpath($pathinfo['dirname']);
        }
        else
        {
            // attempt to get the realpath(), otherwise just use path with any double dots taken out when basedir is set (for security)
            $pathinfo['dirname'] = ( ! empty($this->basedir) ? realpath($this->basedir . DS . $pathinfo['dirname']) : realpath($pathinfo['dirname']) )
                    ?: ( ! empty($this->basedir) ? $this->basedir . DS . str_replace('..', '', $pathinfo['dirname']) : $pathinfo['dirname']);
        }

        // basedir prefix is required when it is set (may cause unexpected errors when realpath doesn't work)
        if ( ! empty($this->basedir) && substr($pathinfo['dirname'], 0, strlen($this->basedir)) != $this->basedir)
        {
            throw new OutsideAreaException('File operation not allowed: given path is outside the basedir for this area.');
        }

        // check file extension
        if ( ! empty(static::$extensions) && array_key_exists($pathinfo['extension'], static::$extensions))
        {
            throw new FileAccessException('File operation not allowed: disallowed file extension.');
        }

        return $pathinfo['dirname'] . DS . $pathinfo['basename'];
    }

    /**
     * Translate relative path to accessible path, throws error when operation is not allowed
     *
     * @param string
     * @param mixed $path
     *
     * @throws \LogicException when no url is set or no basedir is set and file is outside DOCROOT
     *
     * @return string
     */
    public function get_url(string $path) : string
    {
        if (empty($this->url))
        {
            throw new \LogicException('File operation now allowed: cannot create a file url without an area url.');
        }

        $path = $this->get_path($path);

        $basedir                     = $this->basedir;
        empty($basedir) and $basedir = APPPATH;

        if (stripos($path, $basedir) !== 0)
        {
            throw new \LogicException('File operation not allowed: cannot create file url whithout a basedir and file outside APPPATH.');
        }

        return rtrim($this->url, '/') . '/' . ltrim(str_replace(DS, '/', substr($path, strlen($basedir))), '/');
    }
}
