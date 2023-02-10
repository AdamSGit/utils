<?php
/**
 * Set of php utils forked from Fuelphp framework
 */

namespace Velocite;

class Lang
{
    /**
     * @var array array of loaded files
     */
    public static $loaded_files = [];

    /**
     * @var array language lines
     */
    public static $lines = [];

    /**
     * @var array language(s) to fall back on when loading a file from the current lang fails
     */
    public static $fallback;

    public static function _init() : void
    {
        static::$fallback = (array) \Config::get('language_fallback', 'en');
    }

    /**
     * Returns currently active language.
     *
     * @return string currently active language
     */
    public static function get_lang() : string
    {
        $language                      = \Config::get('language');
        empty($language) and $language = static::$fallback[0];

        return $language;
    }

    /**
     * Loads a language file.
     *
     * @param mixed       $file      string file | language array | Lang_Interface instance
     * @param mixed       $group     null for no group, true for group is filename, false for not storing in the master lang
     * @param string|null $language  name of the language to load, null for the configured language
     * @param bool        $overwrite true for array_merge, false for \Arr::merge
     * @param bool        $reload    true to force a reload even if the file is already loaded
     *
     * @throws \FuelException
     *
     * @return array the (loaded) language array
     */
    public static function load($file, $group = null, ?string $language = null, bool $overwrite = false, bool $reload = false) : array
    {
        // get the active language and all fallback languages
        $language or $language = static::get_lang();
        $languages             = static::$fallback;

        // make sure we don't have the active language in the fallback array
        if (in_array($language, $languages))
        {
            unset($languages[array_search($language, $languages)]);
        }

        // stick the active language to the front of the list
        array_unshift($languages, $language);

        if ( ! $reload          and
             ! is_array($file)  and
             ! is_object($file) and
            array_key_exists($language . '/' . $file, static::$loaded_files))
        {
            $group === true and $group = $file;

            if ($group === null or $group === false or ! isset(static::$lines[$language][$group]))
            {
                return false;
            }

            return static::$lines[$language][$group];
        }

        $lang = [];

        if (is_array($file))
        {
            $lang = $file;
        }
        elseif (is_string($file))
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
            $class = '\\Lang_' . ucfirst($type);

            if (class_exists($class))
            {
                static::$loaded_files[$language . '/' . $file] = func_get_args();
                $file                                          = new $class($file, $languages);
            }
            else
            {
                throw new \FuelException(sprintf('Invalid lang type "%s".', $type));
            }
        }

        if ($file instanceof Lang_Interface)
        {
            try
            {
                $lang = $file->load($overwrite);
            }
            catch (Exception\Lang $e)
            {
                $lang = [];
            }
            $group = $group === true ? $file->group() : $group;
        }

        isset(static::$lines[$language]) or static::$lines[$language] = [];

        if ($group === null)
        {
            static::$lines[$language] = $overwrite ? array_merge(static::$lines[$language], $lang) : \Arr::merge(static::$lines[$language], $lang);
        }
        else
        {
            $group = ($group === true) ? $file : $group;

            if ($overwrite)
            {
                \Arr::set(static::$lines[$language], $group, array_merge(\Arr::get(static::$lines[$language], $group, []), $lang));
            }
            else
            {
                \Arr::set(static::$lines[$language], $group, \Arr::merge(\Arr::get(static::$lines[$language], $group, []), $lang));
            }
        }

        return $lang;
    }

    /**
     * Save a language array to disk.
     *
     * @param string       $file     desired file name
     * @param string|array $lang     master language array key or language array
     * @param string|null  $language name of the language to load, null for the configured language
     *
     * @throws Exception\Lang
     *
     * @return bool false when language is empty or invalid else \File::update result
     */
    public static function save(string $file, $lang, ?string $language = null) : bool
    {
        ($language === null) and $language = static::get_lang();

        // prefix the file with the language
        if ( null !== $language)
        {
            $file = explode('::', $file);
            end($file);
            $file[key($file)] = $language . DS . end($file);
            $file             = implode('::', $file);
        }

        if ( ! is_array($lang))
        {
            if ( ! isset(static::$lines[$language][$lang]))
            {
                return false;
            }
            $lang = static::$lines[$language][$lang];
        }

        $type = pathinfo($file, PATHINFO_EXTENSION);

        if ( ! $type)
        {
            $type = 'php';
            $file .= '.' . $type;
        }

        $class = '\\Lang_' . ucfirst($type);

        if ( ! class_exists($class, true))
        {
            throw new Exception\Lang('Cannot save a language file of type: ' . $type);
        }

        $driver = new $class();

        return $driver->save($file, $lang);
    }

    /**
     * Returns a (dot notated) language string
     *
     * @param string      $line     key for the line
     * @param array       $params   array of params to str_replace
     * @param mixed       $default  default value to return
     * @param string|null $language name of the language to get, null for the configured language
     *
     * @return mixed either the line or default when not found
     */
    public static function get(string $line, array $params = [], $default = null, ?string $language = null) : mixed
    {
        ($language === null) and $language = static::get_lang();

        return isset(static::$lines[$language]) ? \Str::tr(\Fuel::value(\Arr::get(static::$lines[$language], $line, $default)), $params) : $default;
    }

    /**
     * Sets a (dot notated) language string
     *
     * @param string      $line     a (dot notated) language key
     * @param mixed       $value    the language string
     * @param string      $group    group
     * @param string|null $language name of the language to set, null for the configured language
     *
     * @return void the \Arr::set result
     */
    public static function set(string $line, $value, ?string $group = null, ?string $language = null) : void
    {
        $group === null or $line = $group . '.' . $line;

        ($language === null) and $language = static::get_lang();

        isset(static::$lines[$language]) or static::$lines[$language] = [];

        \Arr::set(static::$lines[$language], $line, \Fuel::value($value));
    }

    /**
     * Deletes a (dot notated) language string
     *
     * @param string      $item     a (dot notated) language key
     * @param string      $group    group
     * @param string|null $language name of the language to set, null for the configured language
     *
     * @return array|bool the \Arr::delete result, success boolean or array of success booleans
     */
    public static function delete(string $item, ?string $group = null, ?string $language = null)
    {
        $group === null or $item = $group . '.' . $item;

        ($language === null) and $language = static::get_lang();

        return isset(static::$lines[$language]) ? \Arr::delete(static::$lines[$language], $item) : false;
    }

    /**
     * Sets the current language, and optionally reloads all language files loaded in another language
     *
     * @param string $language name of the language to activate
     * @param bool   $reload   true to force a reload of already loaded language files
     *
     * @return bool success boolean, false if no language or the current was passed, true otherwise
     */
    public static function set_lang(string $language, bool $reload = false) : bool
    {
        // check if a language was passedd
        if ( ! empty($language) and $language != static::get_lang())
        {
            // set it
            \Config::set('language', $language);

            // do we need to reload?
            if ($reload)
            {
                foreach (static::$loaded_files as $file => $args)
                {
                    // reload with exactly the same arguments
                    if (strpos($file, $language . '/') !== 0)
                    {
                        call_user_func_array('Lang::load', $args);
                    }
                }
            }

            // return success
            return true;
        }

        // no language or the current language was passed
        return false;
    }
}
