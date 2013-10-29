'use strict';
/*
 * Angular Tree Implementations
 * recursive templates https://github.com/eu81273/angular.treeview
 * recursive directive https://gist.github.com/furf/4331090
 * lazy loading http://blog.boxelderweb.com/2013/08/19/angularjs-a-lazily-loaded-recursive-tree-widget/
 * 
 * Tasks
 * Get Comments Service [OK]
 * Create Recursive Comment Structure [OK]
 * 	- with Maximimze/minimize [OK] 
 *  - +/- Karma Points [OK - just add ajax Functions]
 * Create Comment Tempalte [OK]
 * Add Comment/Reply [OK]
 * Edit Comment [OK]
 * Delete Comment [OK]
 * Toggle Reply/Delete/Edit [OK]
 * Flag comment [Doesn't exist]
 * Remove ngAnimate to enhance performance! [OK]
 * Bind HTML/Sanitize Content/Format Content [OK]
 * NOTE: Karma points are always zero, even if updated in the wp_comments table, it seems they need to be updated somewhere else
 *  - Maximize/Minimize based on Points [OK] = Note that we are currently using a random function to generate points - for testing purposes
 * performance Tuning of initial loading time [OK] [using chrome timeline - less than 1 second to render - after loading data from server]
 * Create a Comment on the Top Level [OK]
 * OrderBy is moved to query field and underscore is removed, just to match the arguments format of the get_comments function
 * Sort Options and refresh based on sorting [OK]
 * Show as Tree or linear [OK]
 * Show Loading Icon while loading data [OK]
 * Permissions, you can only edit and delete your own comments [OK]
 * Show More - when exceeding 600 characters show only the first 4 lines of the comment [OK]
 * encapsulate in a simple directive with a template [OK]
 * 
 *  - show control bar on hover only
 *  - show icons for control bar
 *  - highlight selected function of the control bar [reply, edit, delete, etc...]
 *  
 * add Timezone to date [OK]
 * 
 * when adding new comments they should be open by default?
 * Can we use the html returned directly?

 * Cleanup UI
 * Cleanup CSS
 * Animation? Performance Limitations
 * If we can show the comments with tree=false, what happens when users reply? it will become a tree? no?
 * 
 * Future: Lazy Loading/Load More/Load on Scrolling, etc...
 * Performance http://tech.small-improvements.com/2013/09/10/angularjs-performance-with-large-lists/
 */

postworld.directive('ngShowMore', function ($timeout,$animate) {
	//console.log('at least we got in directive');
	function link(scope, element, attrs) {
      		scope.child.showMore = false;
      		scope.child.tall = false;
      		//console.log(scope.child.comment_ID,scope.child.comment_content.length);
			if (scope.child.comment_content.length>600) {
	      			scope.child.showMore = true;
	      			scope.child.tall = true;				
			} ;
      		//  this needs to perform better, so it is replaced with the above function
      		/*
	      $timeout(function(){
	      		scope.child.showMore = false;
	      		scope.child.tall = false;
	      		scope.child.height = element.height();
	      		if (scope.child.height>60) { 
	      			scope.child.showMore = true;
	      			scope.child.tall = true;
	      			} 
    		});
    		*/
    		       
	    }	
  return {
    restrict: 'A',
    link: link,
  };
});


postworld.directive('loadComments', function() {
    return {
        restrict: 'A',
        // DO not set url here and in nginclude at the same time, so many errors!
        // templateUrl: jsVars.pluginurl+'/postworld/templates/comments/comments-default.html',
        replace: true,
        controller: 'pwTreeController',
        scope: {
        	post : '=',
        }
    };
});

