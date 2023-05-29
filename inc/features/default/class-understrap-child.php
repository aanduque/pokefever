<?php

namespace Pokefever\Features\Required;

use Pokefever\Contracts\Feature;
use Pokefever\Pokefever;

class Understrap_Child implements Feature {

	public function register( Pokefever $app ): void { }

	public function boot( Pokefever $app ): void {

		add_action( 'wp_enqueue_scripts', array( $this, 'understrap_remove_scripts' ), 20 );

		add_action( 'wp_enqueue_scripts', array( $this, 'theme_enqueue_styles' ) );

		add_action( 'after_setup_theme', array( $this, 'add_child_theme_textdomain' ) );

		add_filter( 'theme_mod_understrap_bootstrap_version', array( $this, 'understrap_default_bootstrap_version' ), 20 );

		add_action( 'customize_controls_enqueue_scripts', array( $this, 'understrap_child_customize_controls_js' ) );

	}

	/**
	 * Removes the parent themes stylesheet and scripts from inc/enqueue.php
	 */
	public function understrap_remove_scripts() {
		wp_dequeue_style( 'understrap-styles' );
		wp_deregister_style( 'understrap-styles' );

		wp_dequeue_script( 'understrap-scripts' );
		wp_deregister_script( 'understrap-scripts' );
	}

	/**
	 * Enqueue our stylesheet and javascript file
	 */
	public function theme_enqueue_styles() {
		// Get the theme data .
		$the_theme     = wp_get_theme();
		$theme_version = $the_theme->get( 'Version' );

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		// Grab asset urls .
		$theme_styles  = "/css/child-theme{$suffix}.css";
		$theme_scripts = "/js/child-theme{$suffix}.js";

		$css_version = $theme_version . '.' . filemtime( get_stylesheet_directory() . $theme_styles );

		wp_enqueue_style( 'child-understrap-styles', get_stylesheet_directory_uri() . $theme_styles, array(), $css_version );
		wp_enqueue_script( 'jquery' );

		$js_version = $theme_version . '.' . filemtime( get_stylesheet_directory() . $theme_scripts );

		wp_register_script( 'child-understrap-scripts', get_stylesheet_directory_uri() . $theme_scripts, array(), $js_version, true );

		wp_localize_script(
			'child-understrap-scripts',
			'pokefever',
			array(
				'ajax_url'        => admin_url( 'admin-ajax.php' ),
				'nonce'           => wp_create_nonce( 'pokefever-nonce' ),
				'current_post_id' => get_the_ID(),
				'messages'        => array(
					'403' => __( 'Failed to fetch data.', 'pokerfever' ),
					'404' => __( 'Old Pokedex entry not found.', 'pokerfever' ),
					'500' => __( 'Something went wrong.', 'pokerfever' ),
				),
			)
		);

		wp_enqueue_script( 'child-understrap-scripts' );

		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}
	}
	/**
	 * Load the child theme's text domain
	 */
	public function add_child_theme_textdomain() {
		load_child_theme_textdomain( 'understrap-child', get_stylesheet_directory() . '/languages' );
	}
	/**
	 * Overrides the theme_mod to default to Bootstrap 5
	 *
	 * This function uses the `theme_mod_{$name}` hook and
	 * can be duplicated to override other theme settings.
	 *
	 * @return string
	 */
	public function understrap_default_bootstrap_version() {
		return 'bootstrap5';
	}

	/**
	 * Loads javascript for showing customizer warning dialog.
	 */
	public function understrap_child_customize_controls_js() {
		wp_enqueue_script(
			'understrap_child_customizer',
			get_stylesheet_directory_uri() . '/js/customizer-controls.js',
			array( 'customize-preview' ),
			'20130508',
			true
		);
	}

}
