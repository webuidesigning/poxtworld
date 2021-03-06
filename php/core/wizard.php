<?php
/*             __        ___                  _ 
  _ ____      _\ \      / (_)______ _ _ __ __| |
 | '_ \ \ /\ / /\ \ /\ / /| |_  / _` | '__/ _` |
 | |_) \ V  V /  \ V  V / | |/ / (_| | | | (_| |
 | .__/ \_/\_/    \_/\_/  |_/___\__,_|_|  \__,_|
 |_|                                            
//////// ------ POSTWORLD WIZARD ------ ////////*/



function pw_get_wizard_status( $vars ){
	/*
		$vars = array(
			'user_id'		=>	// integer
			'wizard_name'	=>	// string
			'format'		=>  // string ( A_ARRAY / JSON )
		);
	*/
	extract( $vars );
	$meta_key = 'wizard_status';
	
	// Set Defaults
	if( !isset( $user_id ) ){
		$user_id = get_current_user_id();
		if( $user_id == 0 )
			return array('error' => 'Log in first.');
	}
	if( !isset( $format ) )
		$format = "A_ARRAY";

	// Security
	if( $user_id != get_current_user_id() && !current_user_can( 'edit_users' ) )
		return array('error' => 'No access.');

	// Check if user has a wizard status object
	$wizard_status = get_user_meta( $user_id, $meta_key, true );

	// If value exists
	if( !empty($wizard_status) ){
		// Decode JSON user meta value as A_ARRAY
		$wizard_status = json_decode($wizard_status, true); 

		// Check if the wizard_name is specified
		if( isset($wizard_name) ){
			// Check if the requested wizard_name exists
			if( isset( $wizard_status[$wizard_name] ) )
				$return_data = $wizard_status[$wizard_name];
			else
				$return_data = array();

		} else{
			$return_data = $wizard_status;
		}

		// Format into the specified format and return
		if( $format == "A_ARRAY" )
			return $return_data;
		else if( $format == "JSON" )
			return json_encode( $return_data );

	} else {
		// If not, return empty handed
		return pw_empty_array( $format );	
	}
	
}

function pw_set_wizard_status( $vars ){
	/*
		$vars = array(
			'user_id'		=>	// integer
			'wizard_name'	=>	// string
			'value'			=>  // object/array
			'input_format'	=>	
			'output_format'	=>	
			'format'		=>  // string ( A_ARRAY / JSON )
		);
	*/
	extract( $vars );
	$meta_key = 'wizard_status';

	// Set Defaults
	if( !isset( $user_id ) ){
		$user_id = get_current_user_id();
		if( $user_id == 0 )
			return array('error' => 'Log in first.');
	}
	if( !isset( $wizard_name ) )
		return array('error' => 'No "wizard_name" defined.');
	if( !isset( $value ) )
		return array('error' => 'No "value" defined.');
	if( !isset( $input_format ) )
		$input_format = "A_ARRAY";
	if( !isset( $output_format ) )
		$output_format = "A_ARRAY";

	// Security
	if( $user_id != get_current_user_id() && !current_user_can( 'edit_users' ) )
		return array('error' => 'No access.');

	// Format Validation
	if( $input_format == "A_ARRAY" && !is_array( $value ) )
		return array('error' => 'Expecting "value" to be an Array.');
	if( $input_format == "JSON" && !is_string( $value ) )
		return array('error' => 'Expecting "value" to be a JSON string.');

	// Check if user has a wizard status object
	$wizard_status = get_user_meta( $user_id, $meta_key, true );

	// If value exists, decode it from JSON
	if( !empty($wizard_status) ){
		// Decode JSON user meta value as A_ARRAY
		$wizard_status = json_decode($wizard_status, true); 
	} else {
	// Otherwise, set it as an empty array
		$wizard_status = array();
	}

	// Condition the $value field
	if( $input_format == 'JSON' )
		// Decode JSON $value as
		$value = json_decode( $value, true );


	// If the current incoming status is active
	if( $value['active'] == true ){
		// Deactivate the other wizards so that only one is active at a time
		$wizard_status_deactivated = array();
		foreach( $wizard_status as $name => $status ){
			$status['active'] = false;
			$status['visible'] = false;
			$wizard_status_deactivated[$name] = $status;
		}
		$wizard_status = $wizard_status_deactivated;
	}
	
	// Write in the new value
	$wizard_status[$wizard_name] = $value;

	// Encode it as JSON
	$wizard_status_json = json_encode($wizard_status);

	// Update the Database
	$update_user_meta = update_user_meta( $user_id, $meta_key, $wizard_status_json );

	// Convert into A_ARRAY if requested
	if( $output_format == "JSON" )
		$wizard_status = $wizard_status_json;

	// Return with the current value
	return $wizard_status;

}


function pw_active_wizard( $user_id ){

	// Set Defaults
	if( !isset( $user_id ) )
		$user_id = get_current_user_id();
	if( $user_id == 0 )
		return array( "error" => "No user." );

	$wizard_status = pw_get_wizard_status( array( 'user_id' => $user_id ) );

	// If the wizard is empty, return empty array
	if( empty($wizard_status) )
		return array();

	// If any are active, return the first one found
	foreach( $wizard_status as $name => $status ){
		if( $status['active'] == true )
			return array( $name => $status );
	}

	// If none are active
	return array();


}








?>