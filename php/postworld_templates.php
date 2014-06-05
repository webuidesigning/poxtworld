<?php


function pw_get_dirs($path = '.') {
	$dirs = array();
	
	if( file_exists($path) ){
		$subpaths = new DirectoryIterator($path);
		foreach ( $subpaths as $file) {
			if ($file->isDir() && !$file->isDot()) {
				$dirs[] = $file->getFilename();
			}
		}	
	}
	
	return $dirs;
}

function pw_construct_template_obj( $args ){
	extract($args);

	// Set Defaults
	if( !isset( $path_type ) )
		$path_type = 'dir';
	if( !isset( $ext ) )
		$ext = 'php';
	if( !isset( $url ) )
		$url = '';
	if( !isset( $subdirs ) )
		$subdirs = pw_get_dirs( $dir );

	$template_object = array();

	// Iterate through each Directory
	foreach( $subdirs as $subdir ){
		$template_object[$subdir] = array();
		$files = glob( trailingslashit($dir) . $subdir . '/*' . '.' . $ext );

		// Iterate through each File
		foreach( $files as $file ){
			$basename = basename($file);
			$basename_noext = basename( $file, '.' . $ext );

			// Output Directory Path
			if( $path_type == 'dir' )
				$template_object[$subdir][$basename_noext] = $file;
			else if ( $path_type == 'url' )
			// Output URL Path
				$template_object[$subdir][$basename_noext] = trailingslashit($url) . trailingslashit($subdir) . $basename;
			
		}
	
	}

	return $template_object;
}




function pw_get_templates( $vars = array() ){
	/*
		$vars = array(
			'subdirs'			=>	[array]	(optional)
			'posts'				=>	[array]	(optional)
			'path_type'			=>	[string] ( 'url' / 'dir' ),
			'ext'				=>	[string] ( 'php' / 'html' ),
			'source'			=>	[string] ( 'default' / 'merge' ),
			)

	*/


	global $pwSiteGlobals;
	extract($vars);

	// Set Defaults
	if( !isset($templates_object) )
		$templates_object = array(); // TODO - Add handling for this for performance, folder or file specifics
	if( !isset( $path_type ) )
		$path_type = 'url';
	if( !isset( $source ) )
		$source = 'merge';
	if( !isset( $ext ) )
		$ext = 'html';

	// Check to see if there is a templates folder in the child folder
	//$has_override_templates_dir = is_dir( $pwSiteGlobals['templates']['dir']['override'] );

	// Setup Variables
	$default_template_dir = $pwSiteGlobals['templates']['dir']['default'];
	$default_template_url = $pwSiteGlobals['templates']['url']['default'];
	$override_template_dir = $pwSiteGlobals['templates']['dir']['override'];
	$override_template_url = $pwSiteGlobals['templates']['url']['override'];


	///// DEFAULT Templates Object /////
	$default_template_obj_args = array(
		'dir'	=>	$default_template_dir,
		'url'	=>	$default_template_url,
		'ext'	=>	$ext,
		'path_type'	=>	$path_type,
		);

	if( isset($subdirs) )
		$default_template_obj_args['subdirs'] = (array) $subdirs;

	if( $source == 'default' || $source == 'merge' )
		$default_template_obj = pw_construct_template_obj( $default_template_obj_args );


	///// OVERRIDE Templates Object /////
	$override_template_obj_args = array(
		'dir'	=>	$override_template_dir,
		'url'	=>	$override_template_url,
		'ext'	=>	$ext,
		'path_type'	=>	$path_type,
		);

	if( isset($subdirs) )
		$override_template_obj_args['subdirs'] = (array) $subdirs;

	if( $source == 'override' || $source == 'merge' )
		$override_template_obj = pw_construct_template_obj( $override_template_obj_args );


	///// Merge Results /////
	if( $source == 'merge' ){
		// Start with Default Template Object
		$template_obj = $default_template_obj;

		// Iterate over the Override Template Object
		foreach( $override_template_obj as $subdir => $templates ){

			// Iterate over the Templates
			foreach( $templates as $template_id => $template_value ){
				
				// Create the Subobject if it doesn't exist
				if( !isset($template_obj[$subdir]) )
					$template_obj[$subdir] = array();

				// Add the Override Value
				$template_obj[$subdir][$template_id] = $template_value;

			}

		}

	}

	///// RETURN : BEFORE POSTS /////
	// If 'subdirs' is specified, and 'posts' is not specified
	if( isset($subdirs) &&
		!in_array( 'posts', $subdirs ))
		// Return before processing post templates
		return $template_obj;


	////////// POST TEMPLATES : OBJECT STRUCTURE //////////

	///// GET POST TYPES /////
	$post_types_defined = ( isset ( $posts['post_types'] ) ) ? true : false;
	$post_types = $post_types_defined
		? // Post Types defined
		$posts['post_types']
		: // Post Types are not defined
		get_post_types( array( 'public'   => true ), 'names', 'and' );

	// If the post types were not defined and so derived from `get_post_types`
	if( !$post_types_defined ){
		// Flatten Array
		$post_types_final = array();
		foreach ( $post_types as $post_type ) {
				$post_types_final[] = $post_type ;
		}
		$post_types = $post_types_final;
	}
		

	///// GET VIEWS /////
	$post_views = ( isset( $posts['post_views'] ) ) ?
		$posts['post_views'] :
		$pwSiteGlobals['post_views'];


	///// CONSTRUCT POSTS TEMPLATE OBJECT /////
	$post_template_obj = array();

	// Iterate through post types
	foreach( $post_types as $post_type ){

		// Create empty array for post type		
		$post_template_obj[ $post_type ] = array();

		// Iterate through post views
		foreach( $post_views as $post_view ){

			// Define the id for the current template
			$template_id = $post_type . '-' . $post_view;
			// If not available, default to the 'post' post_type
			$default_template_id = 'post' . '-' . $post_view;

			// Set the template object / string to use
			$existing_template_id = 
				( isset( $template_obj['posts'][ $template_id ] ) ) ?
				$template_id : $default_template_id;

			// Check if the object exists, otherwise return empty string
			$post_template_obj[ $post_type ][ $post_view ] =
				( isset( $template_obj['posts'][ $existing_template_id ] ) ) ? 
				$template_obj['posts'][ $existing_template_id ] : '';

		}

	}

	$template_obj['posts'] = $post_template_obj;

	return $template_obj;

}


