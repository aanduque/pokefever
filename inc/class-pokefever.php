<?php

namespace Pokefever;

use Illuminate\Validation\Factory;
use Illuminate\Container\Container;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Pokefever\Contracts\Monster_Provider;
use Pokefever\Providers\Pokemon;

final class Pokefever extends Container {

	protected function __construct() {
		// Sets up the validator instance.
		$this->instance(
			'validator',
			new Factory(
				new Translator( new ArrayLoader(), 'en' ),
				$this,
			)
		);

		// Auto-injects the container inside the container.
		$this->instance( 'app', $this );

		// Boot the container.
		$this->boot();

		self::init();
	}

	public function boot() {

		$this->load_features();

		$this->boot_providers();

		$this->boot_features();

	}

	protected function load_features() {

		collect( $this->tagged( 'feature' ) )->each(
			function( $feature ) {
				$feature->register( $this );
			}
		);

	}

	protected function boot_features() {

		collect( $this->tagged( 'feature' ) )
			->filter(
				function( $feature ) {
					// Required features are always loaded.
					if ( $feature instanceof Contracts\Feature ) {
						return true;
					}

					return get_option( 'pokefever_feature_' . $feature->name(), true );
				}
			)
			->each(
				function( $feature ) {
					$feature->boot( $this );
				}
			);

	}

	protected function boot_providers() {

		foreach ( $this->get_providers() as $provider ) {
			// Registers the post types for this provider.
			list($post_type_slug, $post_type_args) = $provider->post_type();
			register_post_type( $post_type_slug, $post_type_args );

			// Then, we register the taxonomies for this provider.
			foreach ( $provider->taxonomies() as $taxonomy_slug => $taxonomy ) {
				list($taxonomy_post_types, $taxonomy_args) = $taxonomy;
				register_taxonomy( $taxonomy_slug, $taxonomy_post_types, $taxonomy_args );
			}
		}

		/**
		 * Sets the default provider.
		 *
		 * This will allow us to use dependency injection to get the current provider.
		 * Inside the features and as long as the providers abide to the same contract,
		 * it should all continue to work.
		 *
		 * Doing things this way allows us to easily add new providers in the future.
		 */
		$this->alias(
			$this->get_current_provider(),
			Monster_Provider::class
		);

	}

	public function get_default_provider() {
		return apply_filters( 'pokefever/providers/default', Pokemon::class, $this );
	}

	public function get_current_provider() {
		// get the current provider key saved on the cookie, if the user is not logged in or in the user meta, if the user is logged in.
		$current_provider_key = get_current_user_id() ? get_user_meta( get_current_user_id(), 'pokefever_current_provider', true ) : ( $_COOKIE['pokefever_current_provider'] ?? $this->get_default_provider() );

		return apply_filters( 'pokefever/providers/current', $current_provider_key, $this );
	}

	/**
	 * Allows us to register and tag instances in the container.
	 *
	 * @param string $key The container key to register the instance as.
	 * @param mixed  $value The instance to register.
	 * @param string $tag The tag to register the instance with.
	 * @return $this
	 */
	protected function register_as( string $key, $value, string $tag ) {
		$this->bind( $key, $value );

		if ( $value !== $key ) {
			$this->alias( $value, $key );
		}

		$this->tag( $key, $tag );

		return $this;
	}

	/**
	 * Registers a new feature in the container.
	 *
	 * @param string|\Closure|null $feature The feature instance.
	 * @return $this
	 */
	public function register_feature( $feature ) {
		return $this->register_as( $feature, $feature, 'feature' );
	}

	/**
	 * Registers multiple features in the container.
	 *
	 * @param array<Feature|Extra_Feature> $features The features to register.
	 * @return $this
	 */
	public function register_features( array $features ) {
		foreach ( $features as $feature ) {
			$this->register_feature( $feature );
		}
		return $this;
	}

	/**
	 * Registers a new monster provider in the container.
	 *
	 * @param string $provider_name  The monster provider name. This is used as the container key.
	 * @param mixed  $provider The provider instance.
	 * @return $this
	 */
	public function register_provider( string $provider_name, $provider ) {
		return $this->register_as( $provider_name, $provider, 'provider' );
	}

	public function get_providers() {
		return $this->tagged( 'provider' );
	}

	public static function init() {

		add_filter(
			'wp_get_object_terms_args',
			function( $args ) {
				$args['orderby'] = 'term_order';
				return $args;
			}
		);

		do_action( 'pokefever_loaded' );
	}


}
