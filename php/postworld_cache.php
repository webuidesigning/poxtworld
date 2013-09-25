<?php
	function cache_all_points (){
		/*• Runs cache_user_points() and cache_post_points()
		return : cron_logs Object (add to table wp_postworld_cron_logs)*/
		$post_points_cron_log = cache_all_post_points();
		$user_points_cron_log = cache_all_user_points();
		
		
		return array($post_points_cron_log,$user_points_cron_log);
	}

	function cache_all_user_points(){
		/*• Cycles through all users with cache_user_points() method
		return : cron_logs Object (add to table wp_postworld_cron_logs)*/
		//??????????????????????
		
		//loop for all users: get calculate_user_points and user_post_points?
		
		
	}
	
	function cache_all_post_points() {
		/*
		 • Cycles through each post in each post_type with points enabled
		 • Calculates each post's current points with calculate_points()
		 • Stores points it in wp_postworld_meta 'points' column
		 • return : cron_logs Object (add to table wp_postworld_cron_logs)
		 */
		//Post_type = page/post, 
					 
		global $wpdb;
	
		$wpdb -> show_errors();
		
		//get wp_options Enable Points ( postworld_points ) field and get post types enabled for points - http://codex.wordpress.org/Function_Reference/get_option
		//TODO : Use wp_options_api http://codex.wordpress.org/Options_API
		
		global $pw_defaults;
		$points_options = $pw_defaults['points']; // array of post types
		
		//select all post ids of posts that their post types are enabled for points
		//$post_types_string = implode(', ',$points_options['post_types']);
		
		
		$post_types = $points_options['post_types'];
		//echo(json_encode($post_types).'<br>');
		$number_of_post_types = count($post_types);
		$cron_logs;
		//echo json_encode($cron_logs);
		$cron_logs['points']= array();
		//echo json_encode($cron_logs);
		//echo($number_of_post_types);
		for($i=0;$i<$number_of_post_types;$i++){
				$query = "select * from wp_posts where post_type ='".$post_types[$i]."'";
			//	echo("<br>".$query."<br>");
				$posts = $wpdb -> get_results($query);
				$current_cron_log_object = new cron_logs_Object();	
				
				$current_cron_log_object->time_start = date("Y-m-d H:i:s");// {{timestamp}}
				//update postworld_meta
				$current_cron_log_object->posts=count($posts);// {{number of posts}}
				$current_cron_log_object->type = 'points';
				$current_cron_log_object->query_id=$post_types[$i];// {{feed id / post_type slug}}
				$current_cron_log_object->query_vars = array();
				foreach ($posts as $row) {
					
					//check if already there is a record for this post , yes then calculate points
					//else create a row with zero points
					calculate_post_points($row->ID);
					// {{feed/post_type}}
					
					//$current_cron_log_object->query_vars[] ="";// {{ query_vars Object: use pw_get_posts  }}
				}
				
				$current_cron_log_object->time_end=date("Y-m-d H:i:s");// {{timestamp}}
				$current_cron_log_object->timer=(strtotime( $current_cron_log_object->time_end )-strtotime( $current_cron_log_object->time_start))*1000 ;// {{milliseconds}}
				$current_cron_log_object->timer_average = $current_cron_log_object->timer / $current_cron_log_object->posts;// {{milliseconds}}
				//echo(json_encode($current_cron_log_object));
				$cron_logs[$current_cron_log_object->type][] = $current_cron_log_object;
				//echo json_encode($cron_logs);
		}
	
		//echo json_encode(($cron_logs));
		add_new_cron_log($cron_logs);
		 
	}
	
	
	/*later*/
	function cache_all_comment_points(){
		/*• Cycles through all columns
		• Calculates and caches each comment's current points with cache_comment_points() method
		return : cron_logs Object (add to table wp_postworld_cron_logs)*/
		
		
		
		
	}
	
	function cache_all_rank_scores (){
		/*• Cycles through each post in each post_type scheduled for Rank Score caching
		• Calculates and caches each post's current rank with cache_rank_score() method
		return : cron_logs Object (add to table wp_postworld_cron_logs)*/
	
	/*	global $wpdb;
		$wpdb -> show_errors();
		
		global $pw_defaults;
		$rank_options = $pw_defaults['rank']; // array of post types
		$post_types = $points_options['post_types'];
		//echo(json_encode($post_types).'<br>');
		$number_of_post_types = count($post_types);
		$cron_logs;
		
		$cron_logs['rank']= array();
		
		for($i=0;$i<$number_of_post_types;$i++){
			$query = "select * from wp_posts where post_type ='".$post_types[$i]."'";
		//	echo("<br>".$query."<br>");
			$posts = $wpdb -> get_results($query);
			$current_cron_log_object = new cron_logs_Object();	
			
			$current_cron_log_object->time_start = date("Y-m-d H:i:s");// {{timestamp}}
			//update postworld_meta
			$current_cron_log_object->posts=count($posts);// {{number of posts}}
			$current_cron_log_object->type = 'rank';
			$current_cron_log_object->query_id=$post_types[$i];// {{feed id / post_type slug}}
			$current_cron_log_object->query_vars = array();
			foreach ($posts as $row) {
				calculate_rank_score($row->ID);
			}
			
			$current_cron_log_object->time_end=date("Y-m-d H:i:s");// {{timestamp}}
			$current_cron_log_object->timer=(strtotime( $current_cron_log_object->time_end )-strtotime( $current_cron_log_object->time_start))*1000 ;// {{milliseconds}}
			$current_cron_log_object->timer_average = $current_cron_log_object->timer / $current_cron_log_object->posts;// {{milliseconds}}
			
			$cron_logs[$current_cron_log_object->type][] = $current_cron_log_object;
			
	}

	echo json_encode(($cron_logs));
	 
	*/
	}
	
	/*later*/
	function cache_all_feeds (){
		/*• Run pw_cache_feed() method for each feed registered for feed caching in WP Options
		return : cron_logs Object (store in table wp_postworld_cron_logs)*/
	}
	
	function clear_cron_logs ( $timestamp ){
		/*  • Count number of rows in wp_postworld_cron_logs (rows_before)
			• Deletes all rows which are before the specified timestamp (rows_removed)
			• Count number of rows after clearing (rows_after)
			return : Object
			rows_before: {{integer}}
			rows_removed: {{integer}}
			rows_after: {{integer}}
			
			 $timestamp format : '2013-09-25 14:39:55'
		*/
		
		
		global $wpdb;
		$wpdb -> show_errors();
		
		$query = "select COUNT(*) FROM $wpdb->pw_prefix"."cron_logs";
		echo $query."<br>";
		$total_logs = $wpdb-> get_var($query);
		echo $total_logs."<br>";
		if($total_logs == 0){
			return array('rows_before'=> 0,'rows_removed'=> 0,'rows_after'=>0); 
		}
		else{
		
			$query ="DELETE FROM $wpdb->pw_prefix"."cron_logs WHERE time_end < '".$timestamp."'";
			echo $query."<br>";
			$deleted_rows = $wpdb->query($query);
			echo print_r($deleted_rows);
			if($deleted_rows === FALSE)
				$deleted_rows=0;
		
			return array('rows_before'=> $total_logs,'rows_removed'=> $deleted_rows,'rows_after'=>($total_logs - $deleted_rows));
		}
	}

    ////////////////  HELPER FUNCTIONS  //////////////////////
	function add_new_cron_logs($cron_logs_array){
		$cron_logs_count = count($cron_logs_array);
		
		for ($i=0; $i <$cron_logs_count ; $i++) {
			
			$query = "insert into " ;
			
		}
		
	}
    
  
?>