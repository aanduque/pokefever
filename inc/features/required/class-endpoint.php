<?php
/**
 * The template for displaying search results pages.
 *
 * @package Pokefever
 */

namespace Pokefever\Features\Required;

use Pokefever\Contracts\Feature;
use Pokefever\Contracts\Monster_Provider;
use Pokefever\Pokefever;
use function Pokefever\container as app;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class Endpoint
 *
 * @package Pokefever\Features\Required
 */
abstract class Endpoint implements Feature {

	/**
	 * The endpoint path.
	 *
	 * This is the endpoint path that will be used to match the request.
	 * Example: 'generate' or 'random'.
	 *
	 * @return string
	 */
	abstract public function path(): string;

	/**
	 * Test to check if the current user can access this endpoint.
	 *
	 * @return bool
	 */
	abstract public function check_permissions(): bool;

	/**
	 * Handle the endpoint request.
	 *
	 * This handler is only performed if the request matches the endpoint path.
	 *
	 * @param Monster_Provider $provider The monster provider instance.
	 * @return mixed
	 */
	public function handle( Monster_Provider $provider ) {}

	/**
	 * Register the endpoint.
	 *
	 * @param Pokefever $app The container instance.
	 * @return void
	 */
	public function boot( Pokefever $app ): void {

		/**
		 * Register the endpoint.
		 *
		 * Template Redirect is the last action before the template is loaded.
		 * It allows us to be sure the query is set up and ready to go.
		 */
		add_action(
			'template_redirect',
			array( $this, 'maybe_handle' )
		);

	}

	/**
	 * Check if the request matches this endpoint path and handle i, if that's the case.
	 *
	 * @return void
	 */
	public function maybe_handle() {

		global $wp_query;

		/**
		 * Check the query to see if we are in the right place.
		 */
		if ( $wp_query->get( 'name' ) !== $this->path() ) {
			return;
		}

		/**
		 * Check if the current user has the right permissions to access
		 * this endpoint.
		 */
		if ( ! $this->check_permissions() ) {

			wp_die( esc_html( __( 'You do not have permission to access this page.', 'pokefever' ) ) );

		}

		/**
		 * Run the endpoint handler via the container.
		 *
		 * Using the container allows us to inject dependencies into the handler.
		 */
		app()->call( array( $this, 'handle' ) );

	}

	/**
	 * Register things on the container.
	 *
	 * @param Pokefever $app The container instance.
	 * @return void
	 */
	public function register( Pokefever $app ): void {}

}
