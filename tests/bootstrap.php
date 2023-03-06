<?php
/**
 * Bootstrap velocite package for unit testing
 */
! defined('VELOCITE_ENV') and define('VELOCITE_ENV', 'test');

require __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/ObjectA.php';

Velocite\Velocite::init(['app_path' => realpath(__DIR__ . '/_app')]);
