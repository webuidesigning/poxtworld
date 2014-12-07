<?php
/*
     _    ____ ___       _   _ _   _ _ _ _   _           
    / \  |  _ \_ _|  _  | | | | |_(_) (_) |_(_) ___  ___ 
   / _ \ | |_) | |  (_) | | | | __| | | | __| |/ _ \/ __|
  / ___ \|  __/| |   _  | |_| | |_| | | | |_| |  __/\__ \
 /_/   \_\_|  |___| (_)  \___/ \__|_|_|_|\__|_|\___||___/
                                                         
/////////////////////////////////////////////////////////*/

function _get( $obj, $key ){
	return pw_get_obj( $obj, $key );
}
function pw_get_obj( $obj, $key ){
	// Checks to see if a key exists in an object,
	// and returns it if it does exist. Otherwise return false.

	/*	PARAMETERS:
		$obj 	= 	[array]
		$key 	= 	[string] ie. ( "key.subkey.subsubkey" )
	*/

	// If $key is empty, return the whole $obj
	if( empty($key) )
		return $obj;

	///// KEY PARTS /////
	// FROM : "key.subkey.sub.subkey"
	// TO 	: array( "key", "subkey", "subkey" )
	$key_parts = explode( '.', $key );
	// Count how many parts
	$key_parts_count = count( $key_parts );

	foreach($key_parts as $key_part){
		if( isset( $obj[$key_part] ) )
			$obj = $obj[$key_part];
		else
			return false;
	}

	return $obj;

}

function _set( $obj, $key, $value ){
	return pw_set_obj( $obj, $key, $value );
}
function pw_set_obj( $obj, $key, $value ){
	// Sets the value of an object,
	// even if it or it's parent(s) doesn't exist.
	
	/*	PARAMETERS:
		$obj 	= 	[array]
		$key 	= 	[string] ie. ( "key.subkey.subsubkey" )
		$value 	= 	[string/array/object]
	*/

	///// KEY PARTS /////
	// FROM : "key.subkey.sub.subkey"
	// TO 	: array( "key", "subkey", "subkey" )
	$key_parts = array_reverse( explode( '.', $key ) );
	// Count how many parts
	$key_parts_count = count( $key_parts );
	
	// Prepare to catch finished key parts
	$key_parts_done = array();

	// Iterate through each key part
	$seed = array();
	$i = 0;
	foreach( $key_parts as $key_part ){
		$i++;
		// First Key
		if( $i == 1 ){
			// Create seed with first key
			$seed[$i][$key_part] = $value;
		// Other Keys
		} else{
			// Nest previous seed in current key
			$seed[$i][$key_part] = $seed[($i-1)];
		}
		// Last Key
		if( $i == $key_parts_count ){
			// Return final seed result
			$seed = $seed[$i];
		}
	}

	// Merge $seed array with input $array
	$obj = array_replace_recursive( $obj, $seed );

	return $obj;
}



