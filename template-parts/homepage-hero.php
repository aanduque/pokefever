<?php
/**
 * The home page hero.
 *
 * @package Pokefever
 */

defined( 'ABSPATH' ) || exit;
?>
<div id="hero" class="p-5 mb-4 border-bottom">
	<div class="container pt-5 pb-4 text-center text-lg-start">
		<h1 class="display-5 fw-bold"><?php the_title(); ?></h1>
		<div class="fs-4 col-lg-5">
			<?php the_content(); ?>
		</div>
	</div>
</div>
