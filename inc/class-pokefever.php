<?php

namespace Pokerfever;

final class Pokerfever {

	public static function init() {
		self::register_post_types();

		add_action( 'add_meta_boxes', array( self::class, 'pokefever_add_pokemon_meta_box' ) );

		add_action( 'save_post', array( self::class, 'pokefever_save_pokemon_meta_box_data' ) );

		add_filter( 'admin_post_thumbnail_html', array( self::class, 'pokefever_change_featured_image_label' ) );

		add_action( 'wp_ajax_load_oldest_pokedex_number', array( self::class, 'load_oldest_pokedex_number_callback' ) );

		add_action( 'wp_ajax_nopriv_load_oldest_pokedex_number', array( self::class, 'load_oldest_pokedex_number_callback' ) );

		do_action( 'pokefever_loaded' );
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

	protected static function register_post_types() {
		register_post_type(
			'pokemon',
			array(
				'labels'       => array(
					'name'          => __( 'Pokémon' ),
					'singular_name' => __( 'Pokémon' ),
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
				'label'        => __( 'Pokemon Type' ),
				'hierarchical' => true,
			)
		);
	}

}
