<?php
/**
 * Image config
 */

return [
    /*
     * -------------------------------------------------------------------------
     *  Driver
     * -------------------------------------------------------------------------
     *
     *  The driver to be used. Available options are:
     *
     *      'gd', 'imagemagick' or 'imagick'
     *
     */

    'driver' => 'gd',

    /*
     * -------------------------------------------------------------------------
     *  Background Color
     * -------------------------------------------------------------------------
     *
     *  Sets the background color of the image.
     *
     *  Set to null for a transparent background.
     *
     */

    'bgcolor' => null,

    /*
     * -------------------------------------------------------------------------
     *  Watermark
     * -------------------------------------------------------------------------
     *
     *  Sets the transparency of any watermark added to the image.
     *
     */

    'watermark_alpha' => 75,

    /*
     * -------------------------------------------------------------------------
     *  Quality
     * -------------------------------------------------------------------------
     *
     *  Quality of the image being saved or output, if the format supports it.
     *
     */

    'quality' => 100,

    /*
     * -------------------------------------------------------------------------
     *  Filetype
     * -------------------------------------------------------------------------
     *
     *  Lets you use a default container for images.
     *
     *  This will be overrided by:
     *
     *      Image::output('png') or Image::save('file.png')
     *
     *  Example:
     *
     *      'png', 'bmp', 'jpeg', etc.
     *
     */

    'filetype' => null,

    /*
     * -------------------------------------------------------------------------
     *  Imagemagick Path
     * -------------------------------------------------------------------------
     *
     *  The install location of the imagemagick executables.
     *
     */

    'imagemagick_dir' => '/usr/bin/',

    /*
     * -------------------------------------------------------------------------
     *  Temporary Directory
     * -------------------------------------------------------------------------
     *
     *  Temporary directory to store image files in that are being edited.
     *
     */

    'temp_dir' => APPPATH . 'tmp' . DS,

    /*
     * -------------------------------------------------------------------------
     *  Temporary File Name
     * -------------------------------------------------------------------------
     *
     *  The string of text to append to the image.
     *
     */

    'temp_append' => 'velociteimage_',

    /*
     * -------------------------------------------------------------------------
     *  Queue
     * -------------------------------------------------------------------------
     *
     *  Whether the queue should be cleared after a 'save()', 'save_pa()'
     *  or 'output()'.
     *
     */

    'clear_queue' => true,

    /*
     * -------------------------------------------------------------------------
     *  Queue
     * -------------------------------------------------------------------------
     *
     *  Set to false to automatically reload the image (false).
     *  Or set to true to keep the changes when saving or outputting.
     *
     */

    'persistence' => false,

    /*
     * -------------------------------------------------------------------------
     *  Debug
     * -------------------------------------------------------------------------
     *
     *  Set to true to enable class debugging.
     *
     */

    'debug' => false,

    /*
     * -------------------------------------------------------------------------
     *  Presets
     * -------------------------------------------------------------------------
     *
     *  These presets allow you to call controlled manipulations.
     *
     *  Example:
     *
     *      'example' => array(
     *          'quality' => 100,
     *          'bgcolor' => null,
     *          'actions' => array(
     *              array('crop_resize', 200, 200),
     *              array('border', 20, "#f00"),
     *              array('rounded', 10),
     *              array('output', 'png')
     *          )
     *      )
     *
     *  [!] WARNING:
     *
     *  Config values here will override the current configuration.
     *  Driver cannot be changed in here.
     *
     */

    'presets' => [],
];
