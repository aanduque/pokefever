<?php get_header(); ?>

<div class="container d-flex flex-column flex-grow-1" id="pokemon">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<div class="row d-flex align-content-center flex-grow-1 mb-5">
			<div class="col-md-6 order-md-2 mb-n5">
				<!-- Display the featured image (Photo of the Pokémon) -->
				<?php if ( has_post_thumbnail() ) : ?>
					<style>
						.pokemon-image {
							position: relative;
							text-align: center;
							z-index: 1000;
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
							z-index: 1000;
						}

						.pokemon-image img {
							position: relative;
							z-index: 1010;
						}
					</style>
					<div class="pokemon-image">
						<img src="<?php the_post_thumbnail_url(); ?>" class="img-fluid">
					</div>
				<?php endif; ?>

			</div>

			<div class="col-md-6 d-flex flex-column justify-content-center">

				<div class="card">
					<div class="card-header p-4 pb-2">

						<!-- Pokémon name (post title) -->
						<div class="mt-3 mt-lg-0 d-flex flex-column flex-lg-row gap-lg-2 align-items-lg-center">
							<h1>
								<?php the_title(); ?>
							</h1>
							<small class="text-body-secondary text-muted">
								<?php if ( $pokedex_number = get_post_meta( get_the_ID(), 'pokemon_pokedex_entry_number', true ) ) : ?>
									#<?php echo esc_html( str_pad( $pokedex_number, 3, '0', STR_PAD_LEFT ) ); ?>
								<?php endif; ?>

								<?php if ( $pokedex_game_name = get_post_meta( get_the_ID(), 'pokemon_pokedex_game_name', true ) ) : ?>
									in <?php echo esc_html( ( $pokedex_game_name ) ); ?>
								<?php endif; ?>
							</small>
						</div>

						<!-- Pokémon description (post content) -->
						<div class="pt-2 pt-lg-0"><?php the_content(); ?></div>
						
						<nav class="navbar navbar-expand">
							<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#cardTabOptions" aria-controls="cardTabOptions" aria-expanded="false" aria-label="Toggle navigation">
								<span class="navbar-toggler-icon"></span>
							</button>
							<div id="cardTabOptions" class="collapse navbar-collapse mb-n2">
								<ul class="nav nav-tabs card-header-tabs">

								  <li class="nav-item" role="presentation">
										<button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#basic-info-tab-content" type="button" role="tab" aria-controls="basic-info-tab-content" aria-selected="true">
											<?php esc_html_e( 'Basic Information', 'pokefever' ); ?>
										</button>
								  </li>

								  <li class="nav-item" role="presentation">
										<button class="nav-link" id="move-tab" data-bs-toggle="tab" data-bs-target="#moves-tab-content" type="button" role="tab" aria-controls="moves-tab-content" aria-selected="true">
											<?php esc_html_e( 'Moves', 'pokefever' ); ?>
										</button>
								  </li>

								</ul>
							</div>
						</nav>
					</div>
					
					<div class="tab-content">
						<div id="basic-info-tab-content" class="tab-pane fade show active">
							<div class="card-body p-4">
								<!-- <h5 class="card-title">Special title treatment</h5> -->
								<p class="card-text">
									<?php printf( __( 'Basic information about %s.' ), $post->post_title ); ?>
								</p>
							</div>
							<ul class="list-group list-group-flush border-top">
								<li class="list-group-item px-4">
									<div class="row">
										<div class="col-sm-4"><strong><?php _e( 'Type', 'pokefever' ); ?></strong></div>
										<div class="col-sm-8">
											<!-- Pokémon types (primary and secondary) -->
											<?php
											$types = get_the_terms( get_the_ID(), 'pokemon_type' );
											if ( $types ) :
												?>
													<div>
														<?php
														foreach ( $types as $type ) :
															?>
															<span class="badge badge-lg rounded-pill bg-primary"><?php echo esc_html( $type->name ); ?></span>
														<?php endforeach; ?>
													</div>
											<?php endif; ?>
										</div>
									</div>
								</li>
								<li class="list-group-item px-4">
									<div class="row">
										<div class="col-sm-4"><strong><?php _e( 'Old Pokedex No.', 'pokefever' ); ?></strong></div>
										<div class="col-sm-8">
											<!-- Button to load the Pokedex number in the oldest version of the game -->
											<div id="oldest-pokedex-number" style="display: none">
												<?php esc_html_e( 'Loading...', 'pokefever' ); ?>
											</div>
											<a href="#" id="load-oldest-pokedex-number" class="">
												<?php esc_html_e( 'Load', 'pokefever' ); ?>
											</a>
										</div>
									</div>
								</li>
								<li class="list-group-item px-4">
									<div class="row">
										<div class="col-sm-4"><strong><?php _e( 'Weight', 'pokefever' ); ?></strong></div>
										<div class="col-sm-8"><?php echo get_post_meta( $post->ID, 'pokemon_weight', true ); ?> kg</div>
									</div>
								</li>
								<li class="list-group-item px-4">
									<div class="row">
										<div class="col-sm-4"><strong><?php _e( 'Height', 'pokefever' ); ?></strong></div>
										<div class="col-sm-8"><?php echo get_post_meta( $post->ID, 'pokemon_height', true ); ?> cm</div>
									</div>
								</li>
								<li class="list-group-item px-4 fs-6 text-center text-muted">
									<small>
										<?php echo esc_html( sprintf( __( 'Generated on %s', 'pokefever' ), get_the_date( 'Y-m-d \a\t H:i' ) ) ); ?>
									</small>
								</li>
							</ul>
						</div>

						<div id="moves-tab-content" class="tab-pane fade">
							<div class="card-body p-4">
								<!-- <h5 class="card-title">Special title treatment</h5> -->
								<p class="card-text">
									<?php printf( __( 'List of moves for %s.' ), $post->post_title ); ?>
								</p>
							</div>
							<ul class="list-group list-group-flush border-top">

							<?php

							// Get all moves for this Pokémon
							$moves = get_the_terms( get_the_ID(), 'pokemon_move' );

							if ( $moves ) :
								foreach ( $moves as $move ) :
									?>

								<li class="list-group-item px-4">
									<div class="row">
										<div class="col-lg-4"><strong><?php echo esc_html( $move->name ); ?></strong></div>
										<div class="col-lg-8"><?php echo esc_html( $move->description ); ?></div>
									</div>
								</li>

								<?php endforeach; ?>
								
								<?php else : ?>
									
									<li class="list-group-item px-4 text-center">
										<?php _e( 'No moves found.', 'pokefever' ); ?>
									</li>
							<?php endif; ?>
							
								<li class="list-group-item px-4 fs-6 text-center text-muted">
									<small>
										<?php echo esc_html( sprintf( __( 'Generated on %s', 'pokefever' ), get_the_date( 'Y-m-d \a\t H:i' ) ) ); ?>
									</small>
								</li>
							</ul>
						</div>
					</div>
				</div>

			</div>
		</div>
	<?php endwhile; ?>
</div>

<?php get_footer(); ?>
