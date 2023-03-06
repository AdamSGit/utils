<?php
/**
 * Set of php utils forked from Fuelphp framework
 */

namespace Velocite\Cache\Handler;

class Serialized implements Driver
{
	public function readable( mixed $contents)
	{
		return unserialize($contents);
	}

	public function writable( mixed $contents)
	{
		return serialize($contents);
	}

}
