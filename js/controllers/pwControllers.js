/*____           _      ____            _             _ _           
 |  _ \ ___  ___| |_   / ___|___  _ __ | |_ _ __ ___ | | | ___ _ __ 
 | |_) / _ \/ __| __| | |   / _ \| '_ \| __| '__/ _ \| | |/ _ \ '__|
 |  __/ (_) \__ \ |_  | |__| (_) | | | | |_| | | (_) | | |  __/ |   
 |_|   \___/|___/\__|  \____\___/|_| |_|\__|_|  \___/|_|_|\___|_|   
																	
/*////////// ------------ POST CONTROLLER ------------ //////////*/                

'use strict';

/**
 * @ngdoc directive
 * @name postworld.directive:pwPost
 *
 * @description
 * Provides service functions to post templates.
 */
postworld.directive( 'pwPost', function(){
	return {
		restrict: 'AE',
		controller: 'postController',
		link: function( $scope, element, attrs ){

			// OBSERVE Attribute
			attrs.$observe('postRequiredFields', function( value ) {
				$scope.postRequiredFields = $scope.$eval( value );
			});

		}
	};
});


postworld.controller('postController',
	function($scope, $rootScope, $window, $sce, $pwData, $_, $log, $pwImages, $pw, $pwPosts, $timeout, $compile, $pwTemplatePartials ) {


	///// GET DEFAULT POST /////
	// If $scope.post doesn't exist
	if( _.isUndefined( $scope.post ) || _.isEmpty( $scope.post ) ){

		// First get the post from the pw globals 
		var globalPost = $_.get( $window, "pw.view.post" );
		if( globalPost != false )
			$scope.post = globalPost;

		// Use the window post
		if( !_.isUndefined( $window.post ) )
			$scope.post = $window.post;
		
		// If there's no post from the above sources
		if( _.isUndefined( $scope.post ) )
			$scope.post = {};
	}

	// POST META
	if( !$_.objExists( $scope, 'post.meta' ) )
		$scope.post.meta = {};

	// RUN CUSTOM POST FUNCTIONS
	// This function can be added to the $window object
	// For performing theme-specific per-post operations
	if( typeof $window.pwPostFunctions === "function" )
		$window.pwPostFunctions( $scope );

	///// TIME FUNCTIONS /////
	$scope.jsDateToTimestamp = function(jsDate){
		var dateObject = new Date(jsDate);
		return Date.parse(dateObject);
	}

	$scope.jsDate = function( timestamp ){
		return new Date( timestamp );
	}

	
	///// IMAGE FUNCTIONS /////
	// DEPRECIATED (use pw-background-image directive)
	$scope.backgroundImage = function( imageUrl, properties ){

		// Set the Image URL
		//var imageUrl = $scope.post.image[imageHandle].url;
		var style = { 'background-image': "url(" + imageUrl + ")" };

		// Add additional properties
		if( !_.isUndefined( properties ) ){
			angular.forEach( properties, function(value, key){
				style[key] = value;
			});
		}
		return style;
	}
	

	/**
	 * @description
	 * Selects an image tag based on the image dimensions.
	 */
	$scope.selectImageTag = function(){
		if( $_.objExists( $scope, "post.image.tags" ) == false )
			return false;
		var imageTags = $scope.post.image['tags'];
		return $pwImages.selectImageTag( imageTags );
	}

	///// SET ACTIVE CLASS /////
	$scope.setActiveClass = function( boolean ){
		//alert('test');
		return ( boolean ) ? "active" : "";
	}

	$scope.gotoUrl = function( url ){
		window.location = url;
	};

	$scope.embedProvider = function( linkUrl ){
		var provider;
		// TODO : Integrate with data in PHP method : pw_embed_url()
		var providers = {
			youtube: 	'youtube.com',
			vimeo: 		'vimeo.com',
			soundcloud: 'soundcloud.com',
			podbean: 	'podbean.com',
		};
		angular.forEach( providers, function( value, key ){
			if( $_.inString( value, linkUrl ) ){
				provider = key;
			}
		});
		return provider;
	}

	///// ACTION : POST UPDATED /////
	// Update the contents of post after Quick Edit
	$rootScope.$on('postUpdated', function(event, postId) {
		$log.debug( 'pw-post : $rootScope.$on( "postUpdated" ) : ', postId );
		if ( $scope.post.ID != postId ){
			return false;
		}
		///// HANDLE FEED POSTS /////
		// If the post has a feed
		if ( _.isString( $_.get( $scope, 'post.feed.id' ) ) ){
			// Call Update Feed Post
			$pwPosts.reloadFeedPost( $scope.post.feed.id, postId )
		}
		///// HANDLE STANDALONE POSTS /////
		else{
			var args = {
				post_id: postId,
				fields: 'all'
			};
			$pwData.getPost(args).then(
				// Success
				function(response) {
					if (response.status==200) {
						var post = response.data;
						// Convert Post Content into Bindable HTML
						if( !_.isUndefined( post.post_content ) &&
							_.isString(post.post_content) ){
							post.post_content = $sce.trustAsHtml(post.post_content);
						}
						$scope.post = post;
						// Update Classes
						if( !_.isUndefined( $scope.setClass ) )
							$scope.setClass();
					} else {
						// handle error
					}
				},
				// Failure
				function(response) {
					// $log.error('pwFeedController.pw_live_feed Failure',response);
					// TODO Show User Friendly Message
				}
			);
		}

	});

	///// ACTION : FEED POST UPDATED /////
	// This is run when the central feed post is updated with new data
	$scope.$on( 'feedPostUpdated', function( e, vars ){
		// If the post does not know it's own feed
		if( !$_.objExists( $scope, 'post.feed.id' ) )
			return false;
		// If the feed and post IDs are provided
		if( $scope.post.feed.id == vars.feedId &&
			$scope.post.ID == vars.postId ){
			// Get the post with the updated data from the feed
			var updatedPost = $pwPosts.getFeedPost( vars.feedId, vars.postId );
			// Update the local scope post with foreach to avoid two-way binding
			angular.forEach( updatedPost, function( value, key ){
				$scope.post[key] = value;
			});
			$log.debug( "pwPost : $ON : feedPostUpdated : ", vars );
		}
	});
	
	///// WATCH : REQUIRED FIELDS DIRECTIVE /////
	// When the post or the required fields changes
	// Check to make sure all the required fields are present
	$scope.$watchCollection( '[ postRequiredFields, post.ID ]', function(){
		if( $_.objExists( $scope, 'post.feed.id' ) &&
			$_.objExists( $scope, 'post.ID' ) &&
			$_.objExists( $scope, 'postRequiredFields' ) &&
			$_.get( $scope, 'postRequiredFields' ) != null ){
			
			$log.debug( "DIRECTIVE : pwPost -> postRequiredFields : ", $scope.postRequiredFields );
			$pwPosts.requiredFields({
					feedId: $scope.post.feed.id,
					postId: $scope.post.ID,
					fields: $scope.postRequiredFields
				});

		}	
	});
	
	///// ACTION : LOAD POST DATA /////
	$scope.$on('loadPostData', function(event, post_id) {
		if( post_id == $_.get( $scope, 'post.ID' ) )
			$scope.loadPost( post_id );
	});


	///// GET TEMPLATE PARTIAL /////
	// Alias of the template partials
	$scope.getTemplatePartial = function( vars ){
		return $pwTemplatePartials.get( vars );
	}

	///// DEV /////
	//$pwPosts.mergeFeedPost( $scope.post.feed.id, $scope.post.ID, {post_date:"NOW"} );
	//$log.debug( $pwPosts.getFeedPost( $scope.post.feed.id, $scope.post.ID ) );
	
	// Make API for status. $scope.meta.status = [ 'loading', 'loadingRequiredFields' ], = [ 'done' ]
	// Make core service functions to set, and check for status

	

});


