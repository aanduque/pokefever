<?php

namespace Pokefever\Models;

use Exception;
use Pokefever\Util;

use function Pokefever\container as app;

/**
 * Class Monster.
 *
 * Represents a generic monster, which can be created by different providers.
 *
 * @package Pokefever\Models
 */
class Monster {

	/**
	 * The ID of the monster.
	 *
	 * This is only set after a successful save.
	 *
	 * @var int|null
	 */
	protected $post_id = null;

	/**
	 * The attributes of the monster.
	 *
	 * @var array
	 */
	protected $attributes = array();

	/**
	 * The list of validation rules for the received attributes.
	 *
	 * This is passed down to our Illuminate Validator instance.
	 *
	 * @link https://laravel.com/docs/10.x/validation
	 * @return array
	 */
	protected function validation_rules() {

		$rules = array(
			'api_id'      => 'required|int',
			'name'        => 'required|min:3',
			'slug'        => 'required|alpha_dash',
			'type'        => 'required|alpha_dash',
			'description' => 'required|min:5',
			'image'       => 'url',
			'meta'        => 'array',
			'taxonomies'  => 'array',
		);

		return apply_filters(
			'pf_monster_validation_rules',
			$rules
		);

	}

	/**
	 * Validates the received data and sets the attributes for this monster.
	 *
	 * @param array $data The data to validate and set.
	 * @return void
	 */
	public function __construct( array $data ) {

		/**
		 * Get the validator instance from the container.
		 */
		$validator = app()->get( 'validator' );

		/**
		 * Validate the data received using the validator.
		 *
		 * @link https://laravel.com/docs/10.x/validation
		 */
		$validation = $validator->make(
			$data,
			$this->validation_rules(),
			array(
				// translators: %s is the name of the field.
				'required' => sprintf( __( 'The Monster field "%s" is required', 'pokefever' ), ':attribute' ),
			)
		);

		/**
		 * If the validation fails, we get a wp_die() with the error message.
		 */
		if ( $validation->fails() ) {

			$error_message = collect( $validation->errors()->all() )->join( '<br>' );

			wp_die(
				$error_message, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				__( 'Failed to generate monster', 'pokefever' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			);

		}

		/**
		 * Set the API ID as a meta field.
		 *
		 * This is required to make sure we ways save the api_id as a meta field,
		 * regardless of either or not it was declared by the provider.
		 */
		$this->attributes['meta']['api_id'] = $data['api_id'];

		/**
		 * The data is valid. Attach it to the attributes property.
		 */
		$this->attributes = $validation->validated();

	}

	/**
	 * The default status to use for the Monster post when save is called.
	 *
	 * @return string
	 */
	protected function default_status() {

		return apply_filters( 'pf_monster_default_status', 'publish', $this );

	}

	/**
	 * Saves the monster to the database as a post.
	 *
	 * @throws Exception If the post could not be created.
	 *
	 * @return int The ID of the post.
	 */
	public function save() {

		$monster = array(
			'post_title'   => $this->attributes['name'],
			'post_name'    => $this->attributes['slug'],
			'post_content' => $this->attributes['description'],
			'post_type'    => $this->attributes['type'],
			'post_status'  => $this->default_status(),
			'meta_input'   => collect( $this->attributes['meta'] )->mapWithKeys(
				function( $value, $key ) {
					return array( "{$this->attributes['type']}_{$key}" => $value );
				}
			)->toArray(),
		);

		/**
		 * Create the Pokemon post.
		 */
		$pokemon_post = wp_insert_post( $monster );

		/**
		 * If the Pokemon post could not be created, throw an exception.
		 *
		 * This will be caught by the endpoint and the error message will be displayed.
		 */
		if ( is_wp_error( $pokemon_post ) ) {
			throw new Exception( $pokemon_post->get_error_message() );
		}

		/**
		 * Set the post ID.
		 */
		$this->post_id = $pokemon_post;

		/**
		 * Set the Monster's taxonomies.
		 *
		 * Something weird was going on when I tried to set the taxonomies
		 * using the `tax_input` parameter. I'm not sure why, but it was not working.
		 * It might even be some sort of bug in WordPress Core.
		 *
		 * So, I'm using the `wp_set_post_terms()` function manually here for each item.
		 * In terms  of performance that doesn't make a difference since this is exactly
		 * What the `tax_input` parameter is used to do internally anyway.
		 *
		 * @link https://developer.wordpress.org/reference/functions/wp_insert_post/
		 * @link https://developer.wordpress.org/reference/functions/wp_set_post_terms/
		 */
		collect( $this->attributes['taxonomies'] )->each(
			function( $terms, $taxonomy ) use ( $pokemon_post ) {
				wp_set_post_terms( $pokemon_post, $terms, $taxonomy );
			}
		);

		/**
		 * Saves the monster image as a featured image.
		 */
		if ( $this->attributes['image'] ) {

			try {

				$attachment_id = Util::download_and_set_image_as_thumbnail(
					$this->attributes['image'],
					$this->post_id,
					$this->attributes['name']
				);

				/**
				 * Now that we have an attachment ID, we can get the path to the image
				 * and use the image to calculate the primary and secondary colors for the monster.
				 *
				 * Note: We need actual access to the file, not just the URL. Some offloading plugins
				 * might change the path using the filters available, so we need to make sure we get the
				 * unfiltered version.
				 */
				$image_path = get_attached_file( $attachment_id, true );

				/**
				 * The names of the colors we want to extract from the image.
				 * At the present time, our theme only supports two colors: primary and secondary.
				 */
				$color_names = array( 'primary', 'secondary' );

				/**
				 * Calculate the primary and secondary colors for the monster.
				 */
				$colors = Util::extract_colors_from_image( $image_path, $color_names );

				/**
				 * Set the primary and secondary colors as meta fields.
				 */
				collect( $color_names )->each(
					function( $color_name ) use ( $colors ) {
						if ( isset( $colors[ $color_name ] ) ) {
							update_post_meta( $this->post_id, "{$color_name}_color", $colors[ $color_name ] );
						}
					}
				);

			} catch ( \Throwable $th ) {

				/**
				 * Something went wrong while trying to download and set the image as a thumbnail.
				 *
				 * In a actual production environment, we would want to log this error somewhere.
				 * For this demo, we'll just log it to the PHP error log, and ignore the phpcs warning.
				 */
				error_log( sprintf( 'Unable to download and create image for monster %d. Reason: %s', $this->post_id, $th->getMessage() ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log

			}
		}

		return $this->post_id;

	}

}
