<?php
/**
 * Set of php utils forked from Fuelphp framework
 */

namespace Velocite\Cache\Handler;

class Plaintext implements Driver
{
    public function readable( mixed $contents)
    {
        return (string) $contents;
    }

    public function writable( mixed $contents)
    {
        return (string) $contents;
    }
}
