<?php
/**
 * Monster Provider Interface
 *
 * @package Pokefever
 */

namespace Pokefever\Contracts;

interface Monster_Provider {

	/**
	 * Info about the post type to be registered.
	 *
	 * @return array<string, array>
	 */
	public function post_type();

	/**
	 * Info about the taxonomies to be registered.
	 *
	 * @return array<string, array>
	 */
	public function taxonomies();

	/**
	 * Returns the data for a Monster
	 *
	 * @return Monster
	 */
	public function generate();

}
