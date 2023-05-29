<?php

namespace Pokefever;

use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use League\ColorExtractor\ColorExtractor;
use League\ColorExtractor\Palette;
use League\ColorExtractor\Color;
use LogicException;
use Pokefever\Models\Monster;
use function pf\container as app;

final class Pokefever extends Container {

	protected function __construct() {
		// Sets up the validator instance.
		$this->instance(
			'validator',
			new Factory(
				new Translator( new ArrayLoader(), 'en' ),
				$this,
			)
		);

		// Auto-injects the container inside the container.
		$this->instance( 'app', $this );

		// Boot the container.
		$this->boot();

		self::init();
	}

	public function boot() {

		$this->load_features();

		$this->boot_providers();

		$this->boot_features();

	}

	protected function load_features() {

		collect( $this->tagged( 'feature' ) )->each(
			function( $feature ) {
				$feature->register( $this );
			}
		);

	}

	protected function boot_features() {

		collect( $this->tagged( 'feature' ) )
			->filter(
				function( $feature ) {
					// Required features are always loaded.
					if ( $feature instanceof Contracts\Feature ) {
						return true;
					}

					return get_option( 'pokefever_feature_' . $feature->name(), true );
				}
			)
			->each(
				function( $feature ) {
					$feature->boot( $this );
				}
			);

	}

	protected function boot_providers() {

		foreach ( $this->get_providers() as $provider ) {

			// Registers the post types for this provider.
			list($post_type_slug, $post_type_args) = $provider->post_type();
			register_post_type( $post_type_slug, $post_type_args );

			// Then, we register the taxonomies for this provider.
			$taxonomies = $provider->taxonomies();
			foreach ( $taxonomies as $taxonomy_slug => $taxonomy ) {
				list($taxonomy_post_types, $taxonomy_args) = $taxonomy;
				register_taxonomy( $taxonomy_slug, $taxonomy_post_types, $taxonomy_args );
			}
		}

	}

	public function get_default_provider() {
		return apply_filters( 'pokefever/providers/default', 'pokemon', $this );
	}

	public function get_current_provider() {
		// get the current provider key saved on the cookie, if the user is not logged in or in the user meta, if the user is logged in.
		$current_provider_key = get_current_user_id() ? get_user_meta( get_current_user_id(), 'pokefever_current_provider', true ) : ( $_COOKIE['pokefever_current_provider'] ?? $this->get_default_provider() );

		return apply_filters( 'pokefever/providers/current', $this->get( $current_provider_key ), $this );
	}

	/**
	 * Allows us to register and tag instances in the container.
	 *
	 * @param string $key The container key to register the instance as.
	 * @param mixed  $value The instance to register.
	 * @param string $tag The tag to register the instance with.
	 * @return $this
	 */
	protected function register_as( string $key, $value, string $tag ) {
		$this->instance( $value::class, $value );

		if ( $value::class !== $key ) {
			$this->alias( $value::class, $key );
		}

		$this->tag( $key, $tag );

		return $this;
	}

	/**
	 * Registers a new feature in the container.
	 *
	 * @param Feature|Extra_Feature $feature The feature instance.
	 * @return $this
	 */
	public function register_feature( $feature ) {
		$feature_name = $feature::class;
		return $this->register_as( $feature_name, $feature, 'feature' );
	}

	/**
	 * Registers multiple features in the container.
	 *
	 * @param array<Feature|Extra_Feature> $features The features to register.
	 * @return $this
	 */
	public function register_features( array $features ) {
		foreach ( $features as $feature ) {
			$this->register_feature( $feature );
		}
		return $this;
	}

	/**
	 * Registers a new monster provider in the container.
	 *
	 * @param string $provider_name  The monster provider name. This is used as the container key.
	 * @param mixed  $provider The provider instance.
	 * @return $this
	 */
	public function register_provider( string $provider_name, $provider ) {
		return $this->register_as( $provider_name, $provider, 'provider' );
	}

