<?php

use Pokefever\Pokefever;

use function Pokefever\get_monster_colors_for_card;

 get_header(); ?>

<?php get_template_part( 'template-parts/archive-hero' ); ?>


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
						<input name="s" value="<?php echo esc_attr( $_REQUEST['s'] ?? '' ); ?>" type="text" class="form-control" id="autoSizingInputGroup" placeholder="Type a name...">
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

		<div class="col d-flex" style="<?php echo esc_attr( get_monster_colors_for_card( get_the_ID() ) ); ?>">
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

	<?php get_template_part( 'template-parts/archive-pagination' ); ?>

</div>

	</form>

</div>

<?php get_footer(); ?>