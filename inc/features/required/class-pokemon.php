<?php
/**
 * The template for displaying search results pages.
 *
 * @package Pokefever
 */

namespace Pokefever\Features\Required;

use LogicException;
use Pokefever\Contracts\Feature;
use Pokefever\Pokefever;
use Pokefever\Providers\Pokemon as Pokemon_Provider;
use TypeError;

use function Pokefever\get_monster_attribute;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class Pokemon.
 *
 * @package Pokefever\Features\Required
 */
class Pokemon implements Feature {

	/**
	 * Registers the Pokémon monster provider.
	 *
	 * @param Pokefever $app The container instance.
	 * @return void
	 */
	public function register( Pokefever $app ): void {

		/**
		 * Registers the Pokémon monster provider.
		 */
		$app->register_provider( 'pokemon', Pokemon_Provider::class );

	}

	/**
	 * Boot the feature.
	 *
	 * @param Pokefever $app The container instance.
	 * @return void
	 */
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

	/**
	 * Load the oldest pokedex number for a monster.
	 *
	 * @return void
	 */
	public function load_oldest_pokedex_number_callback() {

		check_ajax_referer( 'pokefever-nonce' );

		$post_id = absint( wp_unslash( $_POST['post_id'] ?? '' ) );

		$monster_post = get_post( $post_id );

		if ( ! $monster_post ) {
			wp_send_json_error( __( 'No monster found.', 'pokefever' ), 404 );
			exit;
		}

		$pokedex_number_oldest = get_monster_attribute( 'entry_number_oldest', null, $monster_post );

		if ( $pokedex_number_oldest ) {
			$pokedex_game_name_oldest = get_monster_attribute( 'game_name_oldest', null, $monster_post );
			echo esc_html( '#' . str_pad( $pokedex_number_oldest, 4, '0', STR_PAD_LEFT ) . ' in ' . $pokedex_game_name_oldest );
		} else {
			wp_send_json_error( __( 'No pokedex number found.', 'pokefever' ), 404 );
		}

		exit;

	}

}
