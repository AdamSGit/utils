<?php
/**
 * File config
 */

return [
    /*
     * -------------------------------------------------------------------------
     *  Base Configurations
     * -------------------------------------------------------------------------
     *
     *  The default 'File_Area' configurations.
     *
     */

    'base_config' => [
        /*
         * ---------------------------------------------------------------------
         *  Basedir
         * ---------------------------------------------------------------------
         *
         *  Path to 'basedir' restriction. Set to null for no restriction.
         *
         */

        'basedir' => null,

        /*
         * ---------------------------------------------------------------------
         *  Extensions
         * ---------------------------------------------------------------------
         *
         *  Allowed extensions. Set to null for allow all extensions.
         *
         */

        'extensions' => null,

        /*
         * ---------------------------------------------------------------------
         *  URL
         * ---------------------------------------------------------------------
         *
         *  Base URL for files. Set to null to make it unavailable.
         *
         */

        'url' => null,

        /*
         * ---------------------------------------------------------------------
         *  File Lock
         * ---------------------------------------------------------------------
         *
         *  Whether or not to use file locks when doing file operations.
         *
         */

        'use_locks' => false,

        /*
         * ---------------------------------------------------------------------
         *  File Handler
         * ---------------------------------------------------------------------
         *
         *  File driver per file extension.
         *
         */

        'file_handlers' => [],
    ],

    /*
     * -------------------------------------------------------------------------
     *  Areas
     * -------------------------------------------------------------------------
     *
     *  Pre-configure some areas.
     *
     *  Use these examples to enable:
     *
     *      'area_name' => array(
     *          'basedir'       => null,
     *          'extensions'    => null,
     *          'url'           => null,
     *          'use_locks'     => false,
     *          'file_handlers' => array(),
     *      )
     *
     */

    'areas' => [],

    /*
     * -------------------------------------------------------------------------
     *  Magic File
     * -------------------------------------------------------------------------
     *
     *  The 'fileinfo()' magic filename.
     *
     */

    'magic_file' => null,

    /*
     * -------------------------------------------------------------------------
     *  Permissions
     * -------------------------------------------------------------------------
     *
     *  Default file and directory permissions.
     *
     */

    'chmod' => [
        /*
         * ---------------------------------------------------------------------
         *  Files
         * ---------------------------------------------------------------------
         *
         *  Permissions for newly created files.
         *
         */

        'files' => 0666,

        /*
         * ---------------------------------------------------------------------
         *  Folders
         * ---------------------------------------------------------------------
         *
         *  Permissions for newly created folders.
         *
         */

        'folders' => 0777,
    ],
];
