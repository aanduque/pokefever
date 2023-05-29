<?php

namespace Pokefever\Features\Extra;

use Pokefever\Contracts\Extra_Feature;
use Pokefever\Pokefever;
use Pokefever\Providers\Digimon as Digimon_Provider;

class Digimon implements Extra_Feature {

	public function name(): string {

		return 'digimon';

	}

	public function register( Pokefever $app ): void {

		/**
		 * Registers the Digimon monster provider.
		 */
		$app->register_provider( 'digimon', new Digimon_Provider() );

	}

	public function boot( Pokefever $app ): void {

	}

}
