<?php
/**
 * Db config
 */

return [
    /*
     * -------------------------------------------------------------------------
     *  Active Configurations
     * -------------------------------------------------------------------------
     *
     *  If you don't specify a DB configuration name when you create
     *  a database connection, the configuration to be used will be determined
     *  by the 'active' value.
     *
     */

    'active' => 'default',

    /*
     * -------------------------------------------------------------------------
     *  PDO
     * -------------------------------------------------------------------------
     *
     *  Base PDO configurations.
     *
     */

    'default' => [
        'type' => 'pdo',

        'connection' => [
            'dsn'        => '',
            'hostname'   => '',
            'username'   => null,
            'password'   => null,
            'database'   => '',
            'persistent' => false,
            'compress'   => false,
        ],

        'identifier'   => '`',
        'table_prefix' => '',
        'charset'      => 'utf8',
        'collation'    => false,
        'enable_cache' => true,
        'profiling'    => false,
        'readonly'     => false,
    ],

    /*
     * -------------------------------------------------------------------------
     *  MySQLi
     * -------------------------------------------------------------------------
     *
     *  Base MySQLi configurations.
     *
     */

    'mysqli' => [
        'type' => 'mysqli',

        'connection' => [
            'dsn'        => '',
            'hostname'   => '',
            'username'   => null,
            'password'   => null,
            'database'   => '',
            'persistent' => false,
            'compress'   => false,
        ],

        'identifier'   => '`',
        'table_prefix' => '',
        'charset'      => 'utf8',
        'collation'    => false,
        'enable_cache' => false,
        'profiling'    => false,
        'readonly'     => false,
    ],

    /*
     * -------------------------------------------------------------------------
     *  Redis
     * -------------------------------------------------------------------------
     *
     *  Base Redis configurations.
     *
     */

    'redis' => [
        'default' => [
            'hostname' => '127.0.0.1',
            'port'     => 6379,
            'timeout'  => null,
            'database' => 0,
        ],
    ],
];
