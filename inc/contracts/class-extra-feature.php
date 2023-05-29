<?php
/**
 * Extra Feature interface.
 *
 * Gives us an easy way to add and control new features, while also
 * allowing us to display controls to turn them on and off, when needed.
 *
 * @package Pokefever
 */

namespace Pokefever\Contracts;

interface Extra_Feature {

	/**
	 * Defines the feature name, which is then used to store its activation status.
	 *
	 * @return string
	 */
	public function name() : string;

}
