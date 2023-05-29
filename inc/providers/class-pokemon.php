<?php
/**
 * The Pokémon monster provider.
 *
 * @package Pokefever
 */

namespace Pokefever\Providers;

use Illuminate\Support\Collection;
use Pokefever\Contracts\Monster_Provider;
use Pokefever\Models\Monster;
use Pokefever\Util;

/**
 * The Pokémon monster provider.
 */
class Pokemon implements Monster_Provider {

	/**
	 * The Pokémon monster provider.
	 *
	 * @return string
	 */
	public function name() : string {
		return 'Pokémon';
	}

	/**
	 * A short description of the pokemon provider.
	 *
	 * @return string
	 */
	public function description() : string {
		return __( 'Pokémon are creatures of all shapes and sizes who live in the wild or alongside humans.', 'pokefever' );
	}

	/**
	 * The URL to the provider's cover image.
	 *
	 * @return string|null
	 */
	public function cover_image() {
		return get_stylesheet_directory_uri() . '/img/pokemon-cover.jpg';
	}

	/**
	 * The post type slug.
	 *
	 * @return string
	 */
	public function post_type_slug() {
		return 'pokemon';
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
				'menu_icon'    => 'dashicons-share-alt',
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

		/**
		 * Describes the Pokémon type taxonomy.
		 *
		 * We are using the Pokémon type taxonomy to categorize the Pokémon by their type.
		 * Taxonomies are known data structures in WordPress, which means that by choosing
		 * to use a taxonomy instead of a custom field, we are making it easier for other
		 * developers to understand our code, as well as making it easier for other plugins
		 * to interact with our data (caching plugins, SEO plugins, etc.)
		 */
		$taxonomies['pokemon_type'] = array(
			'pokemon',
			array(
				'label'        => __( 'Pokemon Type', 'pokefever' ),
				'hierarchical' => false,
			),
		);

		/**
		 * Describe the Pokémon move taxonomy.
		 */
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
	 * Returns a list of pokemon api ids that are already saved in the database.
	 *
	 * This is used to make sure we don't get a pokemon we already have.
	 *
	 * @return array
	 */
	protected function get_saved_api_ids() {
		global $wpdb;

		$saved_api_ids = wp_cache_get( 'saved_api_ids', 'pokefever' );

		if ( ! is_array( $saved_api_ids ) ) {
			// Compile a list of the existing pokemon on our local database.
			$saved_api_ids = $wpdb->get_col( "SELECT meta_value from {$wpdb->prefix}postmeta WHERE meta_key = 'pokemon_api_id'" );

			wp_cache_set( 'saved_api_ids', $saved_api_ids, 'pokefever', 5 * MINUTE_IN_SECONDS );
		}

		return $saved_api_ids;
	}

	/**
	 * Returns the total number of pokemon available on the API.
	 *
	 * This is used to generate a random pokemon id. The results of this API call are cached
	 * for 5 minutes, to avoid hitting the API too much unnecessarily
	 *
	 * @return int
	 */
	protected function get_total_number_of_api_ids(): int {

		$total_pokemon = get_transient( 'pokefever_total_pokemon' );

		if ( false === $total_pokemon ) {

			try {

				$total_pokemon = Util::call_api( 'https://pokeapi.co/api/v2/pokemon-species' )->count;

				set_transient( 'pokefever_total_pokemon', $total_pokemon, 5 * MINUTE_IN_SECONDS );

			} catch ( \Throwable $th ) {

				/**
				 * As an extra measure, we have a fallback with the latest known value.
				 */
				$total_pokemon = 1010;

			}
		}

		return $total_pokemon;

	}

	/**
	 * Generates a random pokemon id that is aware of the pokemon we already have.
	 *
	 * @return int
	 */
	protected function generate_random_pokemon_api_id() : int {

		$total_pokemon = $this->get_total_number_of_api_ids();

		/**
		 * Need to generate a random number between 1 and the total number of pokemon available
		 * on the API - excluding the ones we already have.
		 */
		$range = range( 1, $total_pokemon );

		/**
		 * Remove the numbers we already have from the range.
		 */
		$available_api_ids = array_diff( $range, $this->get_saved_api_ids() );

		/**
		 * Grab a random number from the numbers we have left on the range.
		 */
		return $available_api_ids[ array_rand( $available_api_ids ) ];
	}

	/**
	 * Uses the PokeAPI to get the pokemon data and return a valid Monster object.
	 *
	 * @throws \Exception If the API call fails ot anything else fails.
	 * @return Monster
	 */
	public function generate() : Monster {

		/**
		 * Generate a random pokemon id.
		 *
		 * Our internal logic makes sure we don't get a pokemon we already have.
		 * If we did things in a different way, what could happen is that as we get more and more
		 * pokemon the chances of getting a pokemon we already have would increase.
		 * This would make future calls to the generate endpoint slower and slower, as the change
		 * to get a new pokemon would be smaller and smaller.
		 */
		$random_pokemon_id = $this->generate_random_pokemon_api_id();

			/**
			 * First, get the random pokemon data from the API /pokemon endpoint.
			 *
			 * This endpoint gives us the pokemon's name, id, sprites, types, moves, etc.
			 * For other info, we need to make additional calls to the API.
			 * Most specifically, we need to get the pokemon's species data from the /pokemon-species endpoint.
			 */
			$pokemon_data = Util::call_api( "https://pokeapi.co/api/v2/pokemon/{$random_pokemon_id}" );

			if (! ($pokemon_data->species->url ?? null ) ) {

				dd($random_pokemon_id, $pokemon_data);

			}

			/**
			 * Now, get the pokemon's species data from the API /pokemon-species endpoint.
			 */
			$pokemon_species_data = Util::call_api( $pokemon_data->species->url ?? null );

			/**
			 * Get the Pokedex entries for the pokemon.
			 */
			$pokedex_entries = $this->get_pokedex_entries( $pokemon_species_data->pokedex_numbers );

			$pokemon = array(
				'api_id'      => $pokemon_data->id,
				'slug'        => strtolower( $pokemon_data->name ),
				'type'        => $this->post_type_slug(),
				'name'        => $this->get_localized_text( $pokemon_species_data->names, 'name' ),
				'description' => $this->get_localized_text( $pokemon_species_data->flavor_text_entries, 'flavor_text' ),
				'image'       => $pokemon_data->sprites->other->{'official-artwork'}->front_default ?? null,
				'taxonomies'  => array(
					'pokemon_move' => $this->get_moves( $pokemon_data->moves ),
					'pokemon_type' => $this->get_types( $pokemon_data->types ),
				),
				'meta'        => array(
					'weight'              => absint( $pokemon_data->weight ) / 10,
					'height'              => $pokemon_data->height * 10,
					'entry_number'        => $pokedex_entries->last()->entry_number ?? null,
					'game_name'           => $pokedex_entries->last()->pokedex->game_name ?? null,
					'entry_number_oldest' => $pokedex_entries->first()->entry_number ?? null,
					'game_name_oldest'    => $pokedex_entries->first()->pokedex->game_name ?? null,
				),
			);

			/**
			 * Instantiate a new Monster object with the data we just got.
			 * This ensures that the data returned is valid, as the monster class validates
			 * the data after instantiation.
			 */
			return new Monster( $pokemon );

	}

	/**
	 * Get the pokemon pokedex data.
	 *
	 * @param mixed $pokedex_numbers The pokedex numbers for the pokemon as received from the API.
	 * @return Collection A collection of pokedex entries, enriched by the pokedex endpoint.
	 */
	protected function get_pokedex_entries( $pokedex_numbers ) : Collection {

		/**
		 * Creates a collection based on the received numbers.
		 */
		$pokedex_numbers_collection = collect( $pokedex_numbers );

		/**
		 * Get the data from the API for a particular collection entry.
		 * This function is used in the map method below.
		 */
		$get_pokedex_data_from_api = function( $pokedex ) {
			$data = Util::call_api( $pokedex->pokedex->url );

			$pokedex->pokedex->description = self::get_localized_text( $data->descriptions, 'description' );

			$pokedex->pokedex->version_groups = $data->version_groups;

			$pokedex->pokedex->game_name = ( collect( $pokedex->pokedex->version_groups )->first()->name ) ?? null;

			return $pokedex;
		};

		/**
		 * Only keep the entries that have a game name.
		 * This function is used in the filter method below.
		 */
		$filter_by_game_name = function( $pokedex ) {
				return null !== $pokedex->pokedex->game_name;
		};

		/**
		 * Use our functions to get the data from the API and filter it.
		 */
		return $pokedex_numbers_collection
			->map( $get_pokedex_data_from_api )
			->filter( $filter_by_game_name );

	}

	/**
	 * Returns the list of moves of a particular pokemon.
	 *
	 * @param array $moves_list The list of moves as received from the API.
	 * @return array An array of slugs of the moves.
	 */
	protected function get_moves( $moves_list ) : array {

		/**
		 * First, we convert the array of moves into a collection.
		 */
		$moves_collection = collect( $moves_list );

		/**
		 * Filter out all moves that are not learned as soon as the Pokemon is hatched.
		 */
		$filter_by_learn_method = function( $move ) {
				return 'egg' === $move->version_group_details[0]->move_learn_method->name;
		};

		/**
		 * Checks if the move already exists in the database and creates it if it doesn't.
		 * Then, it returns the slug of the move (as saved in the database).
		 * This is passed down to the map function, bellow, so the slugs can be used to set the terms.
		 *
		 * @link https://developer.wordpress.org/reference/functions/wp_set_post_terms/
		 */
		$maybe_create_move = function( $move ) {
			$term = get_term_by( 'slug', $move->move->name, 'pokemon_move', ARRAY_A );

			/**
			 * If the move doesn't exist in the database, we create it.
			 * To do that, we need to fetch the move data from the API.
			 */
			if ( ! $term ) {

				$data = Util::call_api( $move->move->url );

				wp_insert_term(
					$this->get_localized_text( $data->names, 'name' ),
					'pokemon_move',
					array(
						'slug'        => $move->move->name,
						'description' => $this->get_localized_text( $data->flavor_text_entries, 'flavor_text' ),
					)
				);

				$term = get_term_by( 'slug', $move->move->name, 'pokemon_move', ARRAY_A );
			}

			return $term['slug'] ?? null;
		};

		/**
		 * Use our filter and map functions to get the slugs of the moves.
		 */
		return $moves_collection
			->filter( $filter_by_learn_method )
			->map( $maybe_create_move )
			->toArray();

	}

	/**
	 * Get the types of the Pokemon.
	 *
	 * @param mixed $types_list The type list as returned by the Pokemon API.
	 * @return array A list of slugs of the types.
	 */
	protected function get_types( $types_list ) : array {

		/**
		 * First, we convert the array of types into a collection.
		 */
		$types_collection = collect( $types_list );

		/**
		 * Checks if the type already exists in the database and creates it if it doesn't.
		 * Then, it returns the slug of the type (as saved in the database).
		 * This is passed down to the map function, bellow, so the slugs can be used to set the terms.
		 *
		 * @link https://developer.wordpress.org/reference/functions/wp_set_post_terms/
		 */
		$maybe_create_type = function( $type ) {
			$term = get_term_by( 'slug', $type->type->name, 'pokemon_type', ARRAY_A );

			if ( ! $term ) {
				wp_insert_term( ucfirst( $type->type->name ), 'pokemon_type' );
				$term = get_term_by( 'slug', $type->type->name, 'pokemon_type', ARRAY_A );
			}

				return $term['slug'] ?? null;
		};

		/**
		 * Use our map function to get the slugs of the types.
		 */
		return $types_collection
			->map( $maybe_create_type )
			->toArray();

	}

	/**
	 * Get the localized text from a list of descriptions in different languages.
	 *
	 * @param mixed  $available_descriptions The list of descriptions as returned by the Pokemon API.
	 * @param string $attribute_name The name of the attribute to get from the text entry.
	 * @param string $language The language to return. Defaults to 'en'.
	 * @return string
	 */
	public function get_localized_text( $available_descriptions, $attribute_name = 'flavor_text', $language = 'en' ) {

		$localized_text = collect( $available_descriptions )->filter(
			function( $text_variation ) use ( $language ) {
				return $language === $text_variation->language->name;
			}
		)->first()->{$attribute_name} ?? __( 'No description available.', 'pokefever' );

		return str_replace( "\n", ' ', $localized_text );

	}

}