function pw_get_template( $subdir, $template_id, $ext = "html", $path_type = "url" ){
	// Returns a single string for panel template from ID

	$panel_template = pw_get_templates ( array(
			"subdirs" 	=> 	array( $subdir ),
			"path_type"	=> 	$path_type,
			"ext"		=>	$ext,
			)
		);

	if( isset($panel_template) && isset($panel_template[$subdir][$template_id]) )
		return (string) $panel_template[$subdir][$template_id];
	else
		return false;
}


function  pw_get_post_template ( $post_id, $post_view, $path_type='url' ){

	/* Returns an template path based on the provided post ID and view
		Process
		
		Check the post type of the post as $post_type with get_post_type( $post_id )
		Using pw_get_templates(), get the template object
		Input :
		
		$args = array(
		    'posts' => array(
		        'post_types' => array( $post_type ),    // 'post'
		        'post_views' => array( $post_view )     // 'full'
		    ),
		);
		$post_template_object = pw_get_templates ($args);
		Output :
		
		{
		posts : {
		     'post' : {
		          'full' : '/wp-content/plugins/postworld/templates/posts/post-full.html',
		          },
		     },
		}
		return : string (The single template path) : /wp-content/plugins/postworld/templates/posts/post-full.html
	 */
		
	 $post_type =  get_post_type( $post_id );

	 $args = array(
	 		'subdirs'	=>	array('posts'),
			'posts' => array(
	    		'post_types' => array( $post_type ),    // 'post'
	    		'post_views' => array( $post_view ),    // 'full'
				),
			'path_type'	=>	$path_type,
		);
	
	$templates_object = pw_get_templates( $args );
	
	

	return $templates_object['posts'][$post_type][$post_view];

}



function pw_get_panel_template( $panel_id ){
	// Returns a single string for panel template from ID
	return pw_get_template( 'panels', $panel_id, 'html', 'url' );

}


// Include a Postworld Feed Template from templates/feeds
function pw_parse_template( $template_path, $vars = array() ){
	extract($vars);
	
	ob_start();
	//include $i_paths['infinite']['dir'].'/php/setup-archive.php';
	include $template_path;
	$content = ob_get_contents();
	ob_end_clean();
	return $content;
}


function pw_get_menu_templates(){
	
	$templates = pw_get_templates(
		array(
			'subdirs' => array( 'menu-kit' ),
			'ext' => 'php',
			'path_type' =>  'dir',
			)
		)['menu-kit'];

	return $templates;

}

?>