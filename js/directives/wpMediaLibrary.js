/*                __  __          _ _         _     _ _                          
  _ ____      __ |  \/  | ___  __| (_) __ _  | |   (_) |__  _ __ __ _ _ __ _   _ 
 | '_ \ \ /\ / / | |\/| |/ _ \/ _` | |/ _` | | |   | | '_ \| '__/ _` | '__| | | |
 | |_) \ V  V /  | |  | |  __/ (_| | | (_| | | |___| | |_) | | | (_| | |  | |_| |
 | .__/ \_/\_/   |_|  |_|\___|\__,_|_|\__,_| |_____|_|_.__/|_|  \__,_|_|   \__, |
 |_|                                                                       |___/ 
///////////////////////// POST MEDIA LIBRARY DIRECTIVE ////////////////////////*/
/*
///// EXAMPLE /////
// Create the media frame.
file_frame = wp.media.frames.file_frame = wp.media({
	title: 'My frame title',
	library: { // remove these to show all
		type: 'image', // specific mime
		author: userSettings.uid // specific user-posted attachment
	},
	button: {
		text: $el.data('update'), // button text
		close: true // whether click closes 
	}
	id: 'logo-frame',
	//Set to true to allow multiple images/files
	multiple: false,

	editing_sidebar: false, // Just added for example
	default_tab: 'upload', // Just added for example
	tabs: 'upload, library', // Just added for example
	returned_image_size: 'thumbnail' // Just added for example
});
*/

postworld.directive( 'wpMediaLibrary', function(){
	return {
		restrict: 'AE',
		controller: 'wpMediaLibraryCtrl',
		scope: {
			
			// Bind the selected media's ID
			mediaSetId:'=',

			// Bind the selected media's image object
			mediaModel:'=',

			// Bind the selection of an array of multiple images selected 
			mediaModelArray:'=',

			// Used by the setMediaPost() callback			
			mediaPost:'=',

			// The title of the media selector
			mediaTitle:'@',
			
			// Test in the button in the media selector
			mediaButton:'@',
			
			// Define which tabs are available
			mediaTabs:'@',
			
			// Set which tab is selected by default
			mediaDefaultTab:'@',
			
			// Toggle the option to select multiple pieces of media
			mediaMultiple:'@',
			
			// Filter the media selection options
			mediaType:'@',

			// A callback to call in the current isolated scope of this directive
			mediaCallback:'@',

			// A callback to call in the parent scope in which the directive is placed
			mediaParentCallback:'@',

			// Vars which are broadcast from the rootScope on media selection
			mediaBroadcast:'@',

			// Indicates the current status of loading / saving media
			mediaLoading:'=',

			// The ID of the media object (? is this still in use ?)
			mediaId:'@',

			// ???
			mediaClick:'&'
		},
		link: function( $scope, element, attrs ){
			/*
			// OBSERVE Attribute
			attrs.$observe('var', function(value) {
			});
			*/

			element.bind('click', function() {
				$scope.openMediaLibrary();

				//var src = elem.find('img').attr('src');
				// call your SmoothZoom here
				//angular.element(attrs.options).css({'background-image':'url('+ scope.item.src +')'});

			  });

		}
	};
});

