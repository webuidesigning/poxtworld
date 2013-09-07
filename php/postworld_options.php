<?php


$pw_defaults = array(

	'roles' 			=> array(
		'Administrator' 	=> array(
			'vote_points'	=> 10,
			),
		'Editor' 			=> array(
			'vote_points'	=> 5,
			),
		'Author' 			=> array(
			'vote_points'	=> 2,
			),
		'Contributor' 		=> array(
			'vote_points'	=> 1,
			),
		),

	'points' => array(
		'post_types'		=> array('post'),
		'cache_interval'	=> 'fifteen_minutes',
		'cron_logs'			=> 0,
		),

	'rank' => array(
		'post_types'		=> array('post'),
		'cache_interval'	=> 'fifteen_minutes',
		'cron_logs'			=> 0,
		'equations'			=> array(
			'default'		=> array(
				'time_compression'	=>	0.5,
				'time_weight'		=>	1,
				'comments_weight'	=>	1,
				'points_weight'		=>	1,
				'fresh_period'		=>	1*$ONE_WEEK,
				'fresh_multiplier'	=>	2,
				'archive_period'	=>	6*$ONE_MONTH,
				'archive_multiplier'=>	0.2,
				'free_rank_score'	=>	100,
				'free_rank_period'	=>	3*$ONE_DAY,
				),
			),
		),

	'feeds' => array(
		'cache_feeds'		=> array(),
		'cache_interval'	=> 'fifteen_minutes',
		'cron_logs'			=> 0,
		),

	'views'	=> array(
		'post_types'	=>	array(),
		'stats_page'	=>	0,
		'description'	=>	"Views track which posts that you've already seen.",
		'tracker'	=>	array(
			'bottom'	=>	1000,	// How many pixels from the bottom
			'time'		=>	60,		// How many seconds after page load
			),
		'labels'		=>	array(
			'name'			=>	"Views",
			'singular_name'	=>	"View",
			'not_viewed'	=>	"View this",
			'has_viewed'	=>	"You have already viewed this",
			),
		),

	'shares'	=> array(
		'post_types'	=>	array(),
		'stats_page'	=>	0,
		'description'	=>	"Views track which posts that you've already seen.",
		'tracker'	=>	array(
			'ip_history'	=>	100,	// How many unique IP addresses before re-count
			),
		'labels'		=>	array(
			'name'			=>	"Views",
			'singular_name'	=>	"View",
			'not_viewed'	=>	"View this",
			'has_viewed'	=>	"You have already viewed this",
			),
		),

	'cleanup' => array(
		'points'			=> array(
			'interval'		=> 'daily',
			),
		'cron_logs'			=> array(
			'interval'		=> 'weekly',
			),
		),

	'classes'	=>	array(
		'post_types'	=>	array(),
		'data'	=>	array(
			'a_blog'	=>	array(
				'name'			=>	"Blog",
				'description'	=>	"Main Blog",
				'roles'			=>	array('Administrator', 'Editor', 'Author'),
				),
			'a_feature'	=>	array(
				'name'			=>	"Feature",
				'description'	=>	"Main Feature",
				'roles'			=>	array('Administrator', 'Editor', 'Author'),
				),
			'c_blog'	=>	array(
				'name'			=>	"Community Blog",
				'description'	=>	"Blog for community members.",
				'roles'			=>	array('Contributor'),
				),
			'c_feature'	=>	array(
				'name'			=>	"Community Feature",
				'description'	=>	"Features for community members.",
				'roles'			=>	array('Contributor'),
				),
			),
		),

	'formats'	=>	array(
		'post'	=>	array(
			'standard'	=>	array(),
			),
		),

	'views'		=>	array(
		'enable'	=>	"",
		'grid'		=>	"",
		),

	);
    

?>