	public function get_providers() {
		return $this->tagged( 'provider' );
	}

	public static function init() {

		add_action(
			'wp_head',
			function() {
				$post_type = get_post_type();

				if ( app()->has( $post_type ) ) {
					$provider    = app()->get( $post_type );
					$cover_image = $provider->cover_image();

					if ( $cover_image ) {
						echo '<style>#hero {
							--pokefever-hero-bg: url(' . esc_attr( $cover_image ) . ');
						}</style>';
					}
				}
			},
			99
		);

		add_action(
			'wp_footer',
			function() {
				global $wp_query;

				$post_id = $wp_query->get_queried_object_id();

				if ( $post_id ) {
					$primary_color   = get_post_meta( $post_id, 'pokemon_primary_color', true );
					$secondary_color = get_post_meta( $post_id, 'pokemon_secondary_color', true );

					if ( $primary_color ) {
						self::override_primary_color( $primary_color, $secondary_color ? $secondary_color : '#fff' );
					}
				}
				die;
			}
		);

		add_action(
			'template_redirect',
			function() {
				global $wp_query;
				if ( $wp_query->get( 'name' ) === 'generate' ) {
					self::generate_pokemon();
					die;
				}

				if ( $wp_query->get( 'name' ) === 'random' ) {
					self::random_pokemon();
					die;
				}
			}
		);

		// add_action( 'add_meta_boxes', array( self::class, 'pokefever_add_pokemon_meta_box' ) );

		add_action( 'save_post', array( self::class, 'pokefever_save_pokemon_meta_box_data' ) );

		add_action( 'wp_ajax_load_oldest_pokedex_number', array( self::class, 'load_oldest_pokedex_number_callback' ) );

		add_action( 'wp_ajax_nopriv_load_oldest_pokedex_number', array( self::class, 'load_oldest_pokedex_number_callback' ) );

		add_filter(
			'wp_get_object_terms_args',
			function( $args ) {
				$args['orderby'] = 'term_order';
				return $args;
			}
		);

