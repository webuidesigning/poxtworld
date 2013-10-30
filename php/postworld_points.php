<?php
	
	
	class set_post_points_Output {
		public $points_total = 0;
		public $points_added = 0;
	}
	
	class get_user_votes_report_Output {
		public $total_posts = 0;
		public $total_points = 0;
		public $average_points = 0.0;
	}
	
	class get_user_votes_on_posts_Output{
		 public $post_id =0;
		 public $votes =0;
		 public $time =null;
	}

	/////////////// POST POINTS  ///////////////////
	function get_post_points($post_id) {
		/*
		• Get the total number of points of the given post from the points column in 'wp_postworld_meta' table
		return : integer
		*/
		
		global $wpdb;
		$wpdb -> show_errors();
	
		$query = "SELECT * FROM " . $wpdb->pw_prefix.'post_meta' . " Where post_id=" . $post_id;
		//echo ($query);
		$postPoints = $wpdb -> get_row($query);
		if ($postPoints != null)
			return $postPoints -> post_points;
		else
			return 0;
	}
	
	
	function calculate_post_points($post_id) {
		/*
		• Adds up the points from the specified post, stored in ".$wpdb->pw_prefix.'post_points'."
		• Stores the result in the points column in wp_postworld_meta
		return : integer (number of points)
		*/
		
		global $wpdb;
		$wpdb -> show_errors();
	
		//first sum points
		$query = "select SUM(post_points) from ".$wpdb->pw_prefix.'post_points'." where post_id=" . $post_id;
		$points_total = $wpdb -> get_var($query);
		//echo("\npoints cal" . $points_total);
		if($points_total==null || $points_total =='') $points_total=0;
		
		return $points_total;
	
	}


	function cache_post_points ( $post_id ){
		/*• Calculates given post's current points with calculate_post_points()	
		• Stores points it in wp_postworld_post_meta table in the post_points column
		return : integer (number of points)*/
		$total_points = calculate_post_points($post_id);
		//update wp_postworld_meta
		global $wpdb;
		$wpdb -> show_errors();
	
		$query = "update ".$wpdb->pw_prefix.'post_meta'." set post_points=" . $total_points . " where post_id=" . $post_id;
		$result =$wpdb -> query($query);
		
		if ($result === FALSE || $result === 0){
			//insertt new row for this post in post_meta, no points was added
			
			/*1- get post data
			 2-  Insert new record into post_meta
			 */
			add_record_to_post_meta($post_id,$total_points);
			
		}
		return $total_points;
	}
	
	/////////////// USER POINTS  ///////////////////
	function get_user_posts_points ( $user_id ){
		/*
		 * • Get the number of points voted to posts authored by the given user
		 * • Get cached points of user from wp_postworld_user_meta table post_points column
			return : integer (number of points)
		 
		 * */
		global $wpdb;
		$wpdb -> show_errors();
	
		$query ="Select post_points from ".$wpdb->pw_prefix.'user_meta'." where user_id=".$user_id;
		$user_votes_points = $wpdb -> get_var($query);
		if ($user_votes_points != null) {
			//echo("total_points:" . $total_points);
			return $user_votes_points;
		} else
			return 0;
	
		
	}
	
	function calculate_user_posts_points( $user_id ){
		/*	• Adds up the points voted to given user's posts, stored in wp_postworld_post_points
			• Stores the result in the post_points column in wp_postworld_user_meta
		return : integer (number of points)*/
	/*	$total_user_points = get_user_points_voted_to_posts($user_id);
		global $wpdb;
		$wpdb -> show_errors();
		
		$query = "update ".$wpdb->pw_prefix.'user_meta'." set post_points=".$total_user_points." where user_id=".$user_id;
		$result = $wpdb->query($query);
		if($result === FALSE){
			//insert new Record

						
		}
		return $total_user_points;*/
		
		$total_user_points_breakdown = get_user_points_voted_to_posts($user_id,TRUE);
		$total_user_points =0;
		$post_points_meta = array("post_type"=> array ("post"=> 0,"link" =>0,"blog" =>0, "event" => 0));
		
		
		foreach ($total_user_points_breakdown as $row) {
			$total_user_points+= $row->total_points;
			$post_points_meta['post_type'][$row->post_type] = $row->total_points;
		}
		
		global $wpdb;
		$wpdb -> show_errors();
		
		$query = "update ".$wpdb->pw_prefix.'user_meta'." set post_points=".$total_user_points.", post_points_meta='".json_encode($post_points_meta)."' where user_id=".$user_id;
		$result = $wpdb->query($query);
		
		return array("total"=>$total_user_points,$post_points_meta);
		
	}
	
	function cache_user_posts_points ( $user_id ){
	/*  • Runs calculate_user_post_points() Method
		• Caches value in post_points column in wp_postworld_user_meta table
		return : integer (number of points)*/
		
		return calculate_user_posts_points($user_id);
	}
	
	///////////// COMMNET POINTS ////////////////////
	
	/*Later*/
	function get_user_comments_points ( $user_id ){
		/*• Get the number of points voted to comments authored by the given user
		  • Get cached points of user from wp_postworld_user_meta table comment_points column
		return : integer (number of points)*/
		
		global $wpdb;
		$wpdb -> show_errors();
		
		$query ="select comment_points from ".$wpdb->pw_prefix.'user_meta'." where user_id=".$user_id;
		$total_points = $wpdb -> get_var($query);
		
		if($total_points==null)
			$total_points=0;
		
		return $total_points;

	}
	/*Later*/
	function calculate_user_comments_points ( $user_id ){
		/*• Adds up the points voted to given user's comments, stored in wp_postworld_comment_points
		  • Stores the result in the post_points column in wp_postworld_user_meta
		return : integer (number of points)*/
		
		global $wpdb;
		$wpdb -> show_errors();		
		
		//$total_comment_points = get_user_comments_points($user_id);
		$query ="select SUM(comment_points) from ".$wpdb->pw_prefix.'comment_points'." where comment_author_id=".$user_id;
		$total_comment_points = $wpdb -> get_var($query);
		
		if($total_comment_points==null)
			$total_comment_points=0;
		
		$query = "update ".$wpdb->pw_prefix.'user_meta'." set comment_points=".$total_comment_points." where user_id=".$user_id;
		$wpdb->query($query);
		
		return $total_comment_points;
	}
	
	 
	 /*Later*/
	function cache_user_comments_points ( $user_id ){
		/*• Runs calculate_user_comment_points() Method
		  • Caches value in comment_points column in wp_postworld_user_meta table
		return : integer (number of points)*/
		return calculate_user_comments_points($user_id);
		
		
	}
	function set_comment_points($comment_id,$set_poinst){
		/*Description

		Wrapper for set_points() Method for setting comment points
		Parameters
		
		$comment_id : integer
		
		$set_points : integer
		
		Process
		
		Run set_points( 'comment', $post_id, $set_points )
		return : Array (same as set_points() )
		 * */	
		return set_points( 'comment', $post_id, $set_points );
		
	}
	
	/////////////// GENERAL POINTS  ///////////////////
	
	function get_post_points_meta($user_id){
		global $wpdb;	
		$wpdb-> show_errors();
		$query = "select post_points_meta from $wpdb->pw_prefix"."user_meta where user_id=".$user_id;
		return $wpdb -> get_var($query);
	}
	
	
	function cache_post_points_meta($user_id, $post_points_meta_object){
		global $wpdb;	
		$wpdb-> show_errors();
		$query = "update  $wpdb->pw_prefix"."user_meta set post_points_meta ='".$post_points_meta_object ."' where user_id=".$user_id;
		//echo $query;
		$wpdb ->query($query);		
	}

	function update_post_points_meta($user_id,$post_id, $update_points){
		//echo ("<br> update_points in cache " .$update_points);
		$post_type = get_post_type( $post_id ); // check post_type of given post
		$post_points_meta = get_post_points_meta($user_id);
		
		if($post_points_meta == null)
			$post_points_meta = array("post_type"=> array ("post"=> 0,"link" =>0,"blog" =>0, "event" => 0));
		else
			$post_points_meta = json_decode( $post_points_meta,true ); // decode from JSON
		
		$post_type_points = $post_points_meta['post_type'][$post_type]; // Get the number of points in given post_type
		$post_points_meta['post_type'][$post_type] = ((int)$post_type_points + $update_points); // Add new points
		$post_points_meta = json_encode($post_points_meta); // encode back into JSON
		
		//echo $post_points_meta;
		// Write new post_points_meta object to user_meta table
		cache_post_points_meta($user_id, $post_points_meta);
	} 
	
	function set_points ( $point_type, $id, $set_points ){
		
		/*
		 Description
		A meta function for set_post_points() and set_comment_points()
		
		 * Parameters
		
		$point_type : string
		---------------
		 * Which type of points to set
		 * options :
			post - Will set points for a post_id
			comment - Will set points for comment_id
	 
		$id : integer
		--------------
		 * The post_id or comment_id
		 
		
		$set_points : integer
		----------------
		 * How many points to set for the user
		
	 
	Process
		
		-Get the User ID
		-Get the user's vote power : get_user_vote_power()
				If $set_points is greater than the user's role vote_points , reduce to vote_points
		
		 */
		 $user_id = get_current_user_id();
		 $user_vote_power = get_user_vote_power($user_id);
		 if ( abs($user_vote_power) < abs($set_points) )
    		$set_points = $user_vote_power * (abs($set_points)/$set_points); 
	 
	 
	 	
	 
	 /*
	 	$update_points = $set_points - $old_user_points; // calculate the difference in points
		$old_post_points = get_post_points($post_id); // get previous points of the post
		$new_post_points = $old_post_points + $update_points; // add the updated points	
		$output -> points_added = $update_points;*/
	
		  
		 /*
		-Define the table and column names to work with :
			Points Table : post_points / comment_points
			ID Column : post_id / comment_id
			Points Column : post_points / comment_points
		
		 */
		global $wpdb;	
		$wpdb-> show_errors();
		
		  $table_name;
		  $points_column='' ;
		  $column_id ='';
		  if($point_type == 'post') {
		  	$table_name ="$wpdb->pw_prefix"."post_points";
		  	$points_column = 'post_points';
			$column_id ='post_id';  
			
		  }
		  else{
		  	$table_name ="$wpdb->pw_prefix"."comment_points";
		  	$points_column = 'points';
			$column_id ='comment_id';  
		  }
		  
		  
		 
		  /*
		-Check if row exists in Points Table for the given ID Column and User ID
			If no row , add row to coorosponding Points Table
			If row exists , update the row
			If $set_points = 0 , delete row
		  */
		    
		//$query = "SELECT * FROM ".$table_name." Where $column_id=" . $id . " and user_id=" . $user_id;
		//$points_row = $wpdb -> get_row($query);
		
		if($point_type =='post') 
			$points_row = does_post_points_row_exist($id, $user_id);
		else 
			$points_row = does_comment_points_row_exist($id, $user_id);
		
		
		
		//print_r($points_row);
		
		if($points_row){ //update
			$points_row =  get_object_vars($points_row);
			$old_points = $points_row[$points_column];
			$update_points =  $set_points - $old_points;
			
			//print_r($points_row);
			//echo("oldPOints:".$old_points);
			//echo("update_points:".$update_points);	
		}
		
		//check if it is required to delete the row and update cashed points (triggers)
		if ($set_points == 0) {
			if($points_row){
				$query = "delete from ".$table_name." where $column_id=" . $id . " and user_id=" . $user_id;
				$wpdb -> query($query);
			}else{
				$update_points = 0;
			}
			
		} else { //set points not 0
			
			if($points_row){
				if($point_type =='post') {
					update_post_points($id, $user_id, $set_points);
					//$points_total = cache_post_points($id);
				}
				else{
					update_comment_points($id, $user_id, $set_points);
					//$points_total = cache_comment_points($id);
				}
			}
			else{
			//	echo "row not founddd";
				if($point_type =='post') {
				//	echo "postt";
					add_record_to_post_points($id, $user_id, $set_points);
					//$points_total = cache_post_points($id);
				}else{
					//echo "commment";
					add_record_to_comment_points($id, $user_id, $set_points);
					//$points_total = cache_comment_points($id);
				}
				$update_points= $set_points;
			}
			
		}
		if($point_type =='post') {
				
				$points_total = cache_post_points($id);
		}
		else{ 
				$points_total = cache_comment_points($id);
		}
		
		 
		/*
		-Add Unix timestamp to time column in Points Table
		
		-If $point_type == post , Update cache in Post Meta table
		
			If row doesn't exist for given post_id in Post Meta table, create new row
			Update cached post_points row in Post Meta table directly if there is a change in points
		-Update cache in User Meta table, under the post/comment author, under coorosponding Points Column
		
			$point_type == post update the value of post_points_meta column in user_meta table
		
		Anatomy of post_points_meta column JSON object in user_meta table : see Database Structure Document.
		
		
		return : Array
		
		array(
		     'point_type' => {{$point_type}} // (post/comment) << NEW
		     'user_id' => {{$user_id}} // (user ID) << NEW
		     'id' => {{$id}} // (post/comment ID) << NEW
		
		     'points_added' => {{integer}} // (points which were successfully added)
		 * 
		     'points_total' => {{integer}} // (from wp_postworld_meta)
		)
		TODO:
		
		Check that user has not voted too many times recently <<<< Concept method <<< PHONG
			-Use post_points_meta to store points activity << PHONG
		 * 
		 */
		 
		 	
		$output = array(
		     'point_type' =>$point_type,// (post/comment) << NEW
		     'user_id' => $user_id, // (user ID) << NEW
		     'id' => $id, // (post/comment ID) << NEW
		
		     'points_added' => $update_points, // (points which were successfully added)
		
		     'points_total' => $points_total // (from wp_postworld_meta)
		);
		 
		return $output;
	}
	
	function add_record_to_post_points($post_id,$user_id, $points){
			global $wpdb;
			$wpdb -> show_errors();
			$query = "insert into ".$wpdb->pw_prefix.'post_points'." values(" . $post_id . "," . $user_id . "," . $points . ",null)";
			$wpdb->query($query);
	}
	
	function update_post_points($post_id, $user_id, $points){
			global $wpdb;
			$wpdb -> show_errors();
			$query="update $wpdb->pw_prefix"."post_points set post_points=".$points ." where post_id=".$post_id." and user_id=".$user_id;
			$wpdb->query($query);
	}
	function update_comment_points($comment_id, $user_id, $points){
			global $wpdb;
			$wpdb -> show_errors();
			$query="update $wpdb->pw_prefix"."comment_points set points=".$points ." where comment_id=".$comment_id." and user_id=".$user_id;
			$wpdb->query($query);
	}
	
	function does_post_points_row_exist($post_id,$user_id){
			global $wpdb;
			$wpdb -> show_errors();
			$query = "SELECT * FROM ".$wpdb->pw_prefix.'post_points'." Where post_id=" . $post_id . " and user_id=" . $user_id;
			$points_row = $wpdb -> get_row($query);
			
			if($points_row) return $points_row;
			else return FALSE;
	}
	
	function add_record_to_comment_points($comment_id,$user_id, $points){
			global $wpdb;
			$wpdb -> show_errors();
			$comment_author = get_comment_author_id($comment_id);
			//echo "<br>add_record_to_comment_points";
		//	$query = "insert into ".$wpdb->pw_prefix.'comment_points'." values(" . $comment_id . ",".get_comment_post_id($comment_id)."," . $user_id . ",".$comment_author."," . $points . ",null)";
			
			$query="INSERT INTO `$wpdb->pw_prefix"."comment_points`
					(`comment_id`,
					`user_id`,
					`comment_post_id`,
					`comment_author_id`,
					`points`,
					`time`)
					VALUES
					($comment_id,
					$user_id,".
					get_comment_post_id($comment_id).",
					$comment_author,
					$points,
					null)";
								
		//	echo "<br> teeeeeeeeeeeeeeeeeet $query";
			$wpdb->query($query);
	}
	
	function does_comment_points_row_exist($comment_id,$user_id){
			global $wpdb;
			$wpdb -> show_errors();
		//	echo "<br>does_comment_points_row_exist";
			$query = "SELECT * FROM ".$wpdb->pw_prefix.'comment_points'." Where comment_id=" . $comment_id . " and user_id=" . $user_id;
		//	echo $query;
			$commentPointsRow = $wpdb -> get_row($query);
		//	print_r($commentPointsRow);
			if($commentPointsRow) return $commentPointsRow;
			else return FALSE;
	}
	
	function set_post_points($post_id, $set_points) {
		/*
		Description

		Wrapper for set_points() Method for setting post points
		Parameters
		
		$post_id : integer
		
		$set_points : integer
		
		Process
		
		Run set_points( 'post', $post_id, $set_points )
		return : Array (same as set_points() )
		 */
		
		
		return set_points( 'post', $post_id, $set_points );
		
		/*$user_id = get_current_user_id();
		
		global $wpdb;
		$wpdb -> show_errors();
		$points_total = 0;
		$output = new set_post_points_Output();
		
		$old_user_points = has_voted_on_post($post_id,$user_id); // get previous points user has voted on points
		//echo "<br>Old user points = ".$old_user_points;
		$vote_points;
		$update_points ;
		$old_post_points;
		$new_post_points;	
		
		
	
		//get row to check if found or not, also because when is going to be deleted to return reduced points
		$query = "SELECT * FROM ".$wpdb->pw_prefix.'post_points'." Where post_id=" . $post_id . " and user_id=" . $user_id;
		$postPointsRow = $wpdb -> get_row($query);
	
		//check if it is required to delete the row and update cashed points (triggers)
		if ($set_points == 0) {
			if ($old_user_points != 0) {
				$query = "delete from ".$wpdb->pw_prefix.'post_points'." where post_id=" . $post_id . " and user_id=" . $user_id;
				$wpdb -> query($query);
				$update_points = -($old_user_points);
				$points_total = calculate_post_points($post_id);
			} else {
				//nothing was done.
				$points_total = get_post_points($post_id);
				$update_points = 0;
			}
	
		} else { //set points not 0
			$vote_points = get_user_vote_power($user_id);
			if ( abs($vote_points) < abs($set_points) )
    			$set_points = $vote_points * (abs($set_points)/$set_points); 
	 
			$update_points = $set_points - $old_user_points; // calculate the difference in points
			$old_post_points = get_post_points($post_id); // get previous points of the post
			$new_post_points = $old_post_points + $update_points; // add the updated points	
			$output -> points_added = $update_points;
		
			//echo "<br>set_points = ".$set_points;
			//echo "<br>vote_points = ".$vote_points;
			//echo "<br>update_points = ".$update_points;
			//echo "<br>old_post_points = ".$old_post_points;
			//echo "<br>new_post_points = ".$new_post_points;
			
			
			if ($old_user_points != 0) {
				//echo ('NOTT NULL');
				// check if user can add more points from wp-options
				$query = "Update ".$wpdb->pw_prefix.'post_points'." set post_points=". $new_post_points . " Where post_id=" . $post_id . " and user_id=" . $user_id;
				//$output -> points_added = $update_points;
				//echo $query;
				$wpdb -> query($query);
				$points_total = calculate_post_points($post_id);
	
			} else {// row not found, insert
				$query = "insert into ".$wpdb->pw_prefix.'post_points'." values(" . $post_id . "," . $user_id . "," . $update_points . ",null)";
				//echo($query);
				//$output -> points_added = $update_points;
				//echo "<br>".$query;
			
				$wpdb -> query($query);
				$points_total = calculate_post_points($post_id);
			}
	
		}
		
		
		update_post_points_meta($user_id,$post_id, $update_points);
		
		//prepare output format
		//echo("<br>total Pooints:".$points_total);
		//echo("<br>added points:" .$update_points);
		$output ->points_added = $update_points;
		$output -> points_total = $points_total;
		return $output;*/
	}
	
	
	
	
	function has_voted_on_post($post_id, $user_id) {
		/*
		 • Check wp_postworld_points to see if the user has voted on the post
		 • Return the number of points
		 return : integer
		 */
		
		global $wpdb;
		$wpdb -> show_errors();
	
		$query = "SELECT * FROM ".$wpdb->pw_prefix.'post_points'." Where post_id=" . $post_id . " and user_id=" . $user_id;
		$postPointsRow = $wpdb -> get_row($query);
		//echo "<br>".$query;
		if ($postPointsRow != null)
			return $postPointsRow -> post_points;
		else
			return 0;
	
	}
	
	/*Later*/
	function has_voted_on_comment ( $comment_id, $user_id ){ 
	/*	• Check wp_postworld_comment_points to see if the user has voted on the comment
		• Return the number of points voted
		return : integer*/
		global $wpdb;
		$wpdb -> show_errors();
	
		$query = "SELECT * FROM ".$wpdb->pw_prefix.'comment_points'." Where comment_post_id=" . $comment_id . " and user_id=" . $user_id;
		$commentPointsRow = $wpdb -> get_row($query);
		//echo "<br>".$query;
		if ($commentPointsRow != null)
			return $commentPointsRow -> points;
		else
			return 0;
}
	
	
	function get_user_points_voted_to_posts($user_id, $break_down=FALSE) {
		/*
		 * 
		 * Parameters: $user_id 
		 * 			   $break_down =FALSE
		 * If $break_down == false then
		 • Get array of all posts by given user
		 • Get points of each post from wp_postworld_post_meta
		 • Add all the points up
		 return : integer (number of points)
		 * If $break_down == true 
		 Get total points voted to posts authored by the given user grouped by post_type

		output :

		[
         {"post_id":"13","author_id":"1","total_points":"10","post_type":"post"},
         {"post_id":"19","author_id":"1","total_points":"10","post_type":"link"}
		]*/
	
		global $wpdb;
		$wpdb -> show_errors();
	
		if($break_down === FALSE){
			//SELECT * FROM wp_postworld_a1.get_user_points_view;
			$query = "SELECT SUM(post_points) as total_points FROM ".$wpdb->pw_prefix.'post_meta'." Where author_id=" . $user_id;
			$total_points = $wpdb -> get_var($query);
			if ($total_points != null) {
				//echo("total_points:" . $total_points);
				return $total_points;
			} else
				return 0;
		}else{
			
			$query ="select post_id,author_id ,sum(post_points) as total_points, wp_posts.post_type from $wpdb->pw_prefix"."post_meta left join wp_posts on (wp_posts.ID = $wpdb->pw_prefix"."post_meta.post_id AND wp_posts.post_author = $wpdb->pw_prefix"."post_meta.author_id) group by post_type ";	
			//echo $query;
			$user_votes_points_breakdown = $wpdb -> get_results($query);
			//echo json_encode($user_votes_points_breakdown);
			return $user_votes_points_breakdown;	
		}
	}
	
	
	function get_user_votes_on_posts($user_id) {
		/*
		 • Get all posts which user has voted on from wp_postworld_points
		 return : Object
		 #for_each
		 post_id : {{integer}}
		 votes : {{integer}}
		 time : {{timestamp}}
		 */
	
		global $wpdb;
		$wpdb -> show_errors();
	
		$query = "SELECT * FROM ".$wpdb->pw_prefix.'post_points'." Where user_id=" . $user_id;
		//echo($query);
		$user_votes_per_post = $wpdb -> get_results($query);
	
		$output = array();
		foreach ($user_votes_per_post as $row) {
			$singlePost = new get_user_votes_Output();
			$singlePost->post_id = $row->id;
			$singlePost->votes = $row->post_points;
			$singlePost->time = $row->time;
			
			//echo(serialize($singlePost));
			$output[] = $singlePost;
			
			
		}
		//echo(json_encode($output));
		 return $output;
		 
	}
	
	
	function get_user_votes_report($user_id) {
		/*
		 • Returns the 'recent/active' points activity of the user
		 • Get all posts which user has recently voted on from wp_postworld_points ( total_posts )
		 • Add up all points cast (total_points)
		 • Generate average (total_points/total_posts)
		 return : Object
		 total_posts: {{integer}} (number of posts voted on)
		 total_points: {{integer}} (number of points cast by up/down votes)
		 average_points: {{decimal}} (average number of points per post)
		 */
	
		global $wpdb;
		$wpdb -> show_errors();
	
		$query = "SELECT SUM(post_points) as total_points, Count(*) as total_posts FROM ".$wpdb->pw_prefix.'post_points'." Where user_id=" . $user_id;
		$total_points = $wpdb -> get_results($query);
	
		foreach ($total_points as $row) {
		//	echo $row -> total_points . ",";
		//	echo $row -> total_posts . ",";
		//	echo($row -> total_points / $row -> total_posts);
	
			$output = new get_user_votes_report_Output();
			$output -> total_posts = $row -> total_posts;
			$output -> total_points = $row -> total_points;
			$output -> average_points = ($row -> total_points / $row -> total_posts);
			return $output;
		}
	
	}
	
	function get_user_vote_power ( $user_id ){
		/*
		 	• Checks to see user's WP roles with get_user_role()
			• Checks how many points the user's role can cast, from wp_postworld_user_roles table, under vote_points column
			return : integer (the number of points the user can cast)
		 */
	
		global $wpdb;
		$current_user_role_output = get_user_role($user_id);
		//echo(json_encode($current_user_role_output));

		if(gettype($current_user_role_output) == "array") {
			//echo('arraaaaaay');
			$current_user_role = $current_user_role_output[0];
		}
		else if (gettype($current_user_role_output) == "string"){
			//echo('string<br>');
			$current_user_role = $current_user_role_output;
			//echo("role :" .json_encode($current_user_role));
		}
		
		$query = "select vote_points from ".$wpdb->pw_prefix.'user_roles'." where user_role='".$current_user_role."'";
		$vote_points = $wpdb->get_var($query);
		
		if($vote_points !=null) return $vote_points;
		else 0;
		
	}

	function can_user_add_more_points($user_id,$current_number_of_points,$added_points){
		$user_allowed_points = get_user_vote_power($user_id);
		if($user_allowed_points >=( $current_number_of_points + $added_points))
		return true;
		else return false;
	}
	
	function does_post_meta_exist($post_id){
		global $wpdb;	
		$wpdb-> show_errors();
		
		 
		$query = "select * from ".$wpdb->pw_prefix."post_meta where post_id=".$post_id;
		$row = $wpdb->get_row($query);
		
		if($row ==null){
			return false;
		}
		else return true;
	}
	function add_record_to_post_meta($post_id, $points=0,$rank_score=0,$favorites=0,$post_shares=0){
		/*
		 This function gets all post data and inserts a record in wp_postworld_post_meta table
		 * Parameters:
		 * -post_id
		 * -optional : points (default value=0)
		 *			   rankd_score(default value=0)
		 * 				favorites(default value=0)
		 * optional parameters are not sent if the function adds a new row
		 */
		
			
	
		global $wpdb;	
		$wpdb-> show_errors();
		
		 
		
		
		if(!does_post_meta_exist($post_id)){
			$format = get_post_format();
			if ( false === $format )
			$format = 'standard';
			
			$post_data= get_post( $post_id, ARRAY_A );
			//echo json_encode($post_data);
			$query = "insert into ".$wpdb->pw_prefix.'post_meta'." values("
					.$post_id.","
					.$post_data['post_author'].","
					."'post_class'"."," //TODO
					."'".$format."',"
					."'".$post_data['guid']."',"
					.$points.","
					.$rank_score.","
					.$favorites.","
					.$post_shares
					.")";
					
			//echo $query."<br>";
			$wpdb -> query($query);
		}
	}
	
	

?>