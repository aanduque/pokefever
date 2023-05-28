<?php
/**
 * Archive Hero
 *
 * @package Pokefever
 */

defined( 'ABSPATH' ) || exit;
?>
<div id="hero" class="p-5 mb-4 border-bottom">
	<div class="container pt-5 pb-4 text-center text-lg-start">
		<h1 class="display-5 fw-bold"><?php echo esc_html( post_type_archive_title( '', false ) ); ?></h1>
		<div class="fs-4 col-lg-5">
			<?php echo get_the_post_type_description(); ?>
		</div>
	</div>
</div>
