<?php
/**
 * Set of php utils forked from Fuelphp framework
 */

namespace Velocite\Store;

/**
 * DB config data parser
 */
class Db implements ConfigInterface
{
    use Vars;

    protected $identifier;

    protected $ext = '.db';

    protected $vars = [];

    protected $database;

    protected $table;

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

        $this->database = \Config::get('config.database', null);
        $this->table    = \Config::get('config.table_name', 'config');
    }

    /**
     * Loads the config file(s).
     *
     * @param bool $overwrite Whether to overwrite existing values
     * @param bool $cache     this parameter will ignore in this implement
     *
     * @throws \Database_Exception
     *
     * @return array the config array
     */
    public function load(bool $overwrite = false, bool $cache = true) : array
    {
        $config = [];

        // try to retrieve the config from the database
        try
        {
            $result = \DB::select('config')->from($this->table)->where('identifier', '=', $this->identifier)->execute($this->database);
        }
        catch (Database_Exception $e)
        {
            // strip the actual query from the message
            $msg = $e->getMessage();
            $msg = substr($msg, 0, strlen($msg)  - strlen(strrchr($msg, ':')));

            // and rethrow it
            throw new \Database_Exception($msg, $e->getCode(), $e, $e->GetDbCode());
        }

        // did we succeed?
        if ($result->count())
        {
            empty($result[0]['config']) or $config = unserialize($this->parse_vars($result[0]['config']));
        }

        return $config;
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
     * @return bool DB result
     */
    public function save($contents) : bool
    {
        // prep the contents
        $this->prep_vars($contents);
        $contents = serialize($contents);

        // update the config in the database
        $result = \DB::update($this->table)->set(['config' => $contents, 'hash' => uniqid()])->where('identifier', '=', $this->identifier)->execute($this->database);

        // if there wasn't an update, do an insert
        if ($result === 0)
        {
            list($notused, $result) = \DB::insert($this->table)->set(['identifier' => $this->identifier, 'config' => $contents, 'hash' => uniqid()])->execute($this->database);
        }

        return $result === 1;
    }
}
