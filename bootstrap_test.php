<?php
/**
 * Bootstrap velocite package for unit testing
 */

require 'vendor/autoload.php';

! defined('VELOCITE_ENV') and define('VELOCITE_ENV', 'test');

Velocite\Velocite::init(['app_path' => realpath(__DIR__ . '/src/tests/_app')]);