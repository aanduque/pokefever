<?php
/**
 * Feature interface.
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
