<?php

use function pf\container as app;

it(
	'can register the default provider',
	function () {
		$provider = app()->get( app()->get_default_provider() );
		expect( $provider )->toBeInstanceOf( \Pokefever\Providers\Pokemon::class );
	}
);