postworld.controller( 'wpMediaLibraryCtrl',
	function( $scope, $rootScope, $window, $timeout, $log, $pwData, $_ ) {

	$scope.mediaLoading = false;

	///// SANDBOX /////
	//alert("mediaMultiple : " + $scope.mediaMultiple);
	//$scope.$parent.test = $scope.mediaMultiple;
	
	//$scope.test = "foo";
	
	///// ACTIONS /////

	$scope.openMediaLibrary = function(){
		//alert("Opening Media Library.");
		
		///// Load Settings /////
			// Set Defaults 
			var mediaModel = $scope.mediaModel;
			
			var mediaTitle = ( !_.isUndefined($scope.mediaTitle) ) ?
				$scope.mediaTitle : "Upload Media" ;
			
			var mediaButton = ( !_.isUndefined($scope.mediaButton) ) ?
				$scope.mediaButton : "Set Media" ;
			var mediaTabs = ( !_.isUndefined($scope.mediaTabs) ) ?
				$scope.mediaTabs : "upload, library" ;

			var mediaType = ( !_.isUndefined($scope.mediaType) ) ?
				$scope.mediaType : "" ;

			var mediaMultiple = $_.stringToBoolean( $scope.mediaMultiple );

			var mediaId = ( !_.isUndefined($scope.mediaId) ) ?
				$scope.mediaId : "" ;

			// Default Tab : uploadFiles
			//var mediaDefaultTab =
			//	( !_.isUndefined($scope.mediaDefaultTab) && $scope.mediaDefaultTab == 'upload' ) ?
			//		"uploadFiles" : "" ;

			// Define the Media Library Frame
			var mediaLibraryFrame = {
				id: mediaId,
				title: mediaTitle,
				button: {
				  text: mediaButton,
				},
				library: {
					type: mediaType,
				},
				multiple: mediaMultiple,
				tabs: mediaTabs,
			  };

			/*
			file_frame = wp.media.frames.file_frame = wp.media({
				title: 'My frame title',
				library: { // remove these to show all
					type: 'image', // specific mime
					author: userSettings.uid // specific user-posted attachment
				},
				button: {
					text: $el.data('update'), // button text
					close: true // whether click closes 
				}
				id: 'logo-frame',
				//Set to true to allow multiple images/files
				multiple: false,

				editing_sidebar: false, // Just added for example
				default_tab: 'uploadFiles', // Just added for example
				tabs: 'upload, library', // Just added for example
				returned_image_size: 'thumbnail' // Just added for example
			});

		 	id: 'mystate',
	        title: 'my title',
	        priority:   20,
	        toolbar:    'select',
	        filterable: 'uploaded',
	        library:    media.query( file_frame.options.library ),
	        multiple:   file_frame.options.multiple ? 'reset' : false,
	        editable:   true,
	        displayUserSettings: false,
	        displaySettings: true,
	        allowLocalEdits: true,


			*/

		// If the media frame already exists, reopen it.
		if ( $scope.file_frame ) {
		  $scope.file_frame.open();
		  return;
		}

		// Create the media frame.
		$scope.file_frame = wp.media.frames.file_frame = wp.media( mediaLibraryFrame );

		// When an image is selected, run a callback.
		$scope.file_frame.on( 'select', function() {
			var selectedMedia = $scope.file_frame.state().get('selection');
			$scope.setSelectedMedia( selectedMedia );
		});

		// Finally, open the modal
		$scope.file_frame.open();
		
	};

	
	$scope.setSelectedMedia = function( selectedMedia ){

		///// MEDIA MODEL /////
		// Set the selected Media Object into the specified Media Model
		//if( $_.objExists($scope, 'mediaModel') ){

			// If there's only one image, and mediaModelArray is not set to true
			if( selectedMedia.length == 1 && $scope.mediaModelArray != 'true' )
				$scope.mediaModel = selectedMedia.first();
			else
				$scope.mediaModel = selectedMedia;

		//}

		///// LOCAL CALLBACK /////
		// Run Specified Callback in Local Scope
		if( !_.isUndefined( $scope.mediaCallback ) ){
			var mediaCallback = $scope.mediaCallback;

			// Check to see if there's brackets
			// to see if a executable function is defined
			if( $_.isInArray( '(', mediaCallback ) &&
				$_.isInArray( ')', mediaCallback ) )
				$scope.$eval(mediaCallback);
			// If a pre-set function name is defined
			else
				$scope[mediaCallback](selectedMedia);
		}

		///// PARENT CALLBACK /////
		// Run Specified Callback in Parent Scope
		
		if( !_.isUndefined( $scope.mediaParentCallback ) ){
			var mediaParentCallback = $scope.mediaParentCallback;

			// Check to see if there's brackets
			// to see if a executable function is defined
			if( $_.isInArray( '(', mediaParentCallback ) &&
				$_.isInArray( ')', mediaParentCallback ) ){
				$scope.$parent.$eval( mediaParentCallback );
			}
			// If a pre-set function name is defined
			else{
				$scope.$parent[mediaParentCallback](selectedMedia);
			}
		}
		

		$scope.$apply();

	};

	$scope.test = function(message){
		alert(message);
	};

	$scope.editPostImage = function( selectedMedia ){
		// 1. Sets the image as thumbnail_id in the edit post object
		// 2. Gets the new image sizes and populates the edit post image object
		var thumbnail_id = selectedMedia.first().id;
		$scope.$parent.post.thumbnail_id = thumbnail_id;
		$scope.$parent.post.image = selectedMedia.first().attributes;

		$log.debug( "Selected Media", selectedMedia.first() );
	};


	$scope.editLinkImage = function( selectedMedia ){
		// 1. Sets the image as thumbnail_id in the edit post object
		// 2. Gets the new image sizes and populates the edit post image object
		var thumbnail_id = selectedMedia.first().id;
		$scope.$parent.post.post_meta.link_thumbnail_id = thumbnail_id;
		//$scope.$parent.post.post_meta.link_image = selectedMedia.first().attributes; //.sizes;

		$log.debug( "Selected Media", selectedMedia.first() );
	};

	
	$scope.setPostImage = function( selectedMedia ){
		// Immeadiately sets the selected image as the Post's image in the DB

		//alert( 'setPostImage' );
		// Get the first item from the array
		// Set it as the post image
		// If successful, replace the post.image object with the newly queried one

		var post_id = $scope.$parent.post.ID;
		var thumbnail_id = selectedMedia.first().id;

		var args = {
			'post_id': post_id,
			'thumbnail_id': thumbnail_id,
			'return_fields': ['ID','image(all)'],
			//'return': 'image( large, 300, 300, true )' // id / all / ID of registeded image size / parameters of image - passed to pw_get_post 
		};

		$pwData.set_post_image( args ).then(
			// Success
			function(response) {    
				// Replace the parent image object with the new image
				if( response.data != false )
					$scope.$parent.post.image = response.data.image;
				
			},
			// Failure
			function(response) {
				//$scope.movements = [{post_title:"Movements not loaded.", ID:"0"}];
			}
		);
		
	};

	$scope.returnMediaValue = function( field ){
		// Get selected media, undo 2-way data binding
		var selectedMedia = angular.fromJson( angular.toJson( $scope.mediaModel ) );

		// FIELD : id
		if( field == 'id' ){
			if( !_.isUndefined( selectedMedia.id ) )
				var value = selectedMedia.id;
			else if( !_.isUndefined( selectedMedia[0] ) )
				var value = selectedMedia[0].id;

		} else{
			var value = selectedMedia;
		}

		return value;
	};

	$scope.unbindSelectedMedia = function(){
		var selectedMedia = angular.fromJson( angular.toJson( $scope.mediaModel ) );
		return selectedMedia;
	};

	$scope.errorCheck = function(){
		if( !$_.objExists($scope, 'mediaModel') ){
			$log.debug('WP Media Library "setOption()" Callback Error : Must specify "media-model" attribute.');
			return false;
		}
		return true;
	};

	$scope.updateOption = function( option, field ){
		if( !$scope.errorCheck() )
			return false;

		var value = $scope.returnMediaValue( field );

		var vars = {
			option: option,
			value: value,
		};

		$pwData.update_option( vars ).then(
			function(response){
				// Get around 2-way data binding
				$scope.mediaModel = $scope.unbindSelectedMedia();
				$scope.$emit('updateOptions', vars );
			},
			function(response) {
			}
		);
	};

	/*
	$scope.setOptionObj = function( vars ){
		
		// var vars = {
		//	option_name: option_name,
		//	subkey: subkey,
		//	value: value,
		// };
		
		
		// Error Checking
		if( !$scope.errorCheck() )
			return false;
		
		// Set Value
		var value = $scope.returnMediaValue( vars.value );
		vars['value'] = value;

		// Emit action
		$scope.$emit('updateOptions', vars );

		// Undo 2-way data binding
		$scope.mediaModel = $scope.unbindSelectedMedia();

		// SAVE TO THE DATABASE
		$pwData.set_option_obj( vars ).then(
			function(response){
				$log.debug( "wpMediaLibrary.set_option_obj", response );
			},
			function(response) {
				$log.debug( "ERROR : wpMediaLibrary.set_option_obj", response );
			}
		);
	};
	*/


	$scope.emitSelectedMedia = function( key ){
		var selectedMedia = $scope.returnMediaValue();
		// Emit action
		$scope.$emit('selectedMedia', {
			format : 'media',
			key : key,
			media : selectedMedia
			});
	};

	$scope.emitSelectedMediaId = function( key ){
		var selectedMedia = $scope.returnMediaValue('id');
		// Emit action
		$scope.$emit('selectedMedia', {
			format : 'media-id',
			key : key,
			media : selectedMedia
			});
	};

	$scope.getSelectedMediaId = function(){
		return angular.fromJson( angular.toJson( $scope.returnMediaValue('id') ) );
	}

	$scope.setSelectedMediaId = function(){
		// Get the ID of the selected item
		var selectedMedia =  $scope.getSelectedMediaId();
		// Set it in the specified scope model
		$scope.mediaSetId = selectedMedia;

	};

	$scope.broadcastUpdate = function( broadcastObj ){
		// Standardized Broadcast from RootScope

		if( _.isEmpty( broadcastObj ) )
			broadcastObj = {};

		var defaultBroadcastObj = {
			name: 'selectMedia',
			args:{
				mediaId: $scope.getSelectedMediaId(),
				mediaObj: $scope.mediaModel.attributes,
			},
		};

		broadcastObj = array_replace_recursive( defaultBroadcastObj, broadcastObj );

		$log.debug( '>>> broadcastUpdate <<< ', broadcastObj );

		$rootScope.$broadcast( broadcastObj.name, broadcastObj.args );

	}

	var setMediaLoading = function( bool ){
		$log.debug( '$scope.mediaLoading', $scope.mediaLoading );
		if( !_.isUndefined( $scope.mediaLoading ) )
			$scope.mediaLoading = bool;
	}

	$scope.setSelectedMediaIdAsUsermeta = function( vars ){
		// Saves the selected image's ID as a usermeta value
		if( _.isEmpty( vars ) )
			vars = {};

		// Get the selected media's ID
		var mediaId = $scope.getSelectedMediaId();

		// Setup vars for AJAX cal
		var defaultVars = {
			user_id: 0,
			sub_key: '',
			//meta_key: null,
			value: mediaId,
		};

		vars = array_replace_recursive( defaultVars, vars );

		$log.debug('wpMediaLibrary.setSelectedMediaIdAsUsermeta() : REQUEST : ', vars );

		// Set the loading status
		setMediaLoading(true);

		// Do AJAX call
		$pwData.setWpUsermeta( vars ).then(
			function( response ){
				$log.debug('wpMediaLibrary.setSelectedMediaIdAsUsermeta() : RESPONSE : ', response );
			
				setMediaLoading(false);
				
				if( !_.isUndefined( $scope.mediaBroadcast ) ){
					//$log.debug( 'mediaBroadcast : ', $scope.mediaBroadcast );

					// Standardize a local function for this which
					// Includes the image details in the broadcast
				
					//$scope.broadcastUpdate( $scope.mediaBroadcast );
				
					$scope.broadcastUpdate( $scope.$eval( $scope.mediaBroadcast ) );

				}

				

			},
			function( response ){}
		);

	}


	$scope.setMediaPost = function( fields ){
		// Gets the updated post object for a selected image
		// And places it in the scope

		if( _.isEmpty( fields ) )
			fields = 'preview';

		// Setup vars
		var vars = {
			post_id: $scope.getSelectedMediaId(),
			fields: fields
		};

		// Send AJAX request
		$pwData.getPost( vars ).then(
			function( response ){
				$log.debug('wpMediaLibrary.setMediaPost : $pwData.getPost() : RESPONSE : ', response );
				//if( response. )
				if( $scope.mediaPost )
					$scope.mediaPost = response.data;
				return;
			},
			function( response ){}
		);

	}

});


