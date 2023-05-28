<?php
/**
 * Pagination for archive pages.
 *
 * @package Pokefever
 */

defined( 'ABSPATH' ) || exit;

/**
 * Use our custom pagination function, in place of the default WordPress one.
 *
 * @see inc/helpers.php
 */
use function pf\paginate_links;

echo paginate_links(
	array(
		'prev_text' => __( '&laquo;', 'pokefever' ),
		'next_text' => __( '&raquo;', 'pokefever' ),
		'type'      => 'array',
	)
);