/*
     _    ____ ___       _   _                 __  __      _        
    / \  |  _ \_ _|  _  | | | |___  ___ _ __  |  \/  | ___| |_ __ _ 
   / _ \ | |_) | |  (_) | | | / __|/ _ \ '__| | |\/| |/ _ \ __/ _` |
  / ___ \|  __/| |   _  | |_| \__ \  __/ |    | |  | |  __/ || (_| |
 /_/   \_\_|  |___| (_)  \___/|___/\___|_|    |_|  |_|\___|\__\__,_|

////////////////////////////////////////////////////////////////////*/
function pw_set_wp_usermeta( $vars ){
	/*
		- Sets meta key for the given user under the given key
			in the `wp_usermeta` table
		- Object values passed in can be passed as PHP objects or Arrays,
			and they will automatically be converted and stored as JSON
		
		PARAMETERS:
		$vars = array(
			"user_id"	=>	[integer], 	(optional)
			"sub_key"	=>	[string],	(optional)
			"value" 	=>	[mixed],	(required)
			"meta_key" 	=>	[string] 	(optional)
			);
	*/

	$default_vars = array(
		'user_id'	=>	get_current_user_id(),
		'meta_key'	=>	PW_USERMETA_KEY,
		'sub_key'	=>	'',
		'value'		=>	'',
		);

	$vars = array_replace_recursive( $default_vars, $vars );

	pw_log( json_encode( $vars ) );

	extract($vars);

	// TODO : Use pw_auth_user() here

	///// USER ID /////
	// Security check to see if user can access user meta
	$user_id = pw_check_user_id( $user_id );
	if( !is_numeric( $user_id ) )
		return $user_id; // Will be: array('error'=>'[Error message]')

	///// SUBKEY ////
	if( !empty( $sub_key ) ){
		///// SETUP DATA /////
		// Check if the meta key exists
		$meta_value = get_user_meta( $user_id, $meta_key, true );

		// If it exists, decode it from a JSON string into an object
		if( is_string( $meta_value ) && !empty($meta_value) )
			$meta_value = json_decode($meta_value, true);
		// If it does not exist, define it as an empty array
		else
			$meta_value = array();

		///// SET VALUE /////
		$meta_value = pw_set_obj( $meta_value, $sub_key, $value );

		// Encode back into JSON
		$meta_value = json_encode( $meta_value );

	}

	///// NO SUBKEY /////
	else if( !empty( $value ) ){
		
		if( is_array( $value ) )
			$value = json_encode($value);

		$meta_value = $value;

	}
	
	//pw_log( 'pw_set_wp_usermeta : $update_user_meta : ' . $user_id . ' / ' . $meta_key .' / '. $meta_value );

	// Set user meta
	$update_user_meta = update_user_meta( $user_id, $meta_key, $meta_value );

	// BOOLEAN : True on successful update, false on failure.
	return $update_user_meta;

}


function pw_get_wp_usermeta($vars){
	/*
	- Gets meta key for the given user under the `pw_meta` key
		in the `wp_usermeta` table
	
		PARAMETERS:
		$vars = array(
			"user_id"	=>	[integer], 	(optional)
			"sub_key"	=>	[string],
			"format" 	=>	[string] 	"JSON" / "ARRAY" (default),
			"meta_key" 	=>	[string] 	(optional)
			);
	*/

	extract($vars);
	$meta_key = pw_usermeta_key;

	///// USER ID /////
	// Security check to see if user can access user meta
	$user_id = pw_check_user_id( $user_id );
	if( !is_numeric($user_id) )
		return $user_id;

	///// KEY /////
	if( !isset($sub_key) )
		$sub_key = '';

	///// GET DATA /////
	// Check if the meta key exists
	$meta_value = get_user_meta( $user_id, $meta_key, true );
	if( empty($meta_value) )
		return false;

	// Decode from JSON
	$meta_value = json_decode( $meta_value, true );

	// Get Subkey
	$return = pw_get_obj( $meta_value, $sub_key );
	if( $return == false )
		return $return;

	///// FORMAT /////
	if( isset($format) && $format == 'JSON' )
		return json_encode( $return );
	
	return $return;

}


/*   _    ____ ___       ____           _     __  __      _        
    / \  |  _ \_ _|  _  |  _ \ ___  ___| |_  |  \/  | ___| |_ __ _ 
   / _ \ | |_) | |  (_) | |_) / _ \/ __| __| | |\/| |/ _ \ __/ _` |
  / ___ \|  __/| |   _  |  __/ (_) \__ \ |_  | |  | |  __/ || (_| |
 /_/   \_\_|  |___| (_) |_|   \___/|___/\__| |_|  |_|\___|\__\__,_|
                                                                   
////////////////////////////////////////////////////////////////////*/


