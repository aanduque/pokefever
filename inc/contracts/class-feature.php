<?php
/**
 * Feature interface.
 *
 * Gives us an easy way to add and control new features, while also
 * allowing us to display controls to turn them on and off, when needed.
 *
 * @package Pokefever
 */

namespace Pokefever\Contracts;

use Pokefever\Pokefever;

interface Feature {

	/**
	 * Runs during the initialization of the plugin, before it boots.
	 *
	 * @param Pokefever $app The main plugin instance.
	 * @return void
	 */
	public function register( Pokefever $app ) : void;

	/**
	 * Runs when the application is booting the registered features.
	 *
	 * @param Pokefever $app The main plugin instance.
	 * @return void
	 */
	public function boot( Pokefever $app ) : void;

}
