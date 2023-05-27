<?php

namespace Pokerfever;

use League\ColorExtractor\ColorExtractor;
use League\ColorExtractor\Palette;
use League\ColorExtractor\Color;

final class Pokerfever {

	public static function init() {
		add_action( 'init', array( self::class, 'register_post_types' ) );

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

		add_filter( 'admin_post_thumbnail_html', array( self::class, 'pokefever_change_featured_image_label' ) );

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
		)->toArray();

		$pokemon = array(
			'post_title'   => ucfirst( $data->name ),
			'post_name'    => strtolower( $data->name ),
			'post_content' => self::get_english_description( $data->flavor_text_entries ),
			'post_status'  => 'publish',
			'post_type'    => 'pokemon',
			'meta_input'   => array(
				'pokemon_api_id' => $data->id,
				'pokemon_weight' => absint( $pokemon_data->weight ) / 10,
			),
		);

		// dd( $pokemon );

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

		// Send a call to the pokemon api to get the data for the pokemon with the number we generated.
		// Save the data to our local database.
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
				}

					.site {
						min-height: 100vh;
						display: flex;
						flex-direction: column;
					}
				
					body {
						height: 100vh;
						position: relative;
						background: linear-gradient(0deg, rgba(var(--bs-secondary-rgb),0.75) 0%, rgba(var(--bs-primary-rgb),1) 90%);
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

	public static function load_oldest_pokedex_number_callback() {
		$post_id = $_POST['post_id'];

		if ( $pokedex_number_oldest = get_post_meta( $post_id, 'pokedex_number_oldest', true ) ) {
			echo esc_html( $pokedex_number_oldest );
		} else {
			echo 'No data available.';
		}

		exit(); // this is required to terminate immediately and return a proper response
	}

	public static function pokefever_change_featured_image_label( $content ) {
		global $post_type;
		// var_dump( $content );
		// die;
		if ( $post_type == 'pokemon' ) {
			return str_replace( __( 'featured image' ), __( 'Pokemon image' ), $content );
		} else {
			return $content;
		}
	}

	public static function pokefever_save_pokemon_meta_box_data( $post_id ) {
		// Check if our nonce is set and verify that the nonce is valid.
		if ( ! isset( $_POST['pokemon_nonce'] ) || ! wp_verify_nonce( $_POST['pokemon_nonce'], basename( __FILE__ ) ) ) {
			return;
		}

		// Check the user's permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Store data in post meta table if present in post data```php
		if ( isset( $_POST['pokemon_weight'] ) ) {
			update_post_meta( $post_id, 'pokemon_weight', sanitize_text_field( $_POST['pokemon_weight'] ) );
		}

		if ( isset( $_POST['pokedex_old'] ) ) {
			update_post_meta( $post_id, 'pokedex_old', sanitize_text_field( $_POST['pokedex_old'] ) );
		}

		if ( isset( $_POST['pokedex_new'] ) ) {
			update_post_meta( $post_id, 'pokedex_new', sanitize_text_field( $_POST['pokedex_new'] ) );
		}

		if ( isset( $_POST['pokemon_attacks'] ) ) {
			// Sanitize the input as a string, but allow for comma-separated values
			$pokemon_attacks = sanitize_text_field( $_POST['pokemon_attacks'] );
			$pokemon_attacks = explode( ',', $pokemon_attacks );
			$pokemon_attacks = array_map( 'trim', $pokemon_attacks );
			$pokemon_attacks = implode( ',', $pokemon_attacks );

			update_post_meta( $post_id, 'pokemon_attacks', $pokemon_attacks );
		}
	}

	public static function pokefever_add_pokemon_meta_box() {
		add_meta_box(
			'pokemon_details', // id of the meta box
			'Pokemon Details', // title of the meta box
			array( self::class, 'pokefever_pokemon_meta_box_callback' ), // callback function that will echo the box content
			'pokemon', // post type where to add the meta box
			'normal', // position of the meta box (normal, side, advanced)
			'default' // priority (default, low, high, core)
		);
	}

	public static function pokefever_pokemon_meta_box_callback( $post ) {
		// Nonce field for security
		wp_nonce_field( basename( __FILE__ ), 'pokemon_nonce' );

		// Get the stored meta data
		$pokemon_weight  = get_post_meta( $post->ID, 'pokemon_weight', true );
		$pokedex_old     = get_post_meta( $post->ID, 'pokedex_old', true );
		$pokedex_new     = get_post_meta( $post->ID, 'pokedex_new', true );
		$pokemon_attacks = get_post_meta( $post->ID, 'pokemon_attacks', true );

		echo '<label for="pokemon_weight">Weight: </label>';
		echo '<input type="text" id="pokemon_weight" name="pokemon_weight" value="' . esc_attr( $pokemon_weight ) . '" />';

		echo '<label for="pokedex_old">Old Pokedex Number: </label>';
		echo '<input type="text" id="pokedex_old" name="pokedex_old" value="' . esc_attr( $pokedex_old ) . '" />';

		echo '<label for="pokedex_new">New Pokedex Number: </label>';
		echo '<input type="text" id="pokedex_new" name="pokedex_new" value="' . esc_attr( $pokedex_new ) . '" />';

		echo '<label for="pokemon_attacks">Attacks (separate with commas): </label>';
		echo '<textarea id="pokemon_attacks" name="pokemon_attacks">' . esc_textarea( $pokemon_attacks ) . '</textarea>';
	}

	public static function register_post_types() {
		register_post_type(
			'pokemon',
			array(
				'labels'       => array(
					'name'          => __( 'Pokémon', 'pokefever' ),
					'singular_name' => __( 'Pokémon', 'pokefever' ),
				),
				'public'       => true,
				'has_archive'  => true,
				'rewrite'      => array( 'slug' => 'pokemon' ),
				'show_in_rest' => false,
				'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
				'menu_icon'    => 'dashicons-palmtree',
			)
		);

		register_taxonomy(
			'pokemon_type',
			'pokemon',
			array(
				'label'        => __( 'Pokemon Type', 'pokefever' ),
				'hierarchical' => false,
			)
		);

		register_taxonomy(
			'pokemon_move',
			'pokemon',
			array(
				'label'        => __( 'Pokemon Move', 'pokefever' ),
				'hierarchical' => false,
			)
		);
	}

}
