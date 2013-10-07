'use strict';

pwApp.directive('liveFeed', function() {
    return {
        restrict: 'A',
        templateUrl: jsVars.pluginurl+'/postworld/templates/directives/liveFeed.html',
        replace: true,
        controller: 'pwLiveFeedController',
        scope : {
        	
        }
    };
});

pwApp.controller('pwLiveFeedController',
    function pwLiveFeedController($scope, $location, $log, $attrs, $timeout, pwData) {
    	// Initialize
    	$scope.busy = false; 				// Avoids running simultaneous service calls to get posts. True: Service is Running to get Posts, False: Service is Idle    	
    	$scope.firstRun = true; 			// True until pwLiveFeed runs once. False for al subsequent pwScrollFeed
		$scope.args = {};
		$scope.args.feed_query = {};
		$scope.feed_query = {};
    	$scope.items = [];					// List of Post Items displayed in Scroller
    	$scope.args.feed_id = $attrs.liveFeed; // This Scope variable will propagate to all directives inside Live Feed
    	
    	// Get Data from Feed_Settings			   
    	// TODO move getting templates to app startup
    	pwData.pw_get_templates(null).then(function(value) {
		    // TODO should we create success/failure responses here?
		    // resolve pwData.templates
		    pwData.templates.resolve(value.data);
		    pwData.templatesFinal = value.data;
		    console.log('pwLiveFeedController templates=',pwData.templatesFinal);
		  });		      	    	
    	
    	
    	// the below is not needed since we are using args.feed_query from parent directive live-feed into load-panel 
    	 /*
		//Handle Emitted Arguments from LoadPanel Children
		$scope.$on("UPDATE_PARENT", function(event, message){
		   $log.info('Controller: pwLiveFeedController: ON:UPDATE_PARENT - EMIT Received: ',message);
		   $scope.args.feed_query = message;
		   $log.info('Controller: pwLiveFeedController: ON:UPDATE_PARENT - EMIT Received: args=',$scope.args.feed_query);
		   });
		
		$scope.$on("EXEC_PARENT", function(event, message){
		   $log.info('Controller: pwLiveFeedController: ON:EXEC_PARENT - EMIT Received: args=',$scope.args.feed_query);
		   $scope.pwLiveFeed();
		   });
		   
    	// Receive Form submission from Any Child Panel
		$scope.$on("SUBMIT_FORM_PARENT", function(event, message){
		   $log.info('Controller: pwLiveFeedController: ON:SUBMIT_FORM_PARENT - EMIT Received: ',message);
		   $scope.pwLiveFeed();
		   });
		 */ 
		$scope.$on("CHANGE_FEED_TEMPLATE", function(event, feedTemplateUrl){
		   $log.info('pwLiveFeedController: Event Received:CHANGE_FEED_TEMPLATE',feedTemplateUrl);
		   // Broadcast to all children
			$scope.$broadcast("pwLiveFeedController: Broadcast: FEED_TEMPLATE_UPDATE", feedTemplateUrl);		   
		   });
		   
   		$scope.getNext = function() {
			// If already getting results, do not run again.
			if ($scope.busy) {
				$log.info('pwLiveFeedController.getNext: We\'re Busy, Wait!');
				return;
				}
			$scope.busy = true;
			// if running for the first time, do this
			if ($scope.firstRun) {
				$log.info('pwLiveFeedController.getNext: Running for the first time');
				$scope.firstRun = false;
				$scope.pwLiveFeed();
			}
			// otherwise, do this
			else {
				// Run Search
				$log.info('pwLiveFeedController.getNext: Scrolling More');
				$scope.pwScrollFeed();				
			}
		};
		
		$scope.pwLiveFeed = function() {
			if (!$scope.args.feed_query)	$scope.args.feed_query = {};
	    	// identify the feed_settings feed_id
			
			$scope.items = {};
			// TODO set Nonce from UI
			pwData.setNonce(78);
        	pwData.pw_live_feed($scope.args).then(
				// Success
				function(response) {	
					$scope.busy = false;
					if (response.status === undefined) {
						console.log('response format is not recognized');
						return;
					}
					if (response.status==200) {
						// Reset Feed Data
						pwData.feed_data[$attrs.liveFeed] = {};
						// Insert Response in Feed Data
						pwData.feed_data[$attrs.liveFeed].feed_outline = response.data.feed_outline;						
						pwData.feed_data[$attrs.liveFeed].posts = response.data.post_data;						
						pwData.feed_data[$attrs.liveFeed].loaded = response.data.loaded;						
						// Count Length of loaded and feed_outline
						pwData.feed_data[$attrs.liveFeed].count_loaded = response.data.post_data.length;						
						pwData.feed_data[$attrs.liveFeed].count_feed_outline = response.data.feed_outline.length;
						// Set Feed load Status
						if (pwData.feed_data[$attrs.liveFeed].count_loaded == pwData.feed_data[$attrs.liveFeed].count_feed_outline) {
							pwData.feed_data[$attrs.liveFeed].status = 'all_loaded';													
						} else
							pwData.feed_data[$attrs.liveFeed].status = 'loaded';						
						
						$log.info('pwLiveFeedController.pw_live_feed Success',pwData.feed_data[$attrs.liveFeed]);
						
						// Clear Items from $scope 
						// $scope.posts = response.data.post_data;
						$scope.items = response.data.post_data;
						
						$scope.busy = false;							
						// TODO Do we need to return something?
						return response.data;
					} else {
						// handle error
						console.log('error',response.status,response.message);
						// TODO should we set busy to false when error is returned?
					}
					// return response.posts;
				},
				// Failure
				function(response) {
					$scope.busy = false;
					$log.error('pwLiveFeedController.pw_live_feed Failure',response);
					// TODO Show User Friendly Message
				}
			);
		  };
		$scope.pwScrollFeed = function() {
			// Check if all Loaded, then return and do nothing
			if (pwData.feed_data[$attrs.liveFeed].status == 'all_loaded') {
				$log.info('pwLiveFeedController.pwScrollFeed ALL LOADED - NO MORE POSTS');				
				return;
			};		
			// TODO do we need to set the loading status? or just use the busy flag?
			pwData.feed_data[$attrs.liveFeed].status = 'loading';
			
			
			$log.info('pwLiveFeedController.pwScrollFeed For',$scope.args.feed_id);
			// TODO set Nonce from UI
			pwData.setNonce(78);
			// console.log('Params=',$scope.args);
        	pwData.pw_get_posts($scope.args).then(
				// Success
				function(response) {
					$scope.busy = false;
					if (response.status === undefined) {
						console.log('response format is not recognized');
						// TODO Show User Friendly Error Message
						return;
					}
					if (response.status==200) {
						$log.info('pwLiveFeedController.pwScrollFeed Success',response.data);
						// Add Results to controller items						
						//$scope.posts = response.data;
						var newItems = response.data;
						for (var i = 0; i < newItems.length; i++) {
							// $log.info('Looping :',i,newItems[i].ID);
							//$scope.items.push(newItems[i]);
							// TODO check why when adding an item here, it affects also $scope.items !
							pwData.feed_data[$scope.args.feed_id].posts.push(newItems[i]);
							pwData.feed_data[$scope.args.feed_id].loaded.push(newItems[i].ID);							
							// $log.info('$scope.items has',$scope.items.length,' items');							
						  }
						// Update feed data with newly loaded posts
						$log.info('pwLiveFeedController.pwScrollFeed Success feed_data:',pwData.feed_data[$scope.args.feed_id]);
						return response.data;
						
					} else {
						// handle error
						console.log('error',response.status,response.message);
						// TODO Show User Friendly Error Message
					}
				},
				// Failure
				function(response) {
					$log.error('pwLiveFeedController.pwScrollFeed Failure',response);
					$scope.busy = false;
					// TODO Show User Friendly Error Message
				}
			);
		  };
    }
);