<?php

namespace Pokefever\Features\Required;

use Pokefever\Contracts\Feature;
use Pokefever\Pokefever;
use Pokefever\Providers\Pokemon as Pokemon_Provider;

class Pokemon implements Feature {

	public function register( Pokefever $app ): void {

		/**
		 * Registers the Pokémon monster provider.
		 */
		$app->register_provider( 'pokemon', Pokemon_Provider::class );

	}

	public function boot( Pokefever $app ): void {

		add_action( 'wp_ajax_load_oldest_pokedex_number', array( $this, 'load_oldest_pokedex_number_callback' ) );

		add_action( 'wp_ajax_nopriv_load_oldest_pokedex_number', array( $this, 'load_oldest_pokedex_number_callback' ) );

		add_filter(
			'wp_get_object_terms_args',
			function( $args ) {
				$args['orderby'] = 'term_order';
				return $args;
			}
		);

	}

	public function load_oldest_pokedex_number_callback() {
		check_ajax_referer( 'pokefever-nonce' );

		$post_id = absint( wp_unslash( $_POST['post_id'] ) );

		$pokedex_number_oldest = get_post_meta( $post_id, 'pokemon_pokedex_entry_number_oldest', true );

		if ( $pokedex_number_oldest ) {
			$pokedex_game_name_oldest = get_post_meta( $post_id, 'pokemon_pokedex_game_name_oldest', true );
			echo esc_html( $pokedex_number_oldest . ' - ' . $pokedex_game_name_oldest );
		} else {
			wp_send_json_error( __( 'No pokedex number found.', 'pokefever' ), 404 );
		}

		exit;
	}

}
