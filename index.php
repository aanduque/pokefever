<?php

use function pf\get_registered_post_types;
use function pf\container as app;

 get_header(); ?>

<?php get_template_part( 'template-parts/homepage-hero' ); ?>

<div class="container" id="list">

  <div class="row row-cols-1 row-cols-md-2 row-cols-lg-2 g-4">

    <?php foreach ( get_registered_post_types() as $registered_post_type ) : list( $slug, $args ) = $registered_post_type; ?>
      
      <div class="col d-flex">
      
        <a href="<?php echo esc_attr(get_post_type_archive_link($slug)); ?>" class="card border flex-fill text-decoration-none text-dark">
          <img src="<?php echo esc_attr( app()->get($slug)->cover_image() ); ?>" class="card-img opacity-25" alt="<?php echo esc_attr( $args['labels']['name'] ?? __('Monster', 'pokefever') ); ?>">
          <div class="card-img-overlay d-flex">
            <div class="card-body px-lg-5 my-auto">
              <h2 class="h1 card-title"><?php echo esc_html( $args['labels']['name'] ?? __('Monster', 'pokefever') ); ?></h2>
              <p class="card-text fs-5"><?php echo esc_html( $args['description'] ?? __('No description found.', 'pokefever') ); ?></p>
            </div>
          </div>
        </a>
          
      </div>

    <?php endforeach; ?>

  </div>

</div>

<?php
get_footer();
