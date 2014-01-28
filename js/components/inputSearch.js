/*
  ___                   _     ____                      _     
 |_ _|_ __  _ __  _   _| |_  / ___|  ___  __ _ _ __ ___| |__  
  | || '_ \| '_ \| | | | __| \___ \ / _ \/ _` | '__/ __| '_ \ 
  | || | | | |_) | |_| | |_   ___) |  __/ (_| | | | (__| | | |
 |___|_| |_| .__/ \__,_|\__| |____/ \___|\__,_|_|  \___|_| |_|
           |_|                                                

//////////////////////// INPUT SEARCH //////////////////////*/

postworld.controller('inputSearch',
    ['$scope','$window','$timeout','pwData',
    function($scope, $window, $timeout, $pwData) {
    $scope.input = {};
    $scope.submit = function(){
        //alert( JSON.stringify(search_context) );
        $window.location.href = "/s/#/?s="+$scope.input.s;
    }
}]);


/*
  ____                      _       _____ _      _     _     
 / ___|  ___  __ _ _ __ ___| |__   |  ___(_) ___| | __| |___ 
 \___ \ / _ \/ _` | '__/ __| '_ \  | |_  | |/ _ \ |/ _` / __|
  ___) |  __/ (_| | | | (__| | | | |  _| | |  __/ | (_| \__ \
 |____/ \___|\__,_|_|  \___|_| |_| |_|   |_|\___|_|\__,_|___/
                                                             
////////// ------------ SEARCH FIELDS CONTROLLER ------------ //////////*/
postworld.controller('searchFields', ['$scope', 'pwPostOptions', 'pwEditPostFilters', function($scope, $pwPostOptions, $pwEditPostFilters) {

    // POST TYPE OPTIONS
    $scope.post_type_options = $pwPostOptions.pwGetPostTypeOptions('read');
    // POST YEAR OPTIONS
    $scope.post_year_options = $pwPostOptions.pwGetPostYearOptions();
    // POST MONTH OPTIONS
    $scope.post_month_options = $pwPostOptions.pwGetPostMonthOptions();
    // POST STATUS OPTIONS
    $scope.post_status_options = $pwPostOptions.pwGetPostStatusOptions( );
    // POST FORMAT OPTIONS
    $scope.link_format_options = $pwPostOptions.pwGetLinkFormatOptions();
    // POST FORMAT META
    $scope.link_format_meta = $pwPostOptions.pwGetPostFormatMeta();
    // POST CLASS OPTIONS
    $scope.post_class_options = $pwPostOptions.pwGetPostClassOptions();

    // TEST OPTION IMPORT
    //$scope.pw_site_options = $pwPostOptions.pw_site_options();


    // TAXONOMY TERMS
    // Gets live set of terms from the DB
    // as $scope.tax_terms
    // TODO : VERIFY THIS WORKS
    $pwPostOptions.getTaxTerms($scope, 'tax_terms');

    // TAXONOMY TERM WATCH : Watch for any changes to the post_data.tax_input
    // Make a new object which contains only the selected sub-objects
    $scope.selected_tax_terms = {};
    $scope.$watch( "taxInput",
        function (){
            // Create selected terms object
            $scope.selected_tax_terms = $pwEditPostFilters.selected_tax_terms($scope.tax_terms, $scope.taxInput);
            // Clear irrelivent sub-terms
            //$scope.post_data.tax_input = $pwEditPostFilters.clear_sub_terms( $scope.tax_terms, $scope.taxInput, $scope.selected_tax_terms );
        
        }, 1 );

    /*
    $scope.$on('updateUsername', function(username) { 
        $scope.feedQuery.author_name = username;
    });
    */
    
}]);