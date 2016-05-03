<?php
/**
 * Setup core definitions
 */
// @todo - REFACTOR THIS FILE SO ALL VARIABLES USE THEME SLUG
// @todo - LOOK FOR ANY STRAY FILTER NAMES THAT NEED CONSTANTS
// @todo - DEVELOP THEME MIGRATIONS TO RENAME DB KEYS AND DATABASE TABLES
// @todo - IN THIS ORDER : OPTIONS, POSTMETA, TERMMETA, USERMETA, DB TABLES

add_action( 'postworld_config', 'postworld_definitions', 11 );
function postworld_definitions(){

	// Localize the registered theme slug
	$theme_slug = pw_theme_slug();

	// Used for the core PW_Scripts grouping
	define( 'POSTWORLD_APP', $theme_slug.'-app' );

	// Empty array for footer scripts to be held in
	$GLOBALS['pw_footer_scripts'] = array();

	global $wpdb;
	$wpdb->pw_prefix = $wpdb->prefix . "postworld_";

	$GLOBALS['pw']['info'] = array(
		'version'		=>	1.602,
		'db_version'	=>	1.29,
		'mode'	=>	pw_mode(),
		'slug'	=>	'postworld',
		);
	$GLOBALS['pw']['angular_modules'] = array();
	$GLOBALS['pw']['iconsets'] = array();

	///// DEFINE MODEL FILTER NAMES /////
	define( 'PW_FIELD_MODELS', 		$theme_slug.'-model-fields' );
	define( 'PW_POST_FIELD_MODELS', $theme_slug.'-model-post-fields' );
	define( 'PW_USER_FIELD_MODELS', $theme_slug.'-model-user-fields' );

	define( 'PW_MODEL_STYLES', 		$theme_slug.'-model-styles' );
	define( 'PW_MODEL_BACKGROUNDS', $theme_slug.'-model-backgrounds' );

	define( 'PW_TERM_FEED', 		$theme_slug.'-term-feed-' );
	define( 'PW_FEED_DEFAULT', 		$theme_slug.'-feed-default' );
	define( 'PW_FEED_OVERRIDE', 	$theme_slug.'-feed-override' );
	define( 'PW_STYLES_DEFAULT', 	$theme_slug.'-style-defaults' );

	///// DEFINE META FILTER NAMES /////
	define( 'PW_POSTS', 	'pw_posts' );
	define( 'PW_USERS', 	'pw_users' );
	define( 'PW_POSTMETA', 	$theme_slug.'-postmeta' );
	define( 'PW_USERMETA', 	$theme_slug.'-usermeta' );
	define( 'PW_MODULES', 	$theme_slug.'-modules-filter' );

	///// DEFINE PRINT FILTERS /////
	define( 'PW_GLOBAL_OPTIONS',	$theme_slug.'-global-options' ); // Case in-sensitive

	///// VERSIONS /////
	define( 'PW_DB_VERSION', $theme_slug.'-db-version' );
	define( 'PW_VERSIONS', $theme_slug.'-versions' );

	// MUST BE DEFINED BY THE THEME
	//define( 'PW_OPTIONS_STYLES', 	'postworld-styles-theme' );

	/**
	 * Define the META keys.
	 * Used in 'wp_postmeta' and 'pw_termmeta' tables
	 */
	define( 'PW_POSTMETA_KEY',	'pw_meta', 		true ); // Case in-sensitive
	define( 'PW_USERMETA_KEY',	'pw_meta', 		true ); // Case in-sensitive
	define( 'PW_TAXMETA_KEY',	'pw_meta', 		true ); // Case in-sensitive
	define( 'PW_AVATAR_KEY',	'pw_avatar', 	true ); // Case in-sensitive
	define( 'PW_COLORS_KEY',	'pw_colors', 	true ); // Case in-sensitive

	/**
	 * Define the OPTIONS keys.
	 * Used in 'wp_options' table as 'option_name' key
	 */
	define( 'PW_OPTIONS_CORE', 					'postworld-core' );
	define( 'PW_OPTIONS_MODULES', 				'postworld-modules' );
	define( 'PW_OPTIONS_SITE', 					'postworld-site' );
	define( 'PW_OPTIONS_LAYOUTS', 				'postworld-layouts' );
	define( 'PW_OPTIONS_SIDEBARS', 				'postworld-sidebars' );
	define( 'PW_OPTIONS_FEEDS', 				'postworld-feeds' );
	define( 'PW_OPTIONS_FEED_SETTINGS', 		'postworld-feed-settings' );
	define( 'PW_OPTIONS_SOCIAL', 				'postworld-social' );
	define( 'PW_OPTIONS_ICONSETS', 				'postworld-iconsets' );
	define( 'PW_OPTIONS_BACKGROUNDS', 			'postworld-backgrounds' );
	define( 'PW_OPTIONS_BACKGROUND_CONTEXTS', 	'postworld-background-contexts' );
	define( 'PW_OPTIONS_SHORTCODES', 			'postworld-shortcodes' );
	define( 'PW_OPTIONS_SHORTCODE_SNIPPETS', 	'postworld-shortcode-snippets' );
	define( 'PW_OPTIONS_HEADER_CODE', 			'postworld-header-code' );
	define( 'PW_OPTIONS_DEFAULTS', 				'postworld-defaults' );
	define( 'PW_OPTIONS_COMMENTS', 				'postworld-comments' );
	define( 'PW_CACHE_ICONSET', 				'postworld-cache-iconset-' );
}