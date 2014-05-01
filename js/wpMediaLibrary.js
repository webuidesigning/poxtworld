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

postworld.directive( 'wpMediaLibrary', [ function($scope){
	return {
		restrict: 'AE',
		controller: 'wpMediaLibraryCtrl',
		scope: {
			mediaModel:'@mediaModel',
			mediaTitle:'@mediaTitle',
			mediaButton:'@mediaButton',
			mediaTabs:'@mediaTabs',
			mediaDefaultTab:'@mediaDefaultTab',
			mediaMultiple:'@mediaMultiple',
			mediaType:'@mediaType',
			mediaId:'@mediaId',

			mediaCallback:'@mediaCallback',
			mediaParentCallback:'@mediaParentCallback',

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
}]);

postworld.controller( 'wpMediaLibraryCtrl',
	[ '$scope', '$window', '$timeout', 'pwData',
	function( $scope, $window, $timeout, $pwData ) {

	///// SERVICE FUNCTIONS /////
	$scope.stringToBoolean = function(string){
		switch(string.toLowerCase()){
			case "true": case "yes": case "1": return true;
			case "false": case "no": case "0": case null: return false;
			default: return Boolean(string);
		}
	};

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
			var mediaTitle = ( !_.isUndefined($scope.mediaTitle) ) ? $scope.mediaTitle : "Upload Media" ;
			var mediaButton = ( !_.isUndefined($scope.mediaButton) ) ? $scope.mediaButton : "Set Media" ;
			var mediaTabs = ( !_.isUndefined($scope.mediaTabs) ) ? $scope.mediaTabs : "upload, library" ;
			var mediaType = ( !_.isUndefined($scope.mediaType) ) ? $scope.mediaType : "" ;
			var mediaMultiple = $scope.stringToBoolean($scope.mediaMultiple);
			var mediaId = ( !_.isUndefined($scope.mediaId) ) ? $scope.mediaId : "" ;
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
			$scope.setSelectedMedia(selectedMedia);
		});

		// Finally, open the modal
		$scope.file_frame.open();
		
	};

	
	$scope.setSelectedMedia = function( selectedMedia ){
		
		// alert( JSON.stringify( selectedMedia ) );
		//$scope.$parent.selectedMedia = selectedMedia;

		// Run Specified Callback in Local Scope
		if( !_.isUndefined( $scope.mediaCallback ) ){
			var mediaCallback = $scope.mediaCallback;
			$scope[mediaCallback](selectedMedia);
		}

		// Run Specified Callback in Parent Scope
		if( !_.isUndefined( $scope.mediaParentCallback ) ){
			var mediaParentCallback = $scope.mediaParentCallback;
			$scope.$parent[mediaParentCallback](selectedMedia);
		}

		// If multiple, return an array
		$scope.$apply();
	}

	$scope.setPostImage = function( selectedMedia ){

		//alert( 'setPostImage' );

		// Get the first item from the array

		// Set it as the post image

		// If successful, replace the post.image object with the newly queried one

		var post_id = $scope.$parent.post.ID;
		var thumbnail_id = selectedMedia.first().id;

		//alert(JSON.stringify( selectedMedia.first().id ));

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
				$scope.$parent.post.image = response.data.image;
				
			},
			// Failure
			function(response) {
				//$scope.movements = [{post_title:"Movements not loaded.", ID:"0"}];
			}
		);
		

	}



}]);




