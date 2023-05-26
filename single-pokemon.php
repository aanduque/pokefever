<?php get_header(); ?>

<div class="container" id="pokemon">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<div class="row">
			<div class="col-md-6 order-md-2">
				<!-- Display the featured image (Photo of the Pokémon) -->
				<?php if ( has_post_thumbnail() ) : ?>
					<style>
						.pokemon-image {
							position: relative;
							text-align: center;
						}

						.pokemon-image::after {
							width: 60%;
							margin: -80px auto 20px auto;
							opacity: 0.5;
							display: block;
							height: 60px;
							border-radius: 100%;
							background-color: #000;
							content: " ";
							z-index: 0;
						}

						.pokemon-image img {
							position: relative;
							z-index: 1;
						}
					</style>
					<div class="pokemon-image">
						<img src="<?php the_post_thumbnail_url(); ?>" class="img-fluid">
					</div>
				<?php endif; ?>

			</div>

			<div class="col-md-6 d-flex flex-column justify-content-center">

				<!-- Pokémon name (post title) -->
				<h1>
					<?php the_title(); ?>
					<?php if ( $pokedex_number = get_post_meta( get_the_ID(), 'pokedex_new', true ) ) : ?>
						<small class="text-body-secondary text-muted">#<?php echo esc_html( str_pad( $pokedex_number, 3, '0', STR_PAD_LEFT ) ); ?></small>
					<?php endif; ?>
				</h1>

				<!-- Pokémon types (primary and secondary) -->
				<?php
				$types = get_the_terms( get_the_ID(), 'pokemon_type' );
				if ( $types ) :
					?>
						<div>
							<?php
							foreach ( $types as $type ) :
								?>
								<span class="badge badge-lg rounded-pill text-bg-info"><?php echo esc_html( $type->name ); ?></span>
							<?php endforeach; ?>
						</div>
				<?php endif; ?>

				<!-- Pokémon description (post content) -->
				<div><?php the_content(); ?></div>

				<div class="card">
					<div class="card-body">
					<h5 class="card-title">Card title</h5>
					<p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
					<a href="#" class="btn btn-primary">Go somewhere</a>
					</div>
				</div>

				<!-- Button to load the Pokedex number in the oldest version of the game -->
				<!-- <button id="load-oldest-pokedex-number" class="btn btn-primary">Load Oldest Pokedex Number</button>
				<div id="oldest-pokedex-number"></div> -->

			</div>
		</div>
	<?php endwhile; ?>
</div>

<script>
jQuery(document).ready(function($) {
	$('#load-oldest-pokedex-number').click(function() {
		var data = {
			'action': 'load_oldest_pokedex_number',
			'post_id': <?php echo get_the_ID(); ?>
		};

		$.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', data, function(response) {
			$('#oldest-pokedex-number').html(response);
		});
	});
});
</script>

<?php get_footer(); ?>