postworld.controller('pwTreeController', function ($scope, $timeout,pwCommentsService,$rootScope,$sce,$attrs,pwData,$log) {
    $scope.json = '';
    $scope.user_id = jsVars.user_id;
    $scope.isAdmin = jsVars.is_admin;
    $scope.startTime = 0;
    $scope.endTime = 0;
    $scope.treedata = {children: []};
    $scope.minimized = false;
    $scope.treeUpdated = false;
    $scope.commentsLoaded = false;
    $scope.key = 0;
    $scope.commentsCount = 0;
    $scope.feed = $attrs.loadComments;
    $scope.pluginUrl = jsVars.pluginurl;
    $scope.templateLoaded = false;
    console.log('post is',$scope.post);
    var settings = pwCommentsService.comments_settings[$scope.feed];
    if ($scope.post) {
    	settings.query.post_id = $scope.post.ID; // setting the post id here    	
    }
    $scope.minPoints = settings.min_points;
    if (settings.query.orderby) $scope.orderBy = settings.query.orderby;
    else $scope.orderBy = 'comment_date_gmt'; 
    if (settings.order_options) $scope.orderOptions = settings.order_options;
     
	pwData.templates.promise.then(function(value) {
		   if (settings.view) {
		   		var template = 'comment-'+settings.view;
		    	$scope.templateUrl = pwData.pw_get_template('comments','comment',template);
				$log.info('pwLoadCommentsController Set Post Template to ',$scope.templateUrl);
		   }
		   else {
	   			$scope.templateUrl = jsVars.pluginurl+'/postworld/templates/comments/comments-default.html';
	   			// this template fires the loadComments function, so there is no possibility that loadComments will run first.
		   }
	   		return;
	});
    
    $scope.loadComments = function () {
    	$scope.commentsLoaded = false;
    	settings.query.orderby = $scope.orderBy;
		pwCommentsService.pw_get_comments($scope.feed).then(function(value){
			console.log('Got Comments', value.data.length);
			$scope.treedata = {children: value.data};
			$scope.commentsLoaded = true;
	        $scope.treeUpdated = !$scope.treeUpdated;			      
			$scope.commentsCount = value.data.length;
			/*
			// not used here, but can be used for progressive element loading
		    var recursiveTimeout = function() {
		    	var load = $timeout( function loadComments() {	    		  
		    		  for (var i=0;i<20;i++) {
					      if ($scope.key<$scope.commentsCount) {
					    	  // console.log('loading data', $scope.key);
						      $scope.treedata.children[$scope.key] = value.data[$scope.key];
						      $scope.key++;
					      }
		    		  }
				      $scope.treeUpdated = !$scope.treeUpdated;			      
				      if ($scope.key<$scope.commentsCount) {
				      	load = $timeout(loadComments, 50);
				      }
				    }, 50); 
		    };
		    recursiveTimeout(); 
		    */
		});
    };
	// $scope.loadComments();
    
  $scope.toggleMinimized = function (child) {
    child.minimized = !child.minimized;
  };
  
  $scope.OpenClose = function(child) {
  	child.karmaPoints = child.comment_karma = Math.floor(Math.random() * 100) + 1;
  	if (parseInt(child.comment_karma)>$scope.minPoints) child.minimized = false;
  	else child.minimized = true;
  	// console.log('minimized',child.comment_ID,child.comment_karma,child.minimized);
  };
  
  $scope.trustHtml = function(child) {
    child.trustedContent = $sce.trustAsHtml(child.comment_content);
  };

  $scope.karmaAdd = function (child) {
	// Add Point here
	child.karmaPoints = parseInt(child.comment_karma)+1;
	
	// TODO Call Function
  };
  $scope.karmaRemove = function (child) {
	// Add Point here
	child.karmaPoints = parseInt(child.comment_karma)-1;
	// TODO Call Function
  };

  $scope.addChild = function (child, data) {
  	if (!child.children) child.children = [];
    child.children.push(data);
    $scope.treeUpdated = !$scope.treeUpdated;			      
  };
  
  $scope.updateChild = function (child, data) {
    // child.comment_content = data.comment_content;
    for (var key in data) {
    	child[key] = data[key];
    }
    $scope.treeUpdated = !$scope.treeUpdated;			      
  };

  $scope.toggleReplyBox = function(child) {
  	if (child.editInProgress || child.deleteInProgress || child.replyInProgress) return;
  	// close other boxes
  	child.editMode = false;
  	child.deleteBox = false;
  	// toggle reply box
  	child.replyBox = !child.replyBox;
  	// TODO add focus here
  };
  
  $scope.toggleEditBox = function(child) {
  	if (child.editInProgress || child.deleteInProgress || child.replyInProgress) return;
  	// close other boxes
  	child.deleteBox = false;
  	child.replyBox = false;
  	// toggle edit box
  	child.editMode = !child.editMode;
  	// TODO add focus here
  };
  
  $scope.toggleDeleteBox = function(child) {
  	if (child.editInProgress || child.deleteInProgress || child.replyInProgress) return;
  	// close other boxes
  	child.editMode = false;
  	child.replyBox = false;
  	// toggle delete box
  	child.deleteBox = !child.deleteBox;
  	// TODO add focus here
  };
  
  $scope.replyComment = function(child) {
  		// Disable reply button, text editing, cancelling until we are back
  		child.replyInProgress = true;
		child.replyError = "";
  		// trigger call to send reply
  		var args = {};
  		args.comment_data = {};
  		args.comment_data.comment_content = child.replyText;
  		args.comment_data.comment_post_ID = child.comment_post_ID;
  		args.comment_data.comment_date = new Date(); // should we do it here? security?
  		// args.comment_data.comment_date_gmt = ;
  		// args.comment_data.comment_type = 'comment';  	// in documentation, this is not added in wordpress insert/add functions	  			
  		if (child == $scope.treedata) {
	  		args.comment_data.comment_parent = 0;  			
  		} else {
	  		args.comment_data.comment_parent = child.comment_ID;  			
  		}
  		
  		args.return_value = 'data';  		
  		pwCommentsService.pw_save_comment(args).then(
  			function(response) {
  				if ((response.status==200)&&(response.data)) {
  					
	  				// reset form and hide it
			  		child.replyInProgress = false;
	  				child.replyText = "";
	  				child.replyBox = false;
	  				child.replyError = "";
			  		console.log('added',response);
	  				// show the new comment
	  				$scope.addChild(child, response.data);  					
  				} else {
	  				// reset the form
	  				child.replyInProgress = false;
	  				// TODO add more descriptive error
	  				child.replyError = "Error adding new comment";
	  				// show the error
	  				console.log('error adding new comment',response);  					
  				}
  			},
  			function(response) {
  				// reset the form
  				child.replyInProgress = false;
  				// TODO add more descriptive error
  				child.replyError = "Error adding new comment";
  				// show the error
  				console.log('error adding new comment',response);
  			}
  		);
  };
  
  $scope.editComment = function(child) {
  		// Disable edit button, text editing, cancelling until we are back
  		child.editInProgress = true;
		child.editError = "";		
  		// trigger call to send reply
  		var args = {};
  		args.comment_data = {};
  		args.comment_data.comment_ID = child.comment_ID;
  		args.comment_data.comment_content = child.editText;
  		
  		args.return_value = 'data';  		
  		pwCommentsService.pw_save_comment(args).then(
  			function(response) {
  				if ((response.status==200)&&(response.data)) {
  					
	  				// reset form and hide it
	  				child.editMode = false;
			  		child.editInProgress = false;
	  				child.editText = "";
	  				child.editBox = false;
	  				child.editError = "";
			  		console.log('edited',response);
	  				// show the new comment
	  				$scope.updateChild(child, response.data);  					
  				} else {
	  				// reset the form
	  				child.editMode = false;
	  				child.editInProgress = false;
	  				// TODO add more descriptive error
	  				child.editError = "Error editing comment";
	  				// show the error
	  				console.log('error editing comment',response);  					
  				}
  			},
  			function(response) {
  				// reset the form
  				child.editMode = false;
  				child.editInProgress = false;
  				// TODO add more descriptive error
  				child.editError = "Error editing comment";
  				// show the error
  				console.log('error editing comment',response);
  			}
  		);
  };

  $scope.deleteComment = function(child) {
  		// Disable edit button, text editing, cancelling until we are back
  		child.deleteInProgress = true;
		child.deleteError = "";
  		// trigger call to send reply
  		var args = {};
  		args.comment_id = child.comment_ID;
  		
  		pwCommentsService.pw_delete_comment(args).then(
  			function(response) {
  				if ((response.status==200)&&(response.data)) {
  					
	  				// reset form and hide it
			  		child.deleteInProgress = false;
	  				child.deleteBox = false;
	  				child.deleteError = "";
			  		console.log('deleted',response);
	  				// show the new comment
	  				$scope.removeChild(child);  					
  				} else {
	  				// reset the form
	  				child.deleteInProgress = false;
	  				// TODO add more descriptive error
	  				child.deleteError = "Error deleting comment";
	  				// show the error
	  				console.log('error deleting comment',response);  					
  				}
  			},
  			function(response) {
  				// reset the form
  				child.deleteInProgress = false;
  				// TODO add more descriptive error
  				child.deleteError = "Error deleting comment";
  				// show the error
  				console.log('error deleting comment',response);
  			}
  		);
  };

  $scope.removeChild = function (child) {
    function walk(target) {
      var children = target.children,
        i;
      if (children) {
        i = children.length;
        while (i--) {
          if (children[i] === child) {
            return children.splice(i, 1);
          } else {
            walk(children[i]);
          }
        }
      }
    }
    walk($scope.treedata);
    $scope.treeUpdated = !$scope.treeUpdated;			      
  };

});
