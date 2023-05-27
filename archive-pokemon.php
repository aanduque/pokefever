<?php

use Pokerfever\Pokerfever;

 get_header(); ?>

<div class="p-5 mb-4 bg-body-tertiary">
	<div class="container py-5 text-center text-lg-start">
		<h1 class="display-5 fw-bold"><?php the_title(); ?></h1>
		<div class="fs-4">
			<?php the_content(); ?>
		</div>
		<a href="#pokemon-list" class="btn btn-primary text-white btn-lg">
			<?php esc_html_e( 'Check our Pokémon list', 'pokemon' ); ?>
		</a>
	</div>
</div>

<?php

// // Get all Pokémon
// $pokemons = new WP_Query(
// array(
// 'post_type'      => 'pokemon',
// 'posts_per_page' => $pokemons_per_page,
// 'paged'          => $current_page,
// 'offset'         => ( $current_page - 1 ) * $pokemons_per_page,
// )
// );

$pagination = paginate_links(
	array(
		// 'base'      => add_query_arg( 'paged', '%#%' ),
		// 'format'    => '',
		'prev_text' => __( '&laquo;', 'pokemon' ),
		'next_text' => __( '&raquo;', 'pokemon' ),
		'type'      => 'array',
	)
);

?>

<div class="container" id="list">
	<form id="filter">

	<div class="card p-3 mb-4">
		<div class="row gy-2 gx-3 flex-column flex-md-row align-items-md-center">
			<div class="col-auto flex-fill">
				<div class="row d-block d-md-flex gy-2 gx-3 align-items-center">
				<div class="col-auto">
					<label class="visually-hidden" for="autoSizingInputGroup">Username</label>
					<div class="input-group">
						<div class="input-group-text">Search</div>
						<input name="s" value="<?php echo esc_attr( wp_unslash( $_REQUEST['s'] ?? '' ) ); ?>" type="text" class="form-control" id="autoSizingInputGroup" placeholder="Type a name...">
					</div>
				</div>
				<div class="col-auto">
					<label class="visually-hidden" for="autoSizingSelect">Preference</label>
					<select name="pokemon_type" class="form-select" id="autoSizingSelect">
						<option value="" selected>Pokemon Type...</option>
						<option value="grass">Grass</option>
						<option value="ice">Ice</option>
					</select>
				</div>
			</div>
		</div>
		<div class="col-auto ml-auto d-flex">
			<button name="submit" type="submit" class="btn btn-primary text-white flex-fill">Filter</button>
		</div>
	</div>
	</div>


	<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
	<?php if ( have_posts() ) : ?>
		<?php
		while ( have_posts() ) :
			the_post();
			?>

		<div class="col d-flex" style="<?php echo esc_attr( Pokerfever::override_card_colors( get_post_meta( get_the_ID(), 'pokemon_primary_color', true ), get_post_meta( get_the_ID(), 'pokemon_secondary_color', true ) ) ); ?>">
			<a href="<?php the_permalink(); ?>" class="card border flex-fill text-decoration-none text-dark" style="background: var(--pokemon-linear-gradient)">
				<div class="row g-0">
					<div class="col-8 my-auto">
						<div class="card-body d-flex gap-2 flex-column justify-content-between">
						<span class="h5 m-0 card-title"><?php the_title(); ?></span>
						
						<small class="text-body-secondary text-muted">
								<?php if ( $pokedex_number = get_post_meta( get_the_ID(), 'pokemon_pokedex_entry_number', true ) ) : ?>
									#<?php echo esc_html( str_pad( $pokedex_number, 3, '0', STR_PAD_LEFT ) ); ?>
								<?php endif; ?>

								<?php if ( $pokedex_game_name = get_post_meta( get_the_ID(), 'pokemon_pokedex_game_name', true ) ) : ?>
									in <?php echo esc_html( ( $pokedex_game_name ) ); ?>
								<?php endif; ?>
							</small>
							
							<?php
								$types = get_the_terms( get_the_ID(), 'pokemon_type' );
							?>
							<?php
							if ( $types ) :
								?>
								<div>
								<?php
								foreach ( $types as $type ) :
									?>
										<span class="badge badge-lg rounded-pill bg-primary">
											<?php echo esc_html( $type->name ); ?>
										</span>
								<?php endforeach; ?>
								</div>
							<?php endif; ?>
						</div>
					</div>
					<div class="col-4 p-2 d-flex align-items-center">
						<div>
								<?php the_post_thumbnail( 'medium', array( 'class' => 'img-fluid' ) ); ?>
						</div>
					</div>
				</div>
			</a>
		</div>
			
				<?php endwhile; ?>
			<?php else : ?>
				No results found.
				<?php endif; ?>
</div>

<div class="mt-4">

		<ul class="pagination justify-content-center">
		<?php

		collect( $pagination )->each(
			function( $link ) {
				$link = str_replace( 'page-numbers', 'page-link', $link );
				$link = str_replace( 'current', 'active', $link );
				if ( strpos( $link, 'span' ) !== false && strpos( $link, 'active' ) === false ) {
					$link = str_replace( 'page-link', 'page-link disabled', $link );
				}

				echo "<li class=\"page-item\">$link</li>";
			}
		);

		?>
		</ul>

	</div>

	</form>

</div>

<?php get_footer(); ?>
