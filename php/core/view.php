<?php

/**
 * Gets the current generated context from the
 * Postworld globals.
 */
function pw_view_context(){
	global $pw;
	return _get($pw, 'view.context');
}

/**
 * Maps out the current relavant contexts of the current route
 *
 * @note 	The contexts are generally listed in reverse priority order
 * 			So the lower down on the list, the higher priority it will get when selecting a layout
 * 			As the layout selection will select the last relavant context.
 */
function pw_current_context(){
	$context = array();

	/**
	 * ADD CONTEXTS
	 * List them in the order they will appear
	 * Later listings take higher priority
	 */

	if( is_archive() )
		$context[] = 'archive';

	if( is_tax() || is_tag() || is_category() )
		$context[] = 'archive-taxonomy';

	if( is_post_type_archive() )
		$context[] = 'archive-post-type';

	if( is_year() || is_month() || is_day() )
		$context[] = 'archive-date'; 

	if( is_year() )
		$context[] = 'archive-year'; 

	if( is_month() )
		$context[] = 'archive-month'; 

	if( is_day() )
		$context[] = 'archive-day';

	if( is_search() )
		$context[] = 'search';

	if( is_tag() )
		$context[] = 'tag'; 		

	if( is_category() )
		$context[] = 'category';

	if( is_page() )
		$context[] = 'page';

	if( is_single() )
		$context[] = 'single';

	if( is_attachment() )
		$context[] = 'attachment';

	if( is_author() )
		$context[] = 'author';

	if( is_admin() )
		$context[] = 'admin';

	if( is_404() )
		$context[] = '404';

	// Home must come after page
	if( is_front_page() ){
		$context[] = 'home';
		$context[] = 'front-page';
	}

	// TAXONOMIES
	if( in_array( 'archive-taxonomy', $context ) ){
		// Define Taxonomy
		if( is_tag() )
			$taxonomy = 'post_tag';
		else if( is_category() )
			$taxonomy = 'category';
		else
			$taxonomy = get_query_var( 'taxonomy' );

		// Get the taxonomy object
		$taxonomy_obj = get_taxonomy( $taxonomy );

		// Check if taxonomy is builtin
		if( isset( $taxonomy_obj )  )
			// If it's a custom taxonomy, assign custom layout ID
			$context[] = 'archive-taxonomy-' . $taxonomy;

	}

	// SINGLE : POST TYPES
	if( in_array( 'single', $context ) ){
		global $post;
		$post_type = $post->post_type;
		$post_type_obj = get_post_type_object( $post_type );
		// Check if the post type is builtin
		if( isset( $post_type_obj ) )
			// If it's a custom point type, assign custom layout ID
			$context[] = 'single-'.$post_type;
	}

	// ARCHIVE : POST TYPE
	if( in_array( 'archive-post-type', $context ) ){

		$post_type = get_query_var( 'post_type' );
		$post_type_obj = get_post_type_object( $post_type );
		// Check if the post type is builtin
		if( isset( $post_type_obj ) )
			// If it's a custom point type, assign custom layout ID
			$context[] = 'archive-post-type-'.$post_type;
	}


	// BUDDYPRESS
	if( pw_is_buddypress_active() ){
		// SEE : plugins/buddypress/bp-core/bp-core-template.php
		if( is_buddypress() )
			$context[] = 'buddypress';
		// USER
		if( bp_is_user_activity() || bp_is_user() )
			$context[] = "buddypress-user";
	}


	// Apply Filters
	$context = apply_filters( 'pw_current_context', $context );

	return $context;
}

function pw_displayed_user(){
	///// DISPLAYED USER /////
	// Support for Buddypress Globals
	if ( function_exists('bp_displayed_user_id') ){
		$displayed_user_id = bp_displayed_user_id();
		//$bp->displayed_user
	} else{
		global $post;
		if( gettype( $post ) == 'object' )
			$displayed_user_id = $post->post_author;
	}

	if ( isset($displayed_user_id) && !empty($displayed_user_id) ){
		$displayed_userdata = get_userdata( $displayed_user_id );

		return array(
			"user_id" => $displayed_user_id,
			"display_name" => $displayed_userdata->display_name,
			"first_name" => $displayed_userdata->first_name,	
			);

	} else
		return false;

}

