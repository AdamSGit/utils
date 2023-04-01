<?php
/**
 * Ascii table chars mapping config
 */

return [
    /*
     * -------------------------------------------------------------------------
     *  Active Driver
     * -------------------------------------------------------------------------
     */

    'driver' => 'file',

    /*
     * -------------------------------------------------------------------------
     *  Expiration
     * -------------------------------------------------------------------------
     */

    'expiration' => null,

    /*
     * Default content handlers: convert values to strings to be stored
     * You can set them per primitive type or object class like this:
     *   - 'string_handler' 		=> 'string'
     *   - 'array_handler'			=> 'json'
     *   - 'Some_Object_handler'	=> 'serialize'
     */

    /*
     * -------------------------------------------------------------------------
     *  File Driver Settings
     * -------------------------------------------------------------------------
     *
     *  If empty, the default path will be 'app/cache/'
     *
     */

    'file' => [
        'path' => '',
    ],

    /*
     * -------------------------------------------------------------------------
     *  Memcached Driver Settings
     * -------------------------------------------------------------------------
     */

    'memcached' => [
        /*
         * ---------------------------------------------------------------------
         *  Cache ID
         * ---------------------------------------------------------------------
         *
         *  Unique ID to distinguish fuel cache items from other cache
         *  stored on the same server(s).
         *
         */

        'cache_id'  => 'velocite',

        /*
         * ---------------------------------------------------------------------
         *  Servers
         * ---------------------------------------------------------------------
         *
         *  Servers and port numbers that run the memcached service.
         *
         */

        'servers' => [
            'default' => [
                'host'   => '127.0.0.1',
                'port'   => 11211,
                'weight' => 100,
            ],
        ],
    ],

    /*
     * -------------------------------------------------------------------------
     *  Redis Driver Settings
     * -------------------------------------------------------------------------
     */

    'redis' => [
        /*
         * ---------------------------------------------------------------------
         *  Database Name
         * ---------------------------------------------------------------------
         *
         *  Name of the redis database to use (as configured in 'config/db.php')
         *
         */

        'database' => 'default',
    ],
];
