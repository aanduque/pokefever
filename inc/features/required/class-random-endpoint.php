<?php
/**
 * The Random Endpoint.
 *
 * @package Pokefever
 */

namespace Pokefever\Features\Required;

use Pokefever\Contracts\Monster_Provider;

/**
 * Class Random_Endpoint
 *
 * @package Pokefever\Features\Required
 */
class Random_Endpoint extends Endpoint {

	/**
	 * The endpoint path.
	 *
	 * @return string
	 */
	public function path() : string {

		return 'random';

	}

	/**
	 * Test to check if the current user can access this endpoint.
	 *
	 * @return bool
	 */
	public function check_permissions() : bool {

		return true;

	}

	/**
	 * Gets a random monster from the database and redirects to its page.
	 *
	 * @param Monster_Provider $provider The monster provider instance.
	 * @return void
	 */
	public function handle( Monster_Provider $provider ) {

		// Get a random monster from the database.
		$monster = get_posts(
			array(
				'post_type'      => $provider->post_type(),
				'posts_per_page' => 1,
				'orderby'        => 'rand',
			)
		);

		if ( ! $monster ) {
			// translators: %s is the name of the monster provider.
			wp_die( esc_html( sprintf( __( 'No %s found.', 'pokefever' ), $provider->name() ) ) );
		}

		// Redirect to the pokemon's page.
		wp_safe_redirect( get_permalink( $monster[0]->ID ) );

		exit;

	}

}