'use strict';
postworld.directive( 'pwFeedPost', function(){
	return {
		restrict: 'AE',
		controller: 'pwFeedPostCtrl',
		link: function( $scope, element, attrs ){
			// OBSERVE Attribute
			//attrs.$observe('postRequiredFields', function( value ) {
			//	$scope.postRequiredFields = $scope.$eval( value );
			//});
		}
	};
});

postworld.controller('pwFeedPostCtrl',
	function($scope, $rootScope, $window, $sce, $pwData, $_, $log, $pwImages, $pw, $pwPosts, $timeout ) {

	///// ACTION : POST UPDATED /////
	// Update the contents of post after Quick Edit
	$rootScope.$on('postUpdated', function(event, postId) {
		if ( $scope.post.ID != postId ){
			return false;
		}
		$log.debug( 'pw-feed-post : $rootScope.$on( "postUpdated" ) : ', postId );
		// If the post has a feed
		if ( _.isString( $_.get( $scope, 'post.feed.id' ) ) ){
			// Call Update Feed Post
			$_.clobber( 'postUpdated_' + postId, 100, function(){
				$pwPosts.reloadFeedPost( $scope.post.feed.id, postId )
			});
			
		}

	});

	///// ACTION : FEED POST UPDATED /////
	// This is run when the central feed post is updated with new data
	$scope.$on( 'feedPostUpdated', function( e, vars ){

		//$_.clobber( 'feedPostUpdated_' + vars.postId, 100, function(){
			// If the post does not know it's own feed
			if( !$_.objExists( $scope, 'post.feed.id' ) )
				return false;
			// If the feed and post IDs are provided
			if( $scope.post.feed.id == vars.feedId &&
				$scope.post.ID == vars.postId ){
				$log.debug( "$ON : feedPostUpdated : ", vars );
				// Get the post with the updated data from the feed
				var updatedPost = $pwPosts.getFeedPost( vars.feedId, vars.postId );
				// Update the local scope post with foreach to avoid two-way binding
				angular.forEach( updatedPost, function( value, key ){
					$scope.post[key] = value;
				});
			}

		//});

	});


});



postworld.directive( 'pwCompile', function(  $log, $compile, $sce ){
	return {
		restrict: 'AE',
		//controller: 'postController',
		link: function( $scope, element, attrs ){
			
			$log.debug( "PW COMPILE ELEMENT : ", element );

			// $compile( $sce.trustAsJs( element[0].innerHTML ) )($scope);

			//$compile( element.contents );


			// OBSERVE Attribute
			/*
			attrs.$observe('postRequiredFields', function( value ) {
				$scope.postRequiredFields = $scope.$eval( value );
			});
			*/
		}
	};
});







