<?php
/**
 * The Generate Endpoint.
 *
 * @package Pokefever
 */

namespace Pokefever\Features\Required;

use Pokefever\Contracts\Monster_Provider;

/**
 * Class Generate_Endpoint
 *
 * @package Pokefever\Features\Required
 */
class Generate_Endpoint extends Endpoint {

	/**
	 * The endpoint path.
	 *
	 * @return string
	 */
	public function path() : string {

		return 'generate';

	}

	/**
	 * Generates and saves a monster, redirecting to the permalink on success.
	 *
	 * @param Monster_Provider $provider The monster provider instance.
	 * @return void
	 */
	public function handle( Monster_Provider $provider ) {

		try {

			/**
			 * Ask the provider to generate a monster.
			 */
			$monster = $provider->generate();

			/**
			 * Attempts to save the monster.
			 */
			$monster_id = $monster->save();

			/**
			 * Redirects to the monster permalink.
			 */
			wp_safe_redirect( get_permalink( $monster_id ) );

			exit;

		} catch ( \Throwable $th ) {

			/**
			 * Something went wrong.
			 * Get the error message and pass it to wp_die.
			 */
			wp_die( esc_html( $th->getMessage() ) );

		}

	}

}
