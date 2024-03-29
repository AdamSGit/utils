<?php
/**
 * Set of php utils forked from Fuelphp framework
 */

namespace Velocite\Utils;

/**
 * String transformation class
 */
class Inflector
{
    /**
     * @var array default list of uncountable words, in English
     */
    protected static $uncountable_words = [
        'equipment', 'information', 'rice', 'money',
        'species', 'series', 'fish', 'meta',
    ];

    /**
     * @var array default list of iregular plural words, in English
     */
    protected static $plural_rules = [
        '/^(ox)$/i'                 => '\1\2en',     // ox
        '/([m|l])ouse$/i'           => '\1ice',      // mouse, louse
        '/(matr|vert|ind)ix|ex$/i'  => '\1ices',     // matrix, vertex, index
        '/(x|ch|ss|sh)$/i'          => '\1es',       // search, switch, fix, box, process, address
        '/([^aeiouy]|qu)y$/i'       => '\1ies',      // query, ability, agency
        '/(hive)$/i'                => '\1s',        // archive, hive
        '/(?:([^f])fe|([lr])f)$/i'  => '\1\2ves',    // half, safe, wife
        '/sis$/i'                   => 'ses',        // basis, diagnosis
        '/([ti])um$/i'              => '\1a',        // datum, medium
        '/(p)erson$/i'              => '\1eople',    // person, salesperson
        '/(m)an$/i'                 => '\1en',       // man, woman, spokesman
        '/(c)hild$/i'               => '\1hildren',  // child
        '/(buffal|tomat)o$/i'       => '\1\2oes',    // buffalo, tomato
        '/(bu|campu)s$/i'           => '\1\2ses',    // bus, campus
        '/(alias|status|virus)$/i'  => '\1es',       // alias
        '/(octop)us$/i'             => '\1i',        // octopus
        '/(ax|cris|test)is$/i'      => '\1es',       // axis, crisis
        '/s$/'                      => 's',          // no change (compatibility)
        '/$/'                       => 's',
    ];

    /**
     * @var array default list of iregular singular words, in English
     */
    protected static $singular_rules = [
        '/(matr)ices$/i'                                                   => '\1ix',
        '/(vert|ind)ices$/i'                                               => '\1ex',
        '/^(ox)en/i'                                                       => '\1',
        '/(alias)es$/i'                                                    => '\1',
        '/([octop|vir])i$/i'                                               => '\1us',
        '/(cris|ax|test)es$/i'                                             => '\1is',
        '/(shoe)s$/i'                                                      => '\1',
        '/(o)es$/i'                                                        => '\1',
        '/(bus|campus)es$/i'                                               => '\1',
        '/([m|l])ice$/i'                                                   => '\1ouse',
        '/(x|ch|ss|sh)es$/i'                                               => '\1',
        '/(m)ovies$/i'                                                     => '\1\2ovie',
        '/(s)eries$/i'                                                     => '\1\2eries',
        '/([^aeiouy]|qu)ies$/i'                                            => '\1y',
        '/([lr])ves$/i'                                                    => '\1f',
        '/(tive)s$/i'                                                      => '\1',
        '/(hive)s$/i'                                                      => '\1',
        '/([^f])ves$/i'                                                    => '\1fe',
        '/(^analy)ses$/i'                                                  => '\1sis',
        '/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '\1\2sis',
        '/([ti])a$/i'                                                      => '\1um',
        '/(p)eople$/i'                                                     => '\1\2erson',
        '/(m)en$/i'                                                        => '\1an',
        '/(s)tatuses$/i'                                                   => '\1\2tatus',
        '/(c)hildren$/i'                                                   => '\1\2hild',
        '/(n)ews$/i'                                                       => '\1\2ews',
        '/([^us])s$/i'                                                     => '\1',
    ];

    /**
     * Load any localized rules on first load
     */
    public static function _init() : void
    {
        static::load_rules();
    }

    /**
     * Load any localized rulesets based on the current language configuration
     * If not exists, the current rules remain active
     */
    public static function load_rules() : void
    {
        \Lang::load('inflector', true, false, true);

        if ($rules = \Lang::get('inflector.uncountable_words', []))
        {
            static::$uncountable_words = $rules;
        }

        if ($rules = \Lang::get('inflector.singular_rules', []))
        {
            static::$singular_rules = $rules;
        }

        if ($rules = \Lang::get('inflector.plural_rules', []))
        {
            static::$plural_rules = $rules;
        }
    }

