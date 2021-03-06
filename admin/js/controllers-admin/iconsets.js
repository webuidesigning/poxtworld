/*___                         _       
 |_ _|___ ___  _ __  ___  ___| |_ ___ 
  | |/ __/ _ \| '_ \/ __|/ _ \ __/ __|
  | | (_| (_) | | | \__ \  __/ |_\__ \
 |___\___\___/|_| |_|___/\___|\__|___/
									  
//////////////////////////////////////*/

postworldAdmin.directive('pwAdminIconData',
	function( $pwIconsets, $log, $_ ){
	return{
		restrict: 'A',
		link: function ($scope, element, attrs) {
			
			$scope.iconsets = $pwIconsets.array();

			$scope.filterIconset = function( iconsetClasses ){
				if( _.isEmpty($scope.filterString) )
					return true;
				return $_.stringInArray( $scope.filterString, iconsetClasses );
			}

			$scope.filterIcons = function( className ){
				if( _.isEmpty($scope.filterString) )
					return true;
				return $_.inString( $scope.filterString, className );
			}

			$scope.iconSelectedClass = function( iconClass, selectedIcon ){
				return ( iconClass === selectedIcon ) ? 'selected' : '';
			}

		}
	};
});


postworldAdmin.directive( 'pwAdminIconsets', [ function(){
	return { 
		controller: 'pwAdminIconsetsCtrl',
		link:function( $scope, element, attrs ){
			// Add Module Class
			element.addClass('pw-admin-iconsets');
		}
	};
}]);

postworldAdmin.controller( 'pwAdminIconsetsCtrl',
	[ '$scope', '$log', '$window', '$parse', '$pwData', '$_', '$pwPostOptions',
	function ( $scope, $log, $window, $parse, $pwData, $_, $pwPostOptions ) {
	
	$scope.select = {
		shortcodeIcon:'',
	};

	$scope.shortcodeAtts = {};

	$scope.iconsetIsRequired = function( iconsetSlug ){
		return $_.inArray( iconsetSlug, $scope.pwRequiredIconsets )
	}

	$scope.getIconShortcode = function( iconClass ){
		if( _.isEmpty( iconClass ) )
			return ""

		var additionalAtts = '';

		if( !_.isEmpty( $scope.shortcodeAtts ) )
			angular.forEach( $scope.shortcodeAtts, function( value, key ){
				if( !_.isNull(value) )
					additionalAtts += ' ' + key + "='" + value + "'";
			});
		
		return "[pw-icon class='" + iconClass + "'"+ additionalAtts +"]";

	}


}]);
