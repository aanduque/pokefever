<?php
/**
 * The Pokefever theme's bootstrap file.
 *
 * This file is used to kick off the theme's functionality.
 * It registers the required features of the challenge, as well as
 * any optional features that I wanted to implement.
 *
 * Required features are located in inc/features/required.
 * Optional features are located in inc/features/extra.
 *
 * Required features are always loaded.
 * Optional features can be disabled inside the admin panel.
 *
 * @package Pokefever
 */

use Pokefever\Features\Extra\Digimon;
use Pokefever\Features\Required\Frontend_Customizations;
use Pokefever\Features\Required\Generate_Endpoint;
use Pokefever\Features\Required\Pokemon;
use Pokefever\Features\Required\Random_Endpoint;
use Pokefever\Features\Required\Understrap_Child;
use function Pokefever\container as app;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Require the Composer autoloader.
 */
require_once __DIR__ . '/vendor/autoload.php';

/**
 * Register the features described by the challenge.
 *
 * In order to make sure our code is manageable in the long run, I decided to
 * use the Illuminate Container to manage the application's dependencies.
 *
 * This allows us to register features and providers in a way that is easy to
 * maintain and extend, and even replace.
 *
 * I also decided to use the Illuminate Validation package to validate the fields
 * passed to the Monster model class.
 *
 * @see inc/helpers.php
 *
 * @link https://packagist.org/packages/illuminate/container
 * @link https://packagist.org/packages/illuminate/validation
 */
app()->register_features(
	array(

		/**
		 * Adds the Pokemon API as the default monster provider.
		 *
		 * This project was built in a way that multiple monster providers can be used.
		 * Below, I added the Digimon API as an example of how to add more monster providers.
		 *
		 * This covers itens 1 and 2 of the challenge.
		 *
		 * @see inc/features/required/class-pokemon.php
		 */
		Pokemon::class,

		/**
		 * Adds the /generate endpoint, with permission checking.
		 *
		 * This covers item 7 of the challenge.
		 *
		 * @see inc/features/required/class-generate-endpoint.php
		 */
		Generate_Endpoint::class,

		/**
		* Adds the /random endpoint.
		*
		* This covers item 6 of the challenge.
		*
		* @see inc/features/required/class-random-endpoint.php
		*/
		Random_Endpoint::class,

		/**
		 * Adds the frontend customizations.
		 *
		 * This aids in covering item 4 of the challenge.
		 *
		 * @see inc/features/required/class-frontend-customizations.php
		 */
		Frontend_Customizations::class, // Adds the frontend customizations.
	)
);

/**
 * Register the challenge's optional features that I wanted to implement,
 * as well as extra features I added to make the app more interesting.
 */
app()->register_features(
	array(

		/**
		 * Adds the Digimon API as an example of how to add more monster providers.
		 *
		 * This starts the process of demonstrating how to do what was described
		 * in item 9 of the challenge.
		 *
		 * @see inc/features/extra/class-digimon.php
		 */
		Digimon::class,

	)
);

/**
 * Add other features that I used to consolidate logic that would otherwise
 * pollute the functions.php file.
 */
app()->register_features(
	array(

		/**
		 * The understrap child theme had some logic that I wanted to consolidate
		 * in a separate class, outside of the functions.php file, so we could have
		 * a cleaner file.
		 *
		 * That logic was moved into the Feature paradigm I created for this project,
		 * in the Understrap_Child class.
		 *
		 * @see inc/features/required/class-understrap-child.php
		 */
		Understrap_Child::class,

	)
);

/**
 * Initialize the theme main class.
 */
app()->boot();