		do_action( 'pokefever_loaded' );
	}

	protected static function random_pokemon() {

		// Get a random pokemon from the database.
		$pokemon = get_posts(
			array(
				'post_type'      => 'pokemon',
				'posts_per_page' => 1,
				'orderby'        => 'rand',
			)
		);

		if ( ! $pokemon ) {
			wp_die( 'No Pokemon found.' );
		}

		// Redirect to the pokemon's page.
		wp_safe_redirect( get_permalink( $pokemon[0]->ID ) );
		exit;
	}

	public static function generate_pokemon() {
		global $wpdb;

		new Monster(
			array(
				'name'        => 'sasa',
				'description' => 'sasa',
			)
		);

		$saved_api_ids = wp_cache_get( 'saved_api_ids', 'pokefever' );

		if ( ! is_array( $saved_api_ids ) ) {
			// Compile a list of the existing pokemon on our local database.
			$saved_api_ids = $wpdb->get_col( "SELECT meta_value from {$wpdb->prefix}postmeta WHERE meta_key = 'pokemon_api_id'" );

			wp_cache_set( 'saved_api_ids', $saved_api_ids, 'pokefever', 5 * MINUTE_IN_SECONDS );
		}

		// Need a way of grabbing and save the total number of pokemon available on the API.
		$total_pokemon = 1010; // TODO: grab this value from the api and save it to a transient.

		// Need to generate a random number between 1 and the total number of pokemon available on the API - excluding the ones we already have.
		$range = range( 1, $total_pokemon );

		// Remove the numbers we already have from the range.
		$available_api_ids = array_diff( $range, $saved_api_ids );

		// Grab a random number from the available numbers.
		$random_number = array_rand( $available_api_ids );

		$results = wp_remote_get( "https://pokeapi.co/api/v2/pokemon-species/{$available_api_ids[ $random_number ]}" );

		if ( is_wp_error( $results ) ) {
			// translators: %s is the error message.
			wp_die( sprintf( esc_html_e( 'Something went wrong: %s', 'pokefever' ), $results->get_error_message() ) );
		}

		$body = wp_remote_retrieve_body( $results );

		$data = json_decode( $body );

		$pokemon_data = self::get_pokemon_data_from_api( $data );

		$types = collect( $pokemon_data->types )->map(
			function( $type ) {
				$term = get_term_by( 'slug', $type->type->name, 'pokemon_type', ARRAY_A );

				if ( ! $term ) {
					wp_insert_term( ucfirst( $type->type->name ), 'pokemon_type' );
					$term = get_term_by( 'slug', $type->type->name, 'pokemon_type', ARRAY_A );
				}

				return $term['slug'] ?? null;
			}
		)->toArray();

		$moves = collect( $pokemon_data->moves )
		->filter(
			function( $move ) {
				return $move->version_group_details[0]->move_learn_method->name === 'egg'; // This gives us the moves that the pokemon already knows when it is born.
			}
		)->map(
			function( $move ) {
				$term = get_term_by( 'slug', $move->move->name, 'pokemon_move', ARRAY_A );

				$results = wp_remote_get( $move->move->url );

				if ( is_wp_error( $results ) ) {
					// translators: %s is the error message.
					wp_die( sprintf( esc_html_e( 'Something went wrong: %s', 'pokefever' ), $results->get_error_message() ) );
				}

				$body = wp_remote_retrieve_body( $results );

				$data = json_decode( $body );

				$description = self::get_english_description( $data->flavor_text_entries );

				if ( ! $term ) {
					wp_insert_term(
						ucwords( collect( explode( '-', $move->move->name ) )->join( ' ' ) ),
						'pokemon_move',
						array(
							'slug'        => $move->move->name,
							'description' => $description,
						)
					);
					$term = get_term_by( 'slug', $move->move->name, 'pokemon_move', ARRAY_A );
				}

				return $term['slug'] ?? null;
			}
		)->toArray();

		$pokedex_entries = collect( $data->pokedex_numbers )->filter(
			function( $pokedex ) {
				return true;
			}
		)->map(
			function( $pokedex ) {
				$results = wp_remote_get( $pokedex->pokedex->url );

				if ( is_wp_error( $results ) ) {
					// translators: %s is the error message.
					wp_die( sprintf( esc_html_e( 'Something went wrong: %s', 'pokefever' ), $results->get_error_message() ) );
				}

				$body = wp_remote_retrieve_body( $results );

				$data = json_decode( $body );

				$pokedex->pokedex->description = self::get_english_description( $data->descriptions, 'description' );

				$pokedex->pokedex->version_groups = $data->version_groups;

				$pokedex->pokedex->game_name = ( collect( $pokedex->pokedex->version_groups )->first()->name ) ?? null;

				return $pokedex;
			}
		)->filter(
			function( $pokedex ) {
				return $pokedex->pokedex->game_name !== null;
			}
		);

		$pokemon = array(
			'post_title'   => ucfirst( $data->name ),
			'post_name'    => strtolower( $data->name ),
			'post_content' => self::get_english_description( $data->flavor_text_entries ),
			'post_status'  => 'publish',
			'post_type'    => 'pokemon',
			'meta_input'   => array(
				'pokemon_api_id'                      => $data->id,
				'pokemon_weight'                      => absint( $pokemon_data->weight ) / 10,
				'pokemon_height'                      => $pokemon_data->height * 10,
				'pokemon_pokedex_entry_number'        => $pokedex_entries->last()->entry_number ?? null,
				'pokemon_pokedex_game_name'           => $pokedex_entries->last()->pokedex->game_name ?? null,
				'pokemon_pokedex_entry_number_oldest' => $pokedex_entries->first()->entry_number ?? null,
				'pokemon_pokedex_game_name_oldest'    => $pokedex_entries->first()->pokedex->game_name ?? null,
			),
		);

		$pokemon_post = wp_insert_post( $pokemon );

		if ( is_wp_error( $pokemon_post ) ) {
			wp_die( $pokemon_post->get_error_message() );
		}

		wp_set_post_terms( $pokemon_post, $types, 'pokemon_type' );
		wp_set_post_terms( $pokemon_post, $moves, 'pokemon_move' );

		// Set the featured image.
		$image_url = $pokemon_data->sprites->other->{'official-artwork'}->front_default ?? null;

		if ( $image_url ) {
			$attachment_id = self::set_post_thumbnail_from_image_url( $image_url, $pokemon_post, $data->name );

			if ( ! is_wp_error( $attachment_id ) ) {
				$image_path = get_attached_file( $attachment_id );

				if ( $image_path ) {

					// Set the primary and secondary colors.
					$colors = self::extract_colors_from_image( $image_path );

					collect( $colors )->each(
						function( $color, $index ) use ( $pokemon_post ) {
							update_post_meta( $pokemon_post, "pokemon_{$index}_color", $color );
						}
					);
				}
			}
		}

		wp_safe_redirect( get_permalink( $pokemon_post ) );

		exit;
	}

	protected static function extract_colors_from_image( $image_url ) {
		$colors = array();

		// TODO: this needs to be taken care of.
		set_error_handler( function() {} );

		$palette = Palette::fromFilename( $image_url, Color::fromHexToInt( '#FFFFFF' ) );

		// An extractor is built from a palette.
		$extractor = new ColorExtractor( $palette );

		// it defines an extract method which return the most “representative” colors.
		$colors = $extractor->extract( 2 );

		$keys = array(
			'primary',
			'secondary',
		);

		return collect( $colors )->mapWithKeys(
			function( $color, $index ) use ( $keys ) {
				return array( $keys[ $index ] => Color::fromIntToHex( $color ) );
			}
		)->toArray();

		restore_error_handler();
	}

	/**
	 * Downloads an image from the specified URL and attaches it to a post as a post thumbnail.
	 *
	 * @param string $image_url    The URL of the image to download.
	 * @param int    $post_id The post ID the post thumbnail is to be associated with.
	 * @param string $desc    Optional. Description of the image.
	 * @return string|WP_Error Attachment ID, WP_Error object otherwise.
	 */
	public static function set_post_thumbnail_from_image_url( $image_url, $post_id, $desc ) {
		// Set variables for storage, fix file filename for query strings.
		preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $image_url, $matches );
		if ( ! $matches ) {
			 return new WP_Error( 'image_sideload_failed', __( 'Invalid image URL' ) );
		}

		require_once ABSPATH . '/wp-admin/includes/file.php';
		require_once ABSPATH . '/wp-admin/includes/media.php';
		require_once ABSPATH . '/wp-admin/includes/image.php';

		$file_array         = array();
		$file_array['name'] = basename( $matches[0] );

		// Download file to temp location.
		$file_array['tmp_name'] = download_url( $image_url );

		// If error storing temporarily, return the error.
		if ( is_wp_error( $file_array['tmp_name'] ) ) {
			return $file_array['tmp_name'];
		}

		// Do the validation and storage stuff.
		$id = media_handle_sideload( $file_array, $post_id, $desc );

		// If error storing permanently, unlink.
		if ( is_wp_error( $id ) ) {
			@unlink( $file_array['tmp_name'] );
			return $id;
		}

		set_post_thumbnail( $post_id, $id );

		return $id;
	}

	public static function get_pokemon_data_from_api( $pokemon_species_response ) {
		$endpoint = collect( $pokemon_species_response->varieties )->filter(
			function( $varietty ) {
				return $varietty->is_default;
			}
		)->first()->pokemon->url ?? null; // TODO: fix the min php version checked by the WPCS.

		if ( ! $endpoint ) {
			return null;
		}

		$results = wp_remote_get( $endpoint );

		if ( is_wp_error( $results ) ) {
			// translators: %s is the error message.
			wp_die( sprintf( esc_html_e( 'Something went wrong: %s', 'pokefever' ), $results->get_error_message() ) );
		}

		$body = wp_remote_retrieve_body( $results );

		$data = json_decode( $body );

		return $data;
	}

	public static function get_english_description( $available_descriptions, $attribute_name = 'flavor_text' ) {
		$english_description = collect( $available_descriptions )->filter(
			function( $description ) {
				return 'en' === $description->language->name;
			}
		)->first()->{$attribute_name} ?? __( 'No description available.', 'pokefever' );

		return str_replace( "\n", ' ', $english_description );
	}

	public static function hex_to_rgb( $hex ) {
		list($r, $g, $b) = sscanf( $hex, '#%02x%02x%02x' );
		return (object) array(
			'red'   => $r,
			'green' => $g,
			'blue'  => $b ?? 0,
		);
	}

	public static function override_primary_color( $primary = '#e74c3c', $secondary = '#c0392b' ) {
		$primary_rgb   = self::hex_to_rgb( $primary );
		$secondary_rgb = self::hex_to_rgb( $secondary );

		$bg_image = get_stylesheet_directory_uri() . '/img/bg-pokeball.png';

					echo "<style>:root {
					--bs-primary: $primary;
					--bs-secondary: $secondary;
					--bs-primary-rgb: $primary_rgb->red,$primary_rgb->green,$primary_rgb->blue;
					--bs-secondary-rgb: $secondary_rgb->red,$secondary_rgb->green,$secondary_rgb->blue;
					--bs-link-color: $primary;
					--bs-bg-opacity: 1;
					--pokemon-linear-gradient: linear-gradient(0deg, rgba(var(--bs-secondary-rgb),0.75) 0%, rgba(var(--bs-primary-rgb),1) 90%);
				}

					.site {
						min-height: 100vh;
						display: flex;
						flex-direction: column;
					}
				
					body {
						min-height: 100vh;
						position: relative;
						background: var(--pokemon-linear-gradient);
					}

					body::before {
						content: \" \";
						display: block;
						content: \" \";
						position: absolute;
						background-image: url(\"$bg_image\");
						background-repeat: no-repeat;
						background-position: top right;
						opacity: 0.08;
						top: 0;
						bottom: 0;
						right: 0;
						left: 0;
					}
				</style>";
				// );
	}

	public static function override_card_colors( $primary = '#e74c3c', $secondary = '#c0392b' ) {
		$primary_rgb   = self::hex_to_rgb( $primary );
		$secondary_rgb = self::hex_to_rgb( $secondary );

		if ( ! $primary_rgb ) {
			return '';
		}

		return "
		--bs-primary: $primary;
		--bs-primary-rgb: $primary_rgb->red,$primary_rgb->green,$primary_rgb->blue;
		--bs-link-color: $primary;
		--bs-bg-opacity: 1;
		--pokemon-linear-gradient-angle: 90deg;
		--pokemon-linear-gradient: linear-gradient(var(--pokemon-linear-gradient-angle), white 0%, rgba(var(--bs-primary-rgb),0.6) 100%);";
	}

	public static function load_oldest_pokedex_number_callback() {
		check_ajax_referer( 'pokefever-nonce' );

		$post_id = $_POST['post_id'] ?? 0;

		if ( $pokedex_number_oldest = get_post_meta( $post_id, 'pokemon_pokedex_entry_number_oldest', true ) ) {
			$pokedex_game_name_oldest = get_post_meta( $post_id, 'pokemon_pokedex_game_name_oldest', true );
			echo esc_html( $pokedex_number_oldest . ' - ' . $pokedex_game_name_oldest );
		} else {
			wp_send_json_error( __( 'No pokedex number found.', 'pokefever' ), 404 );
		}

		exit(); // this is required to terminate immediately and return a proper response
	}

}
