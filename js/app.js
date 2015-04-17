/*____           _                      _     _ 
 |  _ \ ___  ___| |___      _____  _ __| | __| |
 | |_) / _ \/ __| __\ \ /\ / / _ \| '__| |/ _` |
 |  __/ (_) \__ \ |_ \ V  V / (_) | |  | | (_| |
 |_|   \___/|___/\__| \_/\_/ \___/|_|  |_|\__,_|

////////////////////////////////////////////////

A Javascript and PHP Wordpress framework for
• Extending the Wordpress functions API
• Displaying posts in creative ways.

JS Framework : AngularJS
GitHub Repo  : https://github.com/phongmedia/postworld/
ASCII Art by : http://patorjk.com/software/taag/#p=display&f=Standard
*/

// Documention by JSDOC
// http://usejsdoc.org/
/**
 * @module Postworld
 */

'use strict';

pw.partials = {};
pw.templates = {};
pw.feeds = {};
pw.widgets = {};
pw.admin = {};
pw.embeds = {};

// Add Standard Modules
pw.angularModules = pw.angularModules.concat([
	'ngResource',
	'ngRoute',
	'ngSanitize',
	'ngTouch',
	'ngAria',
	'ui.bootstrap',
	'monospaced.elastic',
	'timer',
	'angular-parallax',
	'wu.masonry',
	'pw.compile',
	'checklist-model',
]);

var postworld = angular.module('postworld', pw.angularModules );

///// POSTWORLD ADMIN MODULE /////
var depInjectAdmin = [
	'postworld',
	'ui.slider',
	];

var postworldAdmin = angular.module('postworldAdmin', depInjectAdmin );

var controllerProvider;

postworld.config(function ($routeProvider, $locationProvider, $provide, $logProvider, $controllerProvider ) {   

	// Pass $controllerProvider so that vanilla JS can init new controllers
	controllerProvider = $controllerProvider;

	var plugin_url = jsVars.pluginurl;

	$routeProvider.when('/new/:post_type',
		{
			action: "new_post",
		});

	$routeProvider.when('/new/',
		{
			action: "new_post",
		});

	$routeProvider.when('/edit/:post_id',
		{
			action: "edit_post",
		});

	$routeProvider.when('/home/',
		{
			action: "default",
		});

	// this will be also the default route, or when no route is selected
	// $routeProvider.otherwise({redirectTo: '/home/'});

	// SHOW / HIDE DEBUG LOGS IN CONSOLE
	var debugEnabled = ( window.pw.info.mode == 'dev' ) ? true : false;
	$logProvider.debugEnabled( debugEnabled );

	$locationProvider.html5Mode( window.pw.view.location_provider.html_5_mode );

});


/*____              
 |  _ \ _   _ _ __  
 | |_) | | | | '_ \ 
 |  _ <| |_| | | | |
 |_| \_\\__,_|_| |_|        
*/
postworld.run( 
	function( $rootScope, $window, $templateCache, $log, $location, $rootElement, pwData){    

		///// ALLOW LINK CLICKING /////
		// Critical so that when $locationProvider is in HTML5 mode
		// Normal links can be clicked
		$rootElement.off('click');


		/////// DEV SNIPPETS /////
		// TODO remove in production
		/*
		$rootScope.$on('$viewContentLoaded', function() {
		$templateCache.removeAll();
		});
		*/
		//$rootScope.current_user = $window.pwGlobals.user;
		//$log.debug('Current user: ', $rootScope.current_user );

});


///// FUNCTION : REGISTER CONTROLLER AFTER BOOTSTRAP /////
function pwRegisterController( controllerName, moduleName ) {
    // Here I cannot get the controller function directly so I
    // need to loop through the module's _invokeQueue to get it
    if( moduleName == null )
    	moduleName = "postworld";
    
    var queue = angular.module(moduleName)._invokeQueue;
    for(var i=0;i<queue.length;i++) {
        var call = queue[i];
        if(call[0] == "$controllerProvider" &&
           call[1] == "register" &&
           call[2][0] == controllerName) {
           	if( !_.isUndefined( controllerProvider ) )
            	controllerProvider.register(controllerName, call[2][1]);
        }
    }

    //console.log( 'pwRegisterController : ' + controllerName + ', ' + moduleName );

}

///// FUNCTION : COMPILE AND ELEMENT AFTER BOOTSTRAP /////
function pwCompileElement( context, id ){
	// Compile a new element, after the controller is registered
	
	var contextInjector = angular.element(context).injector();

	if( _.isUndefined( contextInjector ) )
		return false;

	contextInjector.invoke(function($compile, $rootScope) {
	    $compile( angular.element('#'+id))($rootScope);
	    $rootScope.$apply();
	});

	/*// USING JQUERY
	jQuery(context).injector().invoke(function($compile, $rootScope) {
	    $compile(jQuery('#'+id))($rootScope);
	    $rootScope.$apply();
	});
	*/
}


////////// REPLACE ALL STRING PROTOTYPE //////////
String.prototype.replaceAll = function(search, replace)
{
    //if replace is null, return original string otherwise it will
    //replace search string with 'undefined'.
    if(!replace) 
        return this;

    return this.replace(new RegExp('[' + search + ']', 'g'), replace);
};


/*
	 __     __  ____    _    _   _ ____  ____   _____  __     __     __
	/ /    / / / ___|  / \  | \ | |  _ \| __ ) / _ \ \/ /    / /    / /
   / /    / /  \___ \ / _ \ |  \| | | | |  _ \| | | \  /    / /    / / 
  / /    / /    ___) / ___ \| |\  | |_| | |_) | |_| /  \   / /    / /  
 /_/    /_/    |____/_/   \_\_| \_|____/|____/ \___/_/\_\ /_/    /_/    
/////////////////////////////////////////////////////////////////*/



postworld.constant('angularMomentConfig', {
	//preprocess: 'unix', 				// optional
	//timezone: 'America/Los_Angeles' 	// optional
});


/*
postworld.run(function($rootScope, $templateCache) {
   $rootScope.$on('$viewContentLoaded', function() {
	  $templateCache.removeAll();
   });
});
*/