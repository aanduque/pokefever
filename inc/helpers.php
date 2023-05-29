<?php // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.ShortPrefixPassed
/**
 * Helper functions for the pokefever theme.
 *
 * @package pokefever
 */

namespace Pokefever; // phpcs:ignore

use Pokefever\Pokefever;
use function Pokefever\container as app;
use function paginate_links as wp_paginate_links;

/**
 * Get the Pokerfever instance.
 *
 * @return Pokerfever
 */
function container() {
	return Pokefever::getInstance();
}

/**
 * Modified version of paginate_links() function that outputs Bootstrap compatible pagination.
 *
 * @see paginate_links()
 *
 * @param array $args Arguments to pass to paginate_links().
 * @return string The markup for the pagination links.
 */
function paginate_links( $args = array() ) {

	/**
	 * Since we are going to modify and loop each menu item, we need to make sure
	 * that the menu is always returned as an array of the individual itens .
	 */
	$args['type'] = 'array';

	$links = wp_paginate_links( $args );

	/**
	 * Make edits to the default markup to make it Bootstrap compatible.
	 */
	$links = collect( $links )->map(
		function( $link ) {
			$link = str_replace( 'page-numbers', 'page-link', $link );
			$link = str_replace( 'current', 'active', $link );

			if ( strpos( $link, 'span' ) !== false && strpos( $link, 'active' ) === false ) {
				$link = str_replace( 'page-link', 'page-link disabled', $link );
			}

			return "<li class=\"page-item\">$link</li>";
		}
	)->join( PHP_EOL );

	return "<ul class=\"pagination justify-content-center\">$links</ul>";

}

/**
 * Get the registered providers' post type slugs as well as their arguments.
 *
 * @return array
 */
function get_registered_post_types() {

	return collect( app()->get_providers() )->map(
		function( $provider ) {
			return $provider->post_type() ?? array();
		}
	)->toArray();

}

/**
 * Get the registered providers' post type slugs only.
 *
 * @return array
 */
function get_registered_post_types_slugs() {

	return collect( app()->get_providers() )->map(
		function( $provider ) {
			return $provider->post_type() ?? array();
		}
	)->pluck( 0 )->toArray();

}

/**
 * Get the card colors for a given monster, by id.
 *
 * @param int $monster_id The ID of the post associated with the monster.
 * @return string
 */
function get_monster_colors_for_card( int $monster_id ) {

	$monster = get_post( $monster_id );

	if ( ! $monster ) {
		return '';
	}

	$primary     = get_post_meta( $monster->ID, 'primary_color', true );
	$primary_rgb = Util::hex_to_rgb( $primary );

	if ( ! $primary_rgb ) {
		return '';
	}

	return "
		--bs-primary: $primary;
		--bs-primary-rgb: $primary_rgb->r,$primary_rgb->g,$primary_rgb->b;
		--bs-link-color: $primary;
		--bs-bg-opacity: 1;
		--pokemon-linear-gradient-angle: 90deg;
		--pokemon-linear-gradient: linear-gradient(var(--pokemon-linear-gradient-angle), white 0%, rgba(var(--bs-primary-rgb),0.6) 100%);
	";

}
