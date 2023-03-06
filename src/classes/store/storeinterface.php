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
    public function load( array $locations ) : array;

    public function group() : string;

    public function save( string $location, array $contents) : bool;
}
