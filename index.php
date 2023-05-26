<?php get_header(); ?>

<div class="container" id="pokemon">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<div class="row">
	</div>
</div>

<?php endwhile; ?>

<?php get_footer(); ?>
