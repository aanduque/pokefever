<?php
/**
 * The Pokémon monster provider.
 *
 * @package Pokefever
 */

namespace Pokefever\Providers;

use Pokefever\Contracts\Monster_Provider;
use Pokefever\Models\Monster;

/**
 * The Digimon monster provider.
 */
class Digimon implements Monster_Provider {

	public function name() : string {
		return __( 'Digimon', 'pokefever' );
	}

	public function description() : string {
		return __( 'Digimon are creatures that inhabit the Digital World, a virtual space created by humanity\'s various communication networks and their developers.', 'pokefever' );
	}

	public function cover_image() {
		return get_stylesheet_directory_uri() . '/img/pokemon-cover.jpg';
	}

	/**
	 * The post type slug.
	 *
	 * @return string
	 */
	public function post_type_slug() {
		return 'digimon';
	}

	/**
	 * Info about the post type to be registered.
	 *
	 * @return array<string, array>
	 */
	public function post_type() {
		return array(
			$this->post_type_slug(),
			array(
				'description'  => $this->description(),
				'labels'       => array(
					'name'          => $this->name(),
					'singular_name' => $this->name(),
				),
				'public'       => true,
				'has_archive'  => true,
				'rewrite'      => array( 'slug' => $this->post_type_slug() ),
				'show_in_rest' => false,
				'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
				'menu_icon'    => 'dashicons-admin-site-alt3',
			),
		);
	}

	/**
	 * Info about the taxonomies to be registered.
	 *
	 * @return array<string, array>
	 */
	public function taxonomies() {
		$taxonomies = array();

		// Add the Pokémon type taxonomy.
		$taxonomies['pokemon_type'] = array(
			'pokemon',
			array(
				'label'        => __( 'Pokemon Type', 'pokefever' ),
				'hierarchical' => false,
			),
		);

		// Add the Pokémon move taxonomy.
		$taxonomies['pokemon_move'] = array(
			'pokemon',
			array(
				'label'        => __( 'Pokemon Move', 'pokefever' ),
				'hierarchical' => false,
			),
		);

		return $taxonomies;
	}

	/**
	 * Returns the data for a Monster
	 *
	 * @return Monster
	 */
	public function generate() : Monster {
		return new Monster(
			array()
		);
	}

}