function pw_current_view(){
	// TODO : Refactor for efficientcy
	global $wp_query;
	$view = array();

	// URL
	$protocol = (!empty($_SERVER['HTTPS'])) ?
		"https" : "http";
	$view['url'] = $protocol."://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	$view['base_url'] = str_replace( '?' . $_SERVER['QUERY_STRING'], '', $view['url'] );
	//pw_log($_SERVER);

	$view['protocol'] = $protocol;
	$view["context"] = pw_current_context();
	$view["displayed_user"] = pw_displayed_user();
	$viewmeta = pw_get_view_meta( $view["context"] );
	$view = array_replace_recursive( $view, $viewmeta );

	$view = pw_to_array( $view );

	// Location Provider
	$view['location_provider'] = array();
	$view['location_provider']['html_5_mode'] = pw_location_provider_html_5_mode($view);

	return $view;
}

function pw_location_provider_html_5_mode( $view ){

	// Enable HTML 5 mode on the following contexts
	$contexts = array( 'search' );

	// Get additional contexts
	$contexts = apply_filters( 'pw_location_provider_html_5_mode_contexts', $contexts );

	// If any of the contexts match the current context, return true
	foreach( $contexts as $context ){
		if( in_array( $context, $view['context'] ) )
			return true;
	}

	return false;

}




function pw_view_query( $view ){
	// Generate the current query vars
	global $pw;

	if( empty( $view ) )
		$context = pw_current_view();

	// Start with the global wp_query
	global $wp_query;
	$query = $wp_query->query;

	/// DATE ARCHIVE ///
	if( in_array( 'archive-date', $view['context'] ) ){
		
	}
	
	/// POST TYPE ARCHIVE ///
	if( in_array( 'archive-post-type', $view['context'] ) ){
		$post_type = _get( $pw, 'view.post_type.name' );
		$query['post_type'] = $post_type;
	}

	/// TAXONOMY ARCHIVE ///
	if( in_array( 'archive-taxonomy', $view['context'] ) ){
		$query['tax_query'] = array(
			array(
				'taxonomy'	=>	$view['term']['taxonomy'],
				'field'		=>	'id',
				'terms'		=>	$view['term']['term_id']
				)
			);
	}

	///// DEFAULT QUERY VARS /////
	$default_query = array(
		'post_status' 		=> 	'publish',
		'post_type'			=>	'any',
		'fields'			=>	'preview',
		'posts_per_page'	=>	1000,
		);
	
	$default_query = apply_filters( 'pw_default_view_query', $default_query );

	$query = array_replace_recursive( $default_query, $query );

	return $query;

}