    /**
     * Add order suffix to numbers ex. 1st 2nd 3rd 4th 5th
     *
     * @param int $number the number to ordinalize
     *
     * @return string the ordinalized version of $number
     *
     * @link    http://snipplr.com/view/4627/a-function-to-add-a-prefix-to-numbers-ex-1st-2nd-3rd-4th-5th/
     */
    public static function ordinalize(int $number) : string
    {
        if ( ! is_numeric($number))
        {
            return $number;
        }

        if (in_array(($number % 100), range(11, 13)))
        {
            return $number . 'th';
        }


        switch ($number % 10)
        {
            case 1:
                return $number . 'st';

                break;

            case 2:
                return $number . 'nd';

                break;

            case 3:
                return $number . 'rd';

                break;

            default:
                return $number . 'th';

                break;
        }
    }

    /**
     * Gets the plural version of the given word
     *
     * @param string $word  the word to pluralize
     * @param int    $count number of instances
     *
     * @return string the plural version of $word
     */
    public static function pluralize(string $word, int $count = 0) : string
    {
        $result = (string) $word;

        // If a counter is provided, and that equals 1
        // return as singular.
        if ($count === 1)
        {
            return $result;
        }

        if ( ! static::is_countable($result))
        {
            return $result;
        }

        foreach (static::$plural_rules as $rule => $replacement)
        {
            if (preg_match($rule, $result))
            {
                $result = preg_replace($rule, $replacement, $result);

                break;
            }
        }

        return $result;
    }

    /**
     * Gets the singular version of the given word
     *
     * @param string $word the word to singularize
     *
     * @return string the singular version of $word
     */
    public static function singularize(string $word) : string
    {
        $result = (string) $word;

        if ( ! static::is_countable($result))
        {
            return $result;
        }

        foreach (static::$singular_rules as $rule => $replacement)
        {
            if (preg_match($rule, $result))
            {
                $result = preg_replace($rule, $replacement, $result);

                break;
            }
        }

        return $result;
    }

    /**
     * Takes a string that has words separated by underscores and turns it into
     * a CamelCased string.
     *
     * @param string $underscored_word the underscored word
     *
     * @return string the CamelCased version of $underscored_word
     */
    public static function camelize(string $underscored_word) : string
    {
        return preg_replace_callback(
            '/(^|_)(.)/',
            static function ($parm) {
                return strtoupper($parm[2]);
            },
            (string) $underscored_word
        );
    }

    /**
     * Takes a CamelCased string and returns an underscore separated version.
     *
     * @param string $camel_cased_word the CamelCased word
     *
     * @return string an underscore separated version of $camel_cased_word
     */
    public static function underscore(string $camel_cased_word) : string
    {
        return Str::strtolower(preg_replace('/([A-Z]+)([A-Z])/', '\1_\2', preg_replace('/([a-z\d])([A-Z])/', '\1_\2', (string) $camel_cased_word)));
    }

    /**
     * Translate string to 7-bit ASCII
     * Only works with UTF-8.
     *
     * @param string $str             string to translate
     * @param bool   $allow_non_ascii whether to remove non ascii
     *
     * @return string translated string
     */
    public static function ascii(string $str, bool $allow_non_ascii = false) : string
    {
        // Translate unicode characters to their simpler counterparts
        \Config::load('ascii', true);
        $foreign_characters = \Config::get('ascii');

        $str = preg_replace(array_keys($foreign_characters), array_values($foreign_characters), $str);

        if ( ! $allow_non_ascii)
        {
            return preg_replace('/[^\x09\x0A\x0D\x20-\x7E]/', '', $str);
        }

        return $str;
    }

    /**
     * Converts your text to a URL-friendly title so it can be used in the URL.
     * Only works with UTF8 input and and only outputs 7 bit ASCII characters.
     *
     * @param string $str             the text
     * @param string $sep             the separator
     * @param bool   $lowercase       whether to convert to lowercase
     * @param bool   $allow_non_ascii whether to allow non ascii
     *
     * @return string the new title
     */
    public static function friendly_title(string $str, string $sep = '-', bool $lowercase = false, bool $allow_non_ascii = false) : string
    {
        // Remove tags
        $str = Security::strip_tags($str);

        // Decode all entities to their simpler forms
        $str = html_entity_decode($str, ENT_QUOTES, 'UTF-8');

        // Only allow 7bit characters
        $str = static::ascii($str, $allow_non_ascii);

        if ($allow_non_ascii)
        {
            // Strip regular special chars
            $str = preg_replace("#[\.;:\]\}\[\{\+\)\(\*&\^\$\#@\!±`%~']#iu", '', $str);
        }
        else
        {
            // Strip unwanted characters
            $str = preg_replace('#[^a-z0-9]#i', $sep, $str);
        }

        // Remove all quotes
        $str = preg_replace("#[\"\']#", '', $str);

        // Replace apostrophes by separators
        $str = preg_replace("#[\’]#", '-', $str);

        // Replace repeating characters
        $str = preg_replace('#[/_|+ -]+#u', $sep, $str);

        // Remove separators from both ends
        $str = trim($str, $sep);

        // And convert to lowercase if needed
        if ($lowercase === true)
        {
            $str = Str::strtolower($str);
        }

        return $str;
    }

