<?php
/**
 * Set of php utils forked from Fuelphp framework
 */

namespace Velocite\Cache\Handler;

class Json implements Driver
{
	public function readable( mixed $contents )
	{
		$array = false;
		if (substr($contents, 0, 1) == 'a')
		{
			$contents = substr($contents, 1);
			$array = true;
		}

		return json_decode($contents, $array);
	}

	public function writable( mixed $contents )
	{
		$array = '';
		if (is_array($contents))
		{
			$array = 'a';
		}

		return $array . json_encode($contents);
	}
}
