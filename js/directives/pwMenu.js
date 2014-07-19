/*                    __  __                      
  _ ____      __  _  |  \/  | ___ _ __  _   _ ___ 
 | '_ \ \ /\ / / (_) | |\/| |/ _ \ '_ \| | | / __|
 | |_) \ V  V /   _  | |  | |  __/ | | | |_| \__ \
 | .__/ \_/\_/   (_) |_|  |_|\___|_| |_|\__,_|___/
 |_|                                              
 ///////////////////////// MENU LOADING DIRECTIVE ////////////////////////*/

// Gets the site menus and populates into the local scope
postworld.directive( 'pwMenus', [ function($scope){
	return {
		restrict: 'AE',
		controller: 'pwMenusCtrl',
		scope:{
			pwMenus:'=pwMenus',	// Where to populated the result
		},
		link: function( $scope, element, attrs ){
			// OBSERVE Attribute
			//attrs.$observe('imageId', function(value) {
			//	$scope.getImage($scope.imageId);
			//});
		}
	};
}]);

postworld.controller( 'pwMenusCtrl',
	[ '$scope', '$window', '$timeout', 'pwData', '$log', '_',
	function( $scope, $window, $timeout, $pwData, $log, $_ ) {

	$scope.getMenus = function(){

		$pwData.get_menus({}).then(
			// Success
			function(response) {    
				//$log.debug("MENUS RESPONSE : ", response.data);
				$scope.pwMenus = response.data;
			},
			// Failure
			function(response) {
				//$scope.movements = [{post_title:"Movements not loaded.", ID:"0"}];
			}
		);
	};
	$scope.getMenus();


}]);