function pw_set_wp_postmeta($vars){
	/*
		- Sets meta key for the given post under the given key
			in the `wp_postmeta` table
		- Object values passed in can be passed as PHP objects or Arrays,
			and they will automatically be converted and stored as JSON
		
		PARAMETERS:
		$vars = array(
			"post_id"	=>	[integer], 	(optional)
			"sub_key"	=>	[string],	(required)
			"value" 	=>	[mixed],	(required)
			"meta_key" 	=>	[string] 	(optional)
			);
	*/

	extract($vars);

	if( !isset( $meta_key ) )
		$meta_key = pw_postmeta_key;

	///// POST ID /////
	if( !isset($post_id) ){
		global $post;
		$post_id = $post->ID;
	}

	///// USER ID /////
	$post_id = pw_check_user_post( $post_id );
	if( !is_numeric($post_id) )
		return $post_id; // Will be: array('error'=>'[Error message]')

	///// SUB KEY /////
	if( isset($sub_key) ){
		///// SETUP DATA /////
		// Check if the meta key exists
		$meta_value = get_post_meta( $post_id, $meta_key, true );

		// If it exists, decode it from a JSON string into an object
		if( !empty($meta_value) )
			$meta_value = json_decode($meta_value, true);
		// If it does not exist, define it as an empty array
		else
			$meta_value = array();

		///// SET VALUE /////
		$meta_value = pw_set_obj( $meta_value, $sub_key, $value );

		// Encode back into JSON
		$meta_value = json_encode( $meta_value );

	} else{
		if( is_array( $meta_value ) || is_object( $meta_value ) )
			// Encode arrays and objects into JSON
			$meta_value = json_encode( $meta_value );	
	}

	// Set user meta
	$update_post_meta = update_post_meta( $post_id, $meta_key, $meta_value );

	// BOOLEAN : True on successful update, false on failure.
	return $update_post_meta;

}


function pw_get_wp_postmeta($vars){
	/*
	- Gets meta key for the given post under the given key
		in the `wp_postmeta` table
	
		PARAMETERS:
		$vars = array(
			"post_id"	=>	[integer], 	(optional)
			"meta_key" 	=>	[string] 	(optional)
			"sub_key"	=>	[string],
			);
	*/

	///// SET DEFAULTS /////
	if( !isset( $vars['meta_key'] ) )
		$vars['meta_key'] = pw_postmeta_key;

	if( !isset( $vars['post_id'] ) ){
		global $post;
		$vars['post_id'] = $post->ID;
	}

	extract($vars);

	///// USER ID /////
	$user_id = pw_check_user_post( $post_id );
	if( !is_numeric($post_id) )
		return $post_id;

	///// KEY /////
	if( !isset($sub_key) )
		$sub_key = '';

	///// GET DATA /////
	// Check if the meta key exists
	$meta_value = get_post_meta( $post_id, $meta_key, true );
	if( empty($meta_value) )
		return false;

	// Decode from JSON
	$meta_value = json_decode( $meta_value, true );

	// Get Subkey
	$value = pw_get_obj( $meta_value, $sub_key );
	
	return $value;

}