function pw_get_view_meta( $context = array() ){
	global $post;

	// If no context provided
	if( empty( $context ) )
		// Use the current context
		$context = pw_current_context();

	///// META OBJ /////
	$meta = array();

	// Set the default title
	$meta['title'] = wp_title( ' - ', false, 'right' );

	////// HOME /////
	if( in_array( 'home', $context ) ){
		$meta['title'] = get_bloginfo( 'name' );
	}

	////// SINGLE /////
	if( in_array( 'single', $context ) ){
		/// POST TYPE ///
		$post_type = $post->post_type;
		$post_type_obj = get_post_type_object( $post_type );
		$meta['post_type'] = $post_type_obj;
		$meta['title'] = $post->post_title;
	}

	////// ARCHIVE /////
	if( in_array( 'archive', $context ) ){

		/// POST TYPE ///
		if( in_array( 'archive-post-type', $context ) ){
			$post_type = get_query_var( 'post_type' );
			$post_type_obj = get_post_type_object( $post_type );
			$meta['post_type'] = $post_type_obj;
			$meta['title'] = $post_type_obj->labels->name;
		}

		/// TAXONOMY ///
		// Get term data, with exception handling for tags and categories
		else if(
			in_array( 'archive-taxonomy', $context ) || 
			in_array( 'category', $context ) ||
			in_array( 'tag', $context )
			){

			// TAG
			// Exception handling for tag terms
			if( in_array( 'tag', $context ) ){
				$taxonomy = 'post_tag';
				$tag_slug = get_query_var( 'tag' );
				$term = get_term_by( 'slug', $tag_slug, $taxonomy, 'ARRAY_A' );
				$term_id = $term['term_id'];
			}

			// CATEGORY
			// Exception handling for category terms
			elseif( in_array( 'category', $context ) ){
				$taxonomy = 'category';
				$tag_slug = get_query_var( 'category_name' );
				$term = get_term_by( 'slug', $tag_slug, $taxonomy, 'ARRAY_A' );
				$term_id = $term['term_id'];
			}

			// OTHER/CUSTOM TAXONOMIES
			else{
				$taxonomy = get_query_var( 'taxonomy' );
				$term_id = get_queried_object()->term_id;
				$term = get_term( $term_id, $taxonomy, 'ARRAY_A' );
			}

			// Set term in meta
			$meta['term'] = $term;

			// Term URL
			$meta['term']['url'] = get_term_link( intval($term_id) , $taxonomy );

			// Taxonomy Object
			$taxonomy_obj = get_taxonomy( $taxonomy );
			$meta['taxonomy'] = $taxonomy_obj;

			// View Title
			$meta['title'] = $taxonomy_obj->labels->singular_name . ' : ' . $meta['term']['name'];

			// If parent term exists
			if( $term['parent'] != 0 ){
				$term_parent = (array) get_term( $term['parent'], $taxonomy );
				$meta['term']['parent'] = (array) $term_parent;
				$meta['term']['parent']['url'] = get_term_link( intval($term_parent['term_id']) , $taxonomy );
			}

			// Filter so theme can add additional meta data 
			$meta['term'] = apply_filters( 'pw_view_term_meta', $meta['term'] );

		}

		/// AUTHOR ///
		else if( in_array( 'author', $context ) ){
			///// GET AUTHOR POSTWORLD META HERE /////
			$user = get_user_by( 'slug', get_query_var( 'author_name' ) );
			$meta['user'] = pw_get_user( $user->ID, 'all' );
			$meta['title'] = $user->display_name;
		}

	}

	///// POST OR PAGE /////
	if( in_array( 'single', $context ) )
		$meta["post"] = $GLOBALS['post'];

	return $meta;

}

function pw_get_bp_contexts(){
	// For adding meta keys to the layouts

	// If BuddyPress is not active
	if( !pw_is_buddypress_active() )
		return array();

	$bp_contexts = array();
	/*
		::: COMPONENTS :::
		"xprofile": "1",
        "settings": "1",
        "friends": "1",
        "messages": "1",
        "activity": "1",
        "notifications": "1",
        "groups": "1",
        "blogs": "1",
        "members": "1"
	*/

	// bp_is_members_component()
    // bp_is_profile_component()


    $bp_contexts[] = array(
    	'name'	=>	'BuddyPress',
    	'slug'	=>	'buddypress',
    	);


	if( bp_is_active( 'xprofile' ) )
		$bp_contexts[] = array(
				'slug' => 'user_activity',
				);


	return $bp_contexts;
}


