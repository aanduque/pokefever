<?php

namespace Pokefever\Models;

use function pf\container as app;

class Monster {

	protected function validation_rules() {
		return apply_filters(
				'pf_monster_validation_rules',
			array(
				'name'        => 'required',
				'description' => 'required',
			)
		);
	}

	public function __construct( array $data ) {
		$validation = app()->get( 'validator' )->validate( $data, $this->validation_rules() );

		// if ( $validation->fails() ) {
		// var_dump( $validation->errors()->all() );
		// }
	}

}
