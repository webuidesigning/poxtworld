<?php

////////// CSS COLUMN PROPERTY SHORTCODE //////////

///// COLUMNS /////
function pw_pagelist_shortcode( $atts, $content = null, $tag ) {
	
	// Extract Shortcode Attributes, set defaults
	extract( shortcode_atts( array(
		"class" => "",
		"view" 	=> "list-h2o",
		"max"	=>	3
	), $atts ) );

	///// Generate Query /////
	global $post;
	$query = array();

	// Set Post Type
	$query['post_type'] = $post->post_type;

	// Set Post Status
	$query['post_status'] = 'publish';

	// Set Other Properties
	$query['posts_per_page'] = 50;
	$query["fields"] = array(
		'ID',
		'post_title',
		'post_parent',
		'post_excerpt',
		'post_timestamp',
		'post_date',
		'post_date_gmt',
		'comment_status',
		'post_parent',
		'post_type',
		'comment_count',
		'post_permalink',
		'image(all)',
		'feed_order',
		'post_meta(all)',
		);

	// Generate query class
	switch($tag){
		case "subpages":
			// Set parent as current post ID
			$query['post_parent'] = $post->ID;
			break;
		case "siblings":
			// Set parent as current post ID
			$query['post_parent'] = $post->post_parent;
			// Exclude the current page
			$query['post__not_in'] = array($post->ID);
			break;
	}
	
	// Add Max Posts
	$query['posts_per_page'] = $max;

	// Setup Feed Query
	$feed_query_args = array(
		'feed_query' => $query,
		'view' => $view,
		);

	//return json_encode( pw_query( $query ) );
	
	// If Postworld is Activated, Return Print Feed
	if( function_exists('pw_print_feed') ){
		$shortcode = pw_print_feed( $feed_query_args );	
		//$shortcode = json_encode( $feed_query_args );
		return $shortcode;
	}
	else
		return false;

}

add_shortcode( 'subpages', 'pw_pagelist_shortcode' );
add_shortcode( 'siblings', 'pw_pagelist_shortcode' );


?>