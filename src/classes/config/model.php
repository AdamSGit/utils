<?php
/**
 * Set of php utils forked from Fuelphp framework
 */

namespace Velocite\Utils\Config;

/**
 * Config interface
 */
interface Model
{
    public function load(bool $overwrite = false);

    public function group();

    public function save(array $contents) : bool;
}
