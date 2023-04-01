<?php
/**
 * Set of php utils forked from Fuelphp framework
 */

namespace Velocite;

/**
 * Language class (i18n)
 */
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

    /**
     * Init method
     *
     * @return void
     */
    public static function _init() : void
    {
        static::$fallback = (array) Config::get('language_fallback', 'en');
    }

    /**
     * Reset all static data from this class
     *
     * @return void
     */
    public static function _reset() : void
    {
        Config::load('config');
        static::$loaded_files = [];
        static::$lines        = [];
        static::set_lang(Config::get('language_fallback'));
    }

    /**
     * Returns currently active language.
     *
     * @return string currently active language
     */
    public static function get_lang() : string
    {
        $language                      = Config::get('language');
        empty($language) and $language = static::$fallback[0];

        return $language;
    }

    /**
     * Loads a language file.
     *
     * @param mixed       $file      string file | language array | LangInterface instance
     * @param mixed       $group     null for no group, true for group is filename, false for not storing in the master lang
     * @param string|null $language  name of the language to load, null for the configured language
     * @param bool        $overwrite true for array_merge, false for Arr::merge
     * @param bool        $reload    true to force a reload even if the file is already loaded
     *
     * @throws Exception\Lang
     *
     * @return array the (loaded) language array
     */
    public static function load($file, $group = null, ?string $language = null, bool $overwrite = false, bool $reload = false) : ?array
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

        $group === true and $group = $file;

        if ( ! $reload and ! is_array($file)  and ! is_object($file) and array_key_exists($language . DS . $file, static::$loaded_files))
        {
            if ($group and isset(static::$lines[$language][$group]))
            {
                return static::$lines[$language][$group];
            }

            return null;
        }

        $lang = [];

        static::$loaded_files[$language . DS . $file] = func_get_args();

        $location = "lang/{$language}";

        // If file is a path that include dirs, set them to location
        if (str_contains($file, DS))
        {
            $file_segments = explode(DS, $file);
            $file          = array_pop($file_segments);
            $location .= DS . implode(DS, $file_segments);
        }

        try
        {
            $lang = Store::load( $location, $file );
        }
        catch( StoreException $e )
        {
            throw new LangException(sprintf('Lang file "%s" not found.', $file));
        }

        if ( $lang === null )
        {
            return null;
        }

        isset(static::$lines[$language]) or static::$lines[$language] = [];

        if ($group === null)
        {
            static::$lines[$language] = $overwrite ? array_merge(static::$lines[$language], $lang) : Arr::merge(static::$lines[$language], $lang);
        }
        else
        {
            $group = ($group === true) ? $file : $group;

            if ($overwrite)
            {
                Arr::set(static::$lines[$language], $group, array_merge(Arr::get(static::$lines[$language], $group, []), $lang));
            }
            else
            {
                Arr::set(static::$lines[$language], $group, Arr::merge(Arr::get(static::$lines[$language], $group, []), $lang));
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
    public static function save(string $file, string|array $lang, ?string $language = null) : bool
    {
        ($language === null) and $language = static::get_lang();

        if ( ! is_array($lang))
        {
            if ( ! isset(static::$lines[$language][$lang]))
            {
                return false;
            }

            $lang = static::$lines[$language][$lang];
        }

        $path = APPPATH . DS . Velocite::$lang_dir . DS . $language;

        // If file is a path that include dirs, set them to location
        if (str_contains($file, DS))
        {
            $file_segments = explode(DS, $file);
            $file          = array_pop($file_segments);
            $path .= DS . implode(DS, $file_segments);
        }

        return Store::save($path, $file, $lang);
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
        $value                             = Str::value(Arr::get(static::$lines[$language], $line, $default));

        return $value ? (is_string($value) ? Str::tr($value, $params) : $value) : $default;
    }

    /**
     * Same than get() with plural support, with $count element to take into account
     * Format Should be : "zero element | one element | 2+ elements". If no pipe is found in the language, return the full string
     * That allow to use plural in some languages only
     *
     *
     * @param string      $line     key for the line
     * @param array       $params   array of params to str_replace
     * @param integer     $count    Number of elements bein used in sentence
     * @param mixed       $default  default value to return
     * @param string|null $language name of the language to get, null for the configured language
     *
     * @return mixed either the line corresponding to count, or default when not found
     */
    public static function get_plural(string $line, array $params = [], int $count = 0, $default = null, ?string $language = null) : mixed
    {
        $plural_string = static::get($line, $params, $default, $language);

        if (is_string($plural_string) and str_contains($plural_string, '|'))
        {
            $plural_string   = str_replace([' |', '| '], '|', $plural_string);
            $plural_versions = explode('|', $plural_string);
            $last            = count($plural_versions) - 1;

            for ( $i = $last; $i >= 0; $i-- )
            {
                if ( $count >= $i )
                {
                    return $plural_versions[$i];
                }
            }
        }

        return $plural_string;
    }

    /**
     * Sets a (dot notated) language string
     *
     * @param string      $line     a (dot notated) language key
     * @param mixed       $value    the language string
     * @param string      $group    group
     * @param string|null $language name of the language to set, null for the configured language
     *
     * @return void the Arr::set result
     */
    public static function set(string $line, $value, ?string $group = null, ?string $language = null) : void
    {
        $group === null or $line = $group . '.' . $line;

        ($language === null) and $language = static::get_lang();

        isset(static::$lines[$language]) or static::$lines[$language] = [];

        Arr::set(static::$lines[$language], $line, Str::value($value));
    }

    /**
     * Deletes a (dot notated) language string
     *
     * @param string      $item     a (dot notated) language key
     * @param string      $group    group
     * @param string|null $language name of the language to set, null for the configured language
     *
     * @return array|bool the Arr::delete result, success boolean or array of success booleans
     */
    public static function delete(string $item, ?string $group = null, ?string $language = null)
    {
        $group === null or $item = $group . '.' . $item;

        ($language === null) and $language = static::get_lang();

        return isset(static::$lines[$language]) ? Arr::delete(static::$lines[$language], $item) : false;
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
            Config::set('language', $language);

            // do we need to reload?
            if ($reload)
            {
                foreach (static::$loaded_files as $file => $args)
                {
                    // reload with exactly the same arguments
                    if (strpos($file, $language . '/') !== 0)
                    {
                        call_user_func_array([static::class, 'load'], $args);
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
