<?php
/**
 * Set of php utils forked from Fuelphp framework
 */

namespace Velocite\Cache\Handler;

interface Driver {
	/**
	 * Should make the contents readable
	 *
	 * @param   mixed $content
	 * @return  mixed
	 */
	public function readable( mixed $contents);

	/**
	 * Should make the contents writable
	 *
	 * @param   mixed $content
	 * @return  mixed
	 */
	public function writable( mixed $contents);
}
