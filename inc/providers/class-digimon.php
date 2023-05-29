<?php
/**
 * The Pokémon monster provider.
 *
 * @package Pokefever
 */

namespace Pokefever\Providers;

use Pokefever\Contracts\Monster_Provider;
use Pokefever\Models\Monster;
use Pokefever\Util;

/**
 * The Digimon monster provider.
 */
class Digimon implements Monster_Provider {

	/**
	 * The name of the provider.
	 *
	 * @return string
	 */
	public function name() : string {
		return __( 'Digimon', 'pokefever' );
	}

	/**
	 * The description of the provider.
	 *
	 * @return string
	 */
	public function description() : string {
		return __( 'Digimon are creatures that inhabit the Digital World, a virtual space created by humanity\'s various communication networks and their developers.', 'pokefever' );
	}

	/**
	 * The cover image for digimons.
	 *
	 * @return string|null
	 */
	public function cover_image() {
		return get_stylesheet_directory_uri() . '/img/digimon-cover.jpg';
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
	 * TODO: THIS IMPLEMENTATION IS LIMITED, IT SHOULD BE REPLACED WITH A BETTER ONE
	 *
	 * @return Monster
	 */
	public function generate() : Monster {

		$random = rand( 1, 1422 );

		$digimon_data = Util::call_api( "https://www.digi-api.com/api/v1/digimon/$random" );

		$digimon = array(
			'api_id'      => $digimon_data->id,
			'slug'        => strtolower( $digimon_data->name ),
			'type'        => $this->post_type_slug(),
			'name'        => $digimon_data->name,
			'description' => collect( $digimon_data->descriptions )->first()->description ?? null,
			'image'       => collect( $digimon_data->images )->first()->href ?? null,
			// 'taxonomies'  => array(
			// 'pokemon_move' => $this->get_moves( $pokemon_data->moves ),
			// 'pokemon_type' => $this->get_types( $pokemon_data->types ),
			// ),
			// 'meta'        => array(
			// 'weight'              => absint( $pokemon_data->weight ) / 10,
			// 'height'              => $pokemon_data->height * 10,
			// 'entry_number'        => $pokedex_entries->last()->entry_number ?? null,
			// 'game_name'           => $pokedex_entries->last()->pokedex->game_name ?? null,
			// 'entry_number_oldest' => $pokedex_entries->first()->entry_number ?? null,
			// 'game_name_oldest'    => $pokedex_entries->first()->pokedex->game_name ?? null,
			// ),
		);

		// dump( $digimon );

		/**
		 * Instantiate a new Monster object with the data we just got.
		 * This ensures that the data returned is valid, as the monster class validates
		 * the data after instantiation.
		 */
		return new Monster( $digimon );

	}

}
