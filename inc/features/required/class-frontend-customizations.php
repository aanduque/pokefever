<?php
/**
 * Frontend Customizations
 *
 * @package Pokefever
 */

namespace Pokefever\Features\Required;

use Illuminate\Support\Collection;
use Pokefever\Contracts\Feature;
use Pokefever\Contracts\Monster_Provider;
use Pokefever\Pokefever;
use Pokefever\Util;

use function Pokefever\container as app;
use function Pokefever\get_registered_post_types_slugs;

/**
 * Class Frontend_Customizations
 *
 * @package Pokefever\Features\Required
 */
class Frontend_Customizations implements Feature {

	/**
	 * The custom CSS rules to be injected into the page.
	 *
	 * @var null|Collection
	 */
	protected ?Collection $custom_css_rules;

	/**
	 * Frontend_Customizations constructor.
	 *
	 * @return void
	 */
	public function __construct() {

		/**
		 * Creates an empty collection just so we can append new rules using the add method.
		 *
		 * Later we join all the rules using the PHP_EOL constant, to combine the rules into a
		 * single string, which can then be passed down to the wp_add_inline_style() function.
		 */
		$this->custom_css_rules = collect();

	}

	/**
	 * Registers the feature with the container.
	 *
	 * @param Pokefever $app The application container.
	 * @return void
	 */
	public function register( Pokefever $app ): void {}

	/**
	 * Boot the feature.
	 *
	 * @param Pokefever $app The application container.
	 * @return void
	 */
	public function boot( Pokefever $app ): void {

		/**
		 * We add the customizations to the page using the wp_enqueue_scripts hook.
		 *
		 * We use a priority of 15 here because we register the default style - which we need to
		 * be registered in order to add inline styles to it - with a priority of 10.
		 */
		add_action( 'wp_enqueue_scripts', fn() => app()->call( array( $this, 'enqueue_inline_styles' ) ), 15 );

	}

	/**
	 * Injects dynamic CSS rules into the page.
	 *
	 * We inject the customizations based on the provider being used, as well as the current
	 * monster being displayed on screen.
	 *
	 * We add inline css using the default WordPress function wp_add_inline_style(), which
	 * guarantees that caching plugins can deal with it properly.
	 *
	 * @link https://developer.wordpress.org/reference/functions/wp_add_inline_style/
	 *
	 * @return void
	 */
	public function enqueue_inline_styles() {

		/**
		 * We create an array of all the customizations we want to apply.
		 *
		 * Each customization is a callable that can ask for different dependencies as parameters
		 * as they get run through our container.
		 */
		$customizations = array(

			/**
			 * Each monster provider can add its own hero image.
			 *
			 * This customization checks if the provider has a hero image, and if that's the case,
			 * it appends a new rule to the collection.
			 */
			array( $this, 'maybe_add_hero_image' ),

			/**
			 * If we are on a monster single, we override the primary and secondary colors of the
			 * theme with the ones defined in the monster saved.
			 */
			array( $this, 'maybe_override_single_colors' ),
		);

		/**
		 * Loops through all the customizations and calls them.
		 *
		 * We use the app()->call() function to call the customizations, which allows us to
		 * inject dependencies into the function dynamically.
		 */
		collect( $customizations )->each( fn( $customization ) => app()->call( $customization ) );

		/**
		 * Joins all the rules together and appends it to the page.
		 */
		wp_add_inline_style( 'pokefever-styles', $this->custom_css_rules->join( PHP_EOL ) );

	}

	/**
	 * Checks if the providers adds a cover image.
	 * If that's the case, we append a new rule to the collection.
	 *
	 * @param Monster_Provider $provider The current monster provider being used.
	 * @return void
	 */
	public function maybe_add_hero_image( Monster_Provider $provider ) {

		$cover_image = $provider->cover_image();

		if ( ! $cover_image ) {
			return;
		}

		$this->custom_css_rules->add(
			'#hero {
				--pokefever-hero-bg: url(' . esc_attr( $cover_image ) . ');
			}'
		);

	}

	/**
	 * Overrides the primary and secondary colors of the theme, if we are inside the monster single.
	 *
	 * @return void
	 */
	public function maybe_override_single_colors() {

		/**
		 * The current post.
		 *
		 * @var \WP_Post|null
		 */
		global $post;

		$current_monster = $post;

		/**
		 * If the current post is not a monster, or if we're not on a singular page, we bail.
		 */
		if ( ! $current_monster || ! is_singular( get_registered_post_types_slugs() ) ) {
			return;
		}

		$primary   = get_post_meta( $current_monster->ID, 'primary_color', true );
		$secondary = get_post_meta( $current_monster->ID, 'secondary_color', true );

		$primary_rgb   = Util::hex_to_rgb( $primary );
		$secondary_rgb = Util::hex_to_rgb( $secondary );

		$this->custom_css_rules->add(
			":root {
				--bs-primary: $primary;
				--bs-secondary: $secondary;
				--bs-primary-rgb: $primary_rgb->r,$primary_rgb->g,$primary_rgb->b;
				--bs-secondary-rgb: $secondary_rgb->r,$secondary_rgb->g,$secondary_rgb->b;
				--bs-link-color: $primary;
				--bs-bg-opacity: 1;
				--pokemon-linear-gradient: linear-gradient(0deg, rgba(var(--bs-secondary-rgb),0.75) 0%, rgba(var(--bs-primary-rgb),1) 90%);
			}"
		);

	}

}