    /**
     * Turns an underscore or dash separated word and turns it into a human looking string.
     *
     * @param string $str       the word
     * @param string $sep       the separator (either _ or -)
     * @param bool   $lowercase lowercase string and upper case first
     *
     * @return string the human version of given string
     */
    public static function humanize(string $str, string $sep = '_', bool $lowercase = true) : string
    {
        // Allow dash, otherwise default to underscore
        $sep = $sep != '-' ? '_' : $sep;

        if ($lowercase === true)
        {
            $str = \Str::ucfirst($str);
        }

        return str_replace($sep, ' ', (string) $str);
    }

    /**
     * Takes the class name out of a modulized string.
     *
     * @param string $class_name_in_module the modulized class
     *
     * @return string the string without the class name
     */
    public static function demodulize(string $class_name_in_module) : string
    {
        return preg_replace('/^.*::/', '', (string) $class_name_in_module);
    }

    /**
     * Takes the namespace off the given class name.
     *
     * @param string $class_name the class name
     *
     * @return string the string without the namespace
     */
    public static function denamespace(string $class_name) : string
    {
        $class_name = trim($class_name, '\\');

        if ($last_separator = strrpos($class_name, '\\'))
        {
            $class_name = substr($class_name, $last_separator + 1);
        }

        return $class_name;
    }

    /**
     * Returns the namespace of the given class name.
     *
     * @param string $class_name the class name
     *
     * @return string the string without the namespace
     */
    public static function get_namespace(string $class_name) : string
    {
        $class_name = trim($class_name, '\\');

        if ($last_separator = strrpos($class_name, '\\'))
        {
            return substr($class_name, 0, $last_separator + 1);
        }

        return '';
    }

    /**
     * Takes a class name and determines the table name.  The table name is a
     * pluralized version of the class name.
     *
     * @param string $class_name the table name
     *
     * @return string the table name
     */
    public static function tableize(string $class_name) : string
    {
        $class_name = static::denamespace($class_name);

        if (strncasecmp($class_name, 'Model_', 6) === 0)
        {
            $class_name = substr($class_name, 6);
        }

        return Str::strtolower(static::pluralize(static::underscore($class_name)));
    }

    /**
     * Takes an underscored classname and uppercases all letters after the underscores.
     *
     * @param string $class classname
     * @param string $sep   separator
     *
     * @return string
     */
    public static function words_to_upper(string $class, string $sep = '_') : string
    {
        return str_replace(' ', $sep, ucwords(str_replace($sep, ' ', $class)));
    }

    /**
     * Takes a table name and creates the class name.
     *
     * @param string $name           the table name
     * @param bool   $force_singular whether to singularize the table name or not
     *
     * @return string the class name
     */
    public static function classify(string $name, bool $force_singular = true) : string
    {
        $class = ($force_singular) ? static::singularize($name) : $name;

        return static::words_to_upper($class);
    }

    /**
     * Gets the foreign key for a given class.
     *
     * @param string $class_name     the class name
     * @param bool   $use_underscore whether to use an underscore or not
     *
     * @return string the foreign key
     */
    public static function foreign_key(string $class_name, bool $use_underscore = true) : string
    {
        $class_name = static::denamespace(Str::strtolower($class_name));

        if (strncasecmp($class_name, 'Model_', 6) === 0)
        {
            $class_name = substr($class_name, 6);
        }

        return static::underscore(static::demodulize($class_name)) . ($use_underscore ? '_id' : 'id');
    }

    /**
     * Checks if the given word has a plural version.
     *
     * @param string $word the word to check
     *
     * @return bool if the word is countable
     */
    public static function is_countable(string $word) : bool
    {
        return ! (\in_array(Str::strtolower((string) $word), static::$uncountable_words));
    }
}
