<?php
/**
 * Set of php utils forked from Fuelphp framework
 */

namespace Velocite;

class ObjectA implements \ArrayAccess
{
    private $container = [];

    public function __construct()
    {
        $this->container = [
            'one'   => 1,
            'two'   => 2,
            'three' => 3,
        ];
    }

    public function offsetSet($offset, $value) : void
    {
        if (null === $offset)
        {
            $this->container[] = $value;
        }
        else
        {
            $this->container[$offset] = $value;
        }
    }

    public function offsetExists(mixed $offset) : bool
    {
        return isset($this->container[$offset]);
    }

    public function offsetUnset($offset) : void
    {
        unset($this->container[$offset]);
    }

    public function offsetGet(mixed $offset) : mixed
    {
        return $this->container[$offset] ?? null;
    }
}
