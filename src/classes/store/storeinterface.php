<?php
/**
 * Set of php utils forked from Fuelphp framework
 */

namespace Velocite\Store;

/**
 * Config interface
 */
interface StoreInterface
{
    public function load(bool $overwrite = false) : array;

    public function group() : string;

    public function save(array $contents) : bool;
}
