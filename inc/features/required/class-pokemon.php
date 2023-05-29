<?php

namespace Pokefever\Features\Required;

use Pokefever\Contracts\Feature;
use Pokefever\Pokefever;
use Pokefever\Providers\Pokemon as Pokemon_Provider;

class Pokemon implements Feature {

	public function register( Pokefever $app ): void {

		/**
		 * Registers the Pokémon monster provider.
		 */
		$app->register_provider( 'pokemon', new Pokemon_Provider() );

	}

	public function boot( Pokefever $app ): void {

	}

}
