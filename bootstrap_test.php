<?php
/**
 * Bootstrap velocite package
 */

// Environment
! defined('VELOCITE_ENV') and define('VELOCITE_ENV', 'test');

// Make sure app path is defined
! defined ('APPPATH') and define('APPPATH', __DIR__);

include './bootstrap.php';