function pw_get_contexts( $types = array() ){
	global $pw;

	///// GET FROM CACHE /////
	// If the contexts have already been generated
	// Get them directly from the global object
	if( empty( $types ) )
		$cache = true;
	else
		$cache = false;
	if( isset( $pw['contexts'] ) &&
		is_array( $pw['contexts'] ) &&
		$cache
		){
		$pw['contexts'] = apply_filters( 'pw_contexts', $pw['contexts'] );
		return $pw['contexts'];
	}

	///// DEFAULT TYPES /////
	if( empty( $types ) ){
		$types = array(
			'default',
			'home',
			'blog',
			'single',
			'archive',
			'search',
			'post-type',
			'taxonomy',
			'buddypress'
			);
	}

	// Apply filter so themes can modify available contexts 
	$types = apply_filters( 'pw_context_types', $types );

	///// ADD STANDARD CONTEXTS /////
	// TODO : Add Multi-language support, draw values from Language array
	$contexts = array();

	if( in_array( 'default', $types ) )
		$contexts[] = array(
			"label"	=>	"Default",
			"name"	=>	"default",
			"icon"	=>	"pwi-circle-medium",
			);

	if( in_array( 'home', $types ) )
		$contexts[] = array(
			"label"	=>	"Home",
			"name"	=>	"home",
			"icon"	=>	"pwi-home",
			);

	if( in_array( 'blog', $types ) )
		$contexts[] = array(
			"label"	=>	"Blog",
			"name"	=>	"blog",
			"icon"	=>	"pwi-pushpin",
			);

	if( in_array( 'single', $types ) )
		$contexts[] = array(
			"label"	=>	"Page",
			"name"	=>	"page",
			"icon"	=>	"pwi-file",
			);

	if( in_array( 'single', $types ) )
		$contexts[] = array(
			"label"	=>	"Post",
			"name"	=>	"single",
			"icon"	=>	"pwi-pushpin",
			);

	if( in_array( 'archive', $types ) )
		$contexts[] = array(
			"label"	=>	"Archive",
			"name"	=>	"archive",
			"icon"	=>	"pwi-th-list",
			);

	if( in_array( 'search', $types ) )
		$contexts[] = array(
			"label"	=>	"Search",
			"name"	=>	"search",
			"icon"	=>	"pwi-search",
			);



	///// ADD CUSTOM POST TYPES /////
	if( in_array( 'post-type', $types ) ){

		// Get registered custom post types
		$custom_post_types = get_post_types( array( '_builtin' => false, ), 'objects' );

		// Iterate through each post type and add it to contexts
		foreach( $custom_post_types as $post_type ){

			/// SINGLES ///
			if( in_array( 'single', $types ) )
				array_push( $contexts,
					array(
						"label"	=>	$post_type->labels->singular_name . " : Single",
						"name"	=>	"single-" . $post_type->name,
						"icon"	=>	"pwi-cube",
						)
				 );

			/// ARCHIVES ///
			if( in_array( 'archive', $types ) )
				if( $post_type->has_archive )
					array_push( $contexts,
						array(
							"label"	=>	$post_type->labels->singular_name . " : Archive",
							"name"	=>	"archive-post-type-" . $post_type->name,
							"icon"	=>	"pwi-cubes",
							)
					 );

		}

	}
	

	///// ADD BUILTIN TAXONOMIES /////
	if( in_array( 'taxonomy', $types ) ){

		/// CATEGORIES ///
		if( taxonomy_exists('category') )
			array_push( $contexts,
				array(
					"label"	=>	"Category : Archive",
					"name"	=>	"category",
					"icon"	=>	"pwi-folder",
					)
			 );
		/// TAGS ///
		if( taxonomy_exists('post_tag') )
			array_push( $contexts,
				array(
					"label"	=>	"Tag : Archive",
					"name"	=>	"tag",
					"icon"	=>	"pwi-tags",
					)
			 );
		
		///// ADD CUSTOM TAXONOMIES /////
		// Get registered custom taxonomies
		$custom_taxonomies = get_taxonomies( array( '_builtin' => false, ), 'objects' );

		foreach( $custom_taxonomies as $taxonomy ){

			/// TAXONOMIES ///
			// Only custom taxonomies
			if( !$taxonomy->_builtin )
				array_push( $contexts,
					array(
						"label"	=>	$taxonomy->labels->singular_name . " : Archive",
						"name"	=>	"archive-taxonomy-" . $taxonomy->name,
						"icon"	=>	"pwi-cube-o",
						)
				 );
		}

	}

	///// ADD BUDDYPRESS /////
	if( pw_is_buddypress_active() &&
		in_array( 'buddypress', $types ) ){

		$contexts[] = array(
			"label"	=>	"BuddyPress",
			"name"	=>	"buddypress",
			"icon"	=>	"pwi-plugin",
			);

		$contexts[] = array(
			"label"	=>	"BuddyPress User",
			"name"	=>	"buddypress-user",
			"icon"	=>	"pwi-user",
			);
	}

	// Apply contexts filter
	$contexts = apply_filters( 'pw_contexts', $contexts );
	
	// Set into globals
	if( $cache )
		$pw['contexts'] = $contexts;

	return $contexts;
}



?>