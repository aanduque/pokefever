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
 * Register the features required by the challenge.
 */
app()->register_features(
	array(
		Pokemon::class, // Adds the main functionality regarding the Pokemon API.
		Generate_Endpoint::class, // Adds the /generate endpoint.
		Random_Endpoint::class, // Adds the /random endpoint.
		Frontend_Customizations::class, // Adds the frontend customizations.
	)
);

/**
 * Register the challenge's optional features that I wanted to implement,
 * as well as extra features I added to make the app more interesting.
 */
app()->register_features(
	array(
		Digimon::class, // Adds the Digimon API as an example of how to add more monster providers.
	)
);

/**
 * Add other features that I used to consolidate logic that would otherwise
 * pollute the functions.php file.
 */
app()->register_features(
	array(
		Understrap_Child::class, // Adds the child theme logic that came with the Understrap theme.
	)
);

/**
 * Initialize the theme main class.
 */
app()->boot();
