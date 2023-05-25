<?php

test(
	'Test that the action for loading our theme is triggered',
	function () {
		expect( did_action( 'pokefever_loaded' ) )->toBeGreaterThan( 0 );
	}
);
