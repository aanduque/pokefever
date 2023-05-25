<?php get_header(); ?>

<div class="container">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<div class="row">
			<div class="col-md-6">
				<!-- Display the featured image (Photo of the Pokémon) -->
				<?php if ( has_post_thumbnail() ) : ?>
					<img src="<?php the_post_thumbnail_url(); ?>" class="img-fluid">
				<?php endif; ?>

				<!-- Pokémon name (post title) -->
				<h1><?php the_title(); ?></h1>

				<!-- Pokémon description (post content) -->
				<div><?php the_content(); ?></div>

				<!-- Pokémon types (primary and secondary) -->
				<?php
				$types = get_the_terms( get_the_ID(), 'pokemon_type' );
				if ( $types ) :
					?>
					<div>
						<h5>Types</h5>
						<ul>
							<?php
							foreach ( $types as $type ) :
								?>
								<li><?php echo esc_html( $type->name ); ?></li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>
			</div>

			<div class="col-md-6">
				<!-- Pokedex number in the most recent version of the game -->
				<?php if ( $pokedex_number = get_post_meta( get_the_ID(), 'pokedex_number', true ) ) : ?>
					<div>
						<h5>Pokedex Number (Most Recent)</h5>
						<p><?php echo esc_html( $pokedex_number ); ?></p>
					</div>
				<?php endif; ?>

				<!-- Button to load the Pokedex number in the oldest version of the game -->
				<button id="load-oldest-pokedex-number" class="btn btn-primary">Load Oldest Pokedex Number</button>
				<div id="oldest-pokedex-number"></div>

				<!-- Pokémon attacks -->
				<?php if ( $attacks = get_post_meta( get_the_ID(), 'attacks', true ) ) : ?>
					<div>
						<h5>Attacks</h5>
						<table class="table">
							<thead>
								<tr>
									<th>Name</th>
									<th>Description</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $attacks as $attack ) : ?>
									<tr>
										<td><?php echo esc_html( $attack['name'] ); ?></td>
										<td><?php echo esc_html( $attack['description'] ); ?></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php endif; ?>
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
