<?php

function pw_get_current_layout(){

	global $pw;

	// If layouts module is not activated, return false
	if( !in_array( 'layouts', $pw['info']['modules'] ) )
		return false;

	// An array with the current context(s)
	$contexts = pw_current_context();

	// Set Layout Variable
	$layout = false;

	// Get layouts
	$pwLayouts = i_get_option( array( 'option_name' => PW_OPTIONS_LAYOUTS ) );

	/// GET LAYOUT : FROM POSTMETA : OVERRIDE ///
	// Check for layout override in : post_meta.pw_meta.layout
	$override_layout = pw_get_wp_postmeta( array( 'sub_key' => 'layout' ) );
	
	// If override layout exists
	if( $override_layout != false && !empty( $override_layout ) ){
		$layout = $override_layout;
		$layout['source'] = 'post_meta';
	}

	/// GET LAYOUT : FROM CONTEXT ///
	if( !$layout || pw_get_obj( $layout, 'template' ) == 'default' ){
		// Iterate through all the current contexts
		// And find a match for it
		foreach( $contexts as $context ){
			$test_layout = pw_get_obj( $pwLayouts, $context );
			// If there is a match
			if( (bool) $test_layout ){
				$layout = $test_layout;
				$layout['source'] = $context;
			}
		}
	}

	/// GET LAYOUT : DEFAULT LAYOUT : FALLBACK ///
	if( !$layout || $layout['template'] == 'default' ){ //  || $layout['layout'] == 'default'
		$layout = pw_get_obj( $pwLayouts, 'default' );
		$layout['source'] = 'default';
	}

	// FILL IN DEFAULT VALUES
	// In case of incomplete layout values
	if( pw_get_obj( $layout, 'source' ) != 'default' ){
		// Get the default layout
		$default_layout = pw_get_obj( $pwLayouts, 'default' );
		// Merge it with the default layout, in case values are missing
		$layout = array_replace_recursive( $default_layout, $layout );

		// TODO : THIS BETTER TECHNIQUE
		// Fill in default header and footer
		if( empty( $layout['header']['id'] ) )
			$layout['header']['id'] = $default_layout['header']['id'];
		if( empty( $layout['footer']['id'] ) )
			$layout['footer']['id'] = $default_layout['footer']['id'];
	}

	// Autocorrect layout in case of migrations
	$layout = pw_autocorrect_layout( $layout );

	// Apply filter so that $layout can be over-ridden
	$layout = apply_filters( 'pw_layout', $layout );

	return $layout;

}

?>