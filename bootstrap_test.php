<?php
/**
 * Bootstrap velocite package for unit testing
 */

require 'vendor/autoload.php';

Velocite\Velocite::init(['app_path' => realpath(__DIR__ . '/src/tests/_app')]);