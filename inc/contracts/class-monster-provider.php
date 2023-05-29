<?php
/**
 * Monster Provider Interface
 *
 * @package Pokefever
 */

namespace Pokefever\Contracts;

use Pokefever\Models\Monster;

interface Monster_Provider {

	/**
	 * The name of the provider.
	 *
	 * @return string
	 */
	public function name() : string;

	/**
	 * A short description of the provider.
	 *
	 * This info is displayed publicly, so keep it short and sweet.
	 *
	 * @return string
	 */
	public function description() : string;

	/**
	 * The URL to the provider's cover image.
	 *
	 * Return null if the provider doesn't have a cover image.
	 *
	 * @return string|null
	 */
	public function cover_image();

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
	public function generate() : Monster;

}