//i_get_postmeta
function pw_get_postmeta( $vars = array() ){
	global $post;
	
	///// DEFAULT VALUES /////
	$defaultVars = array(
		'post_id' 	=> $post->ID,
		'meta_key' 	=> PW_POSTMETA_KEY,
		'sub_key'	=> false,
		'type' 		=> 'json',
		);

	$vars = array_replace_recursive( $defaultVars, $vars );

	extract( $vars );

	///// CACHING LAYER /////
	// Make a global to cache data at runtime
	// To prevent making multiple queries on the same postmeta
	global $pw_postmeta_cache;

	// If cached data is already found
	if( isset( $pw_postmeta_cache[$post_id][$meta_key] ) ){
		// If no subkey
		if( empty( $vars['sub_key'] ) )
			// Return the whole value
			return $pw_postmeta_cache[$post_id][$meta_key];
		else
			// Otherwise, get and return the subkey value
			return _get( $pw_postmeta_cache[$post_id][$meta_key], $vars['sub_key'] );
	}

	///// GET POST META /////
	// Get Post Meta
	$metadata = get_post_meta( $vars['post_id'], $vars['meta_key'], true );
	
	if( empty( $metadata ) )
		$metadata = array();

	// Convert from JSON to A_ARRAY
	if( $vars['type'] == "json" && is_string( $metadata ) )
		$metadata = json_decode( $metadata, true );

	///// APPLY FILTERS /////
	// If the value is from the default postmeta key
	if( $meta_key == PW_POSTMETA_KEY )
		// Apply the standard filter ID
		$metadata = apply_filters( PW_POSTMETA, $metadata );
	else
		// Apply custom filter ID
		$metadata = apply_filters( PW_POSTMETA.'-'.$meta_key, $metadata );

	///// CACHING LAYER /////
	// Store meta data in a runtime cache
	if( !isset( $pw_postmeta_cache[$post_id] ) ) 
		$pw_postmeta_cache[ $post_id ] = array();
	$pw_postmeta_cache[ $post_id ][ $meta_key ] = $metadata;


	///// SUB KEY /////
	// If no subkey
	if( empty( $vars['sub_key'] ) )
		// Return the whole value
		return $metadata;
	else
		// Otherwise, get and return the subkey value
		return _get( $metadata, $vars['sub_key'] );

}


/*   _    ____ ___        ___        _   _                 
    / \  |  _ \_ _|  _   / _ \ _ __ | |_(_) ___  _ __  ___ 
   / _ \ | |_) | |  (_) | | | | '_ \| __| |/ _ \| '_ \/ __|
  / ___ \|  __/| |   _  | |_| | |_) | |_| | (_) | | | \__ \
 /_/   \_\_|  |___| (_)  \___/| .__/ \__|_|\___/|_| |_|___/
                              |_|                          
//////////////////////////////////////////////////////////*/

function pw_get_option( $vars ){
	// Returns a sub key stored in the `i-options` option name
	//	From `wp_options` table
	/*
		vars = array(
			'option_name'	=>	[string], // "i-options",
			'key'			=> 	[string], // "images.logo",
			'filter'		=>	[boolean] // Gives option to disable filtering
	*/

	$default_vars = array(
		'option_name'	=>	PW_OPTIONS_SITE,
		'key'			=>	false,
		'filter'		=>	true,
		);
	$vars = array_replace_recursive( $default_vars, $vars );

	extract($vars);

	///// CACHING LAYER /////
	// Make a global to cache data at runtime
	// To prevent making multiple queries and json_decodes on the same option
	global $i_options_cache;

	// If cached data is already found, return it instantly
	if( isset( $i_options_cache[$option_name] ) ){
		$value = $i_options_cache[$option_name];
	}
	else{

		///// GET OPTION /////
		// Retreive Option
		$value = get_option( $option_name, array() );
		
		// Decode from JSON, assuming it's a JSON string
		// TODO : Handle non-JSON string values
		if( !empty( $value ) )
			$value = json_decode( $value, true );

		///// APPLY FILTERS /////
		// This allows themes to over-ride default settings for options
		// ie. pwGetOption-postworld-styles-theme, to modify the default values
		if( $filter ){
			// Apply Filters
			$value = apply_filters( $option_name , $value );
			// Depreciated
			$value = apply_filters( 'iOptions-' . $option_name , $value );

		}

		///// CACHING LAYER /////
		// Set the decoded data into the cache
		$i_options_cache[$option_name] = $value;

	}

	//pw_log( 'pw_get_option : ' . $option_name . ' : ' . json_encode($value) );

	// If no key set, return the value directly
	if( empty( $key ) )
		return $value;
	// Get Option Value Object Key Value
	else
		return _get( $value, $key );

}


?>