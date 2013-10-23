'use strict';
var feed_settings = [];
var load_comments = [];




var postworld = angular.module('postworld', ['ngResource','ngRoute', 'ngSanitize', 'infinite-scroll'])
    .config(function ($routeProvider, $locationProvider) {    	    	
        $routeProvider.when('/live-feed-1/',
            {
                templateUrl: jsVars.pluginurl+'/postworld/templates/samples/pwLiveFeed1Widget.html',				
            });
        $routeProvider.when('/live-feed-2/',
            {
                templateUrl: jsVars.pluginurl+'/postworld/templates/samples/pwLiveFeed2Widget.html',				
            });
        $routeProvider.when('/live-feed-2-feeds/',
            {
                templateUrl: jsVars.pluginurl+'/postworld/templates/samples/pwLiveFeed3Widget.html',				
            });
        $routeProvider.when('/live-feed-2-feeds-auto/',
            {
                templateUrl: jsVars.pluginurl+'/postworld/templates/samples/pwLiveFeed4Widget.html',				
            });
        $routeProvider.when('/live-feed-params/',
            {
                templateUrl: jsVars.pluginurl+'/postworld/templates/samples/pwLiveFeed5Widget.html',				
            });
        $routeProvider.when('/load-feed-1/',
            {
                templateUrl: jsVars.pluginurl+'/postworld/templates/samples/pwLoadFeed1Widget.html',				
            });
        $routeProvider.when('/load-feed-2/',
            {
                template: '<h2>Coming Soon</h2>',				
            });
        $routeProvider.when('/load-feed-2-feeds/',
            {
                templateUrl: jsVars.pluginurl+'/postworld/templates/samples/pwLoadFeed3Widget.html',				
            });
        $routeProvider.when('/load-feed-cached-outline/',
            {
                templateUrl: jsVars.pluginurl+'/postworld/templates/samples/pwLoadFeed4Widget.html',				
            });
        $routeProvider.when('/load-panel/',
            {
                templateUrl: jsVars.pluginurl+'/postworld/templates/samples/pwLoadPanelWidget.html',				
            });
        $routeProvider.when('/register-feed/',
            {
                templateUrl: jsVars.pluginurl+'/postworld/templates/samples/pwRegisterFeedWidget.html',				
            });
        $routeProvider.when('/home/',
            {
                templateUrl: jsVars.pluginurl+'/postworld/templates/samples/pwLiveFeed2Widget.html',				
            });
        $routeProvider.when('/edit-post/',
            {
                templateUrl: jsVars.pluginurl+'/postworld/templates/samples/editPost.html',                
            });
        $routeProvider.when('/load-comments/',
            {
                templateUrl: jsVars.pluginurl+'/postworld/templates/samples/pwLoadCommentsWidget.html',				
            });            
		// this will be also the default route, or when no route is selected
        $routeProvider.otherwise({redirectTo: '/home/'});
    });

// Submit on Enter, without a real form
postworld.directive('ngEnter', function() {
        return function(scope, element, attrs) {
            element.bind("keydown keypress", function(event) {
                if(event.which === 13) {
                    scope.$apply(function(){
                        //scope.$eval(attrs.ngEnter);
                        scope.$eval("submit()");
                    });
                    event.preventDefault();
                }
            });
        };
    });
    
    

postworld.run(function($rootScope, $templateCache, pwData) {	
    	// TODO move getting templates to app startup
    	pwData.pw_get_templates(null).then(function(value) {
		    // TODO should we create success/failure responses here?
		    // resolve pwData.templates
		    pwData.templates.resolve(value.data);
		    pwData.templatesFinal = value.data;
		    console.log('postworld RUN getTemplates=',pwData.templatesFinal);
		  });    	
// TODO remove in production
   $rootScope.$on('$viewContentLoaded', function() {
      $templateCache.removeAll();
   });
});
   


/*
 * Getting Organized (Michel):
 * 
 * Whole Components
 ******************
 * Create Advanced Search Panel [complete missing boxes]
 * Do we need Directives for non-post types?
 * Create Post Types Toggles in Search Panel Dynamically http://jsfiddle.net/BtrZH/5/
 * 
 * Create Edit Fields for Radio, checkbox, TinMCE (WP has an hook for it), Buttons
 * 	Add Validations
 * 	Add Dynamic Sub Forms [ng-switch]
 * 	Add Embedding of URLs [embed.ly?]
 * 	Will be used in URL like #/post/edit/id, #/post/new, etc...
 * 	Will Switch between forms dynamically
 * 
 * TODO List
 * *********
 * Create Startup code that runs at app startup, and put getting templates into it
 *  * 
 * Refactoring Needed
 * ******************
 * Use App Constants
 * 
 * Issues
 * ******
 * Button for Feed Templates need to be toggled and populated from Feed Settings
 * NONCE - not active yet
 * Feed_settings must have a template URL for feed []
 * Remove additional fields added to args and saved with register_feed()
 * Add Parameters to URL of the Live Feed / Search parameters - add that to our menu as an example
 * 
 * Enhancements
 * *************
 * Submitting on Field Change
 * Fix Bootstrap field alignment
 * Shouldnt we get all templates in pw_get_templates, and cache them to be used across the whole session? this will save many uneeded calls as long as we're on the same SPA session?
 * 
 * Testing
 * *******
 * 
 * UI Enhancements
 * ***************
 * Add Animation
 * Add Scrollbar like Facebook
 * Make Simple Search panel show number of results.
 * 
 * Questions
 * *********
 * How will the Search and Other Pages be presented? Templates? Pages? Widgets? other?
 * 
 * 
 */


//////////////////// CONSTRUCTION ZONE < (phongmedia) ////////////////////

////////// SIMPLE HELPERS ////////
window.isInArray =  function(value, array) {
    if (array)
        return array.indexOf(value) > -1 ? true : false;
    else
        return false;
}

window.varExists = function(value){
    if ( typeof value === 'undefined' )
        return false;
    else
        return true;
}

window.isEmpty = function(value){
    if ( typeof value === 'undefined' || value == '' )
        return true;
    else
        return false; //value[0].value ? true : false;  
}

function extract_parentheses(string){
    var pattern = /\((.+?)\)/g,
        match,
        matches = [];
    while (match = pattern.exec(string)) {
        matches.push(match[1]);
    }
    return matches;
}

function isNumber(n) {
    return !isNaN(parseFloat(n)) && isFinite(n);
}

////////// ADVANCED HELPERS ////////

///// PARSE HIERARCHICAL SELECT ITEMS : FUNCTION /////
// Produces a series of HTML <options> from a given hierarchical object
function parse_hierarchical_select_items( items, selected, id_key, label_key, child_key, depth, indent ){
    // SET DEFAULTS
    if ( isEmpty(id_key) ) var id_key = 0;
    if ( isEmpty(label_key) ) var label_key = 'name';
    if ( isEmpty(child_key) ) var child_key = 'children';
    if ( isEmpty(depth) ) var depth = 1;
    if ( isEmpty(indent) ) var indent = ' - ';
    
    var select_items = '';

    // ROOT LEVEL ITEMS
    angular.forEach( items, function( item ){
        var id = item[id_key];
        var label = item[label_key];
        if ( isInArray( id, selected ) )
            var selected_attribute = ' selected ';
        else
            var selected_attribute = '';
        select_items += "<option value='" + id + "' "+selected_attribute+" >" + label + "</option>";

        // CHILD ITEMS
        var child = item[child_key];
        if ( typeof child !== 'undefined' && depth == 2 ){
            angular.forEach( item[child_key], function( item ){
                var id = item[id_key];
                var label = item[label_key];
                select_items += "<option value='" + id + "' "+selected_attribute+" > " + indent + label+ "</option>";
            });
        }

    });
    return select_items;
}

///// PARSE LINEAR SELECT ITEMS : FUNCTION /////
// Produces a series of HTML <options> from a given flat object
function parse_linear_select_items( items, selected ){
    var select_items = '';
    // ROOT LEVEL ITEMS
    angular.forEach( items, function( value, key ){
        var id = key;
        var label = items[key];
        if ( isInArray( id, selected ) )
            var selected_attribute = ' selected ';
        else
            var selected_attribute = '';
        select_items += "<option value='" + id + "' "+selected_attribute+" >" + label + "</option>";
    });
    return select_items;
}



////////// EDIT POST CONTROLLER //////////
function editPost($scope) {

    $scope.mode = "edit";

    // POST CLASS
    $scope.post_class_options = {
        contributor:"Contributor",
        author:"Author"
    };

    // TAXONOMY TERMS
    $scope.tax_terms = {
        "topic" : [
            {
                slug:"psyche",
                name:"/psyche",
                children:{
                    ancient:"Ancient Mysteries",
                    astrology:"Astrology",
                    consciousness:"Consciousness",
                    dreams:"Dreams",
                    ets:"Extraterrestrials",
                    indigenous:"Indigenous Cultures",
                    occult:"Occult",
                    psi:"Psi",
                    psychedelics:"Psychedelics",
                    psychology:"Psychology",
                    shamanism:"Shamanism",
                    spirituality:"Spirituality",
                    transformation:"Transformation",
                    psyche_misc:"Misc"
                    },
            },
            {
                slug:"arts",
                name:"/arts",
                children:{
                    conferences:"Conferences",
                    digital_art:"Digital Art",
                    world_art:"World Art",
                    festival_culture:"Festival Culture",
                    games:"Games",
                    music:"Music",
                    performance:"Performance",
                    publications:"Publications",
                    video:"Video",
                    film:"Film",
                    misc:"Misc"
                    },
            },
            {
                slug:"body",
                name:"/body",
                children:{
                    energy_medicine:"Energy Medicine",
                    food_nutrition:"Food & Nutrition",
                    healing:"Healing",
                    herbalism:"Herbalism",
                    homeopathy:"Homeopathy",
                    sexuality:"Sexuality",
                    slow_living:"Slow Living",
                    tantra:"Tantra",
                    yoga:"Yoga",
                    body_misc:"Misc",
                    },
            },
            {
                slug:"eco",
                name:"/eco",
                children:{
                    animal_rights:"Animal Rights",
                    climate_change:"Climate Change",
                    conservation:"Conservation",
                    energy:"Energy",
                    environment:"Environment",
                    extinction:"Extinction",
                    gardening:"Gardening",
                    permaculture:"Permaculture",
                    sustainability:"Sustainability",
                    water:"Water",
                    eco_misc:"Misc",
                    },
            },
            {
                slug:"tech",
                name:"/tech",
                children:{
                    biomimicry:"Biomimicry",
                    cosmology:"Cosmology",
                    design_science:"Design Science",
                    digital:"Digital",
                    diy:"DIY",
                    networks:"Networks",
                    privacy:"Privacy",
                    robotics:"Robotics",
                    singularity:"Singularity",
                    tech_misc:"Misc"
                }
            },
            {
                slug:"commons",
                name:"/commons",
                children:{
                    action_alerts:"Action Alerts",
                    activism:"Activism",
                    alternative_economics:"Alternative Economics",
                    collaboration:"Collaboration",
                    community:"Community",
                    crowdfunding:"Crowdfunding",
                    democracy:"Democracy",
                    drug_laws:"Drug Laws",
                    evolver:"Evolver",
                    evolver_spores:"Evolver Spores",
                    open_source:"Open Source",
                    peer_to_peer:"Peer to Peer",
                    retreats:"Retreats",
                    commons_misc:"Misc"
                }
            },
        ],
        'section' : [
            {
                slug:"psychedelic",
                name:"Psychedelic Culture",
            },
            {
                slug:"conscious_convergences",
                name:"Conscious Convergences",
            },
            {
                slug:"psi",
                name:"Psi Frontiers",
            },
            {
                slug:"video",
                name:"Videos",
            },
            {
                slug:"podcast",
                name:"Podcasts",
            },
            {
                slug:"edm",
                name:"Evolver EDM",
            },
            {
                slug:"evo_network",
                name:"Evolver Network",
            },
            {
                slug:"evo_learning_lab",
                name:"Evolver Learning Lab",
            },

        ],
        'type' : [
            {
                slug:"song_week",
                name:"Song of the Week",
                parent_name:"Hilight",
                parent:"hilight",
            },
            {
                slug:"video_week",
                name:"Video of the Week",
                parent_name:"Hilight",
                parent:"hilight"
            },
            {
                slug:"event_feature",
                name:"Featured Event",
                parent_name:"Events",
                parent:"events"
            },
            {
                slug:"event_evolver",
                name:"Evolver Event",
                parent_name:"Events",
                parent:"events"
            },
        ],
        
    };

    $scope.pw_get_post_object = function(){
        var post_data = {
            post_id : 24,
            post_date_gmt:"2013-09-16 18:24:16",
            post_title : "Hello Space",
            post_name : "hello_space",
            post_type : "feature",
            post_status : "publish",
            post_format : "video",
            post_class : "contributor",
            link_url : "",
            tax_input : {
                topic : ["healing","body"],
                section : ["psi"],
            },
            tags_input : "tag1, tag2, tag3",
        }

        // SORT TAXONOMIES
        // FOR EACH SELECTED TAXONOMY TERM SET
        // So that the taxonomy[0] is the main term and taxonomy[1] is the sub-term
        // ie. { topic : ["healing","body"] }
        angular.forEach( post_data.tax_input, function( selected_terms, taxonomy ){
            if ( selected_terms.length > 1 ){
                // FOR EACH TAXONOMY TERM OPTION
                // Go through each top level term for taxonomy in tax_terms
                // If it equals the first value of terms, leave it as is
                // If it isn't found, then swap order
                var reorder = true;
                angular.forEach( $scope.tax_terms[taxonomy], function( term_option ){
                    // Compare each term option to the selected terms
                    // If they're the same, do not reorder
                    if ( term_option.slug == selected_terms[0] ){
                        // If the term is the first term
                        reorder = false;
                    }
                });
                if ( reorder == true ){
                    post_data.tax_input[taxonomy].reverse();
                }
            }
        });

        return post_data;   
    }

    // POST DATA OBJECT
    $scope.post_data = $scope.pw_get_post_object();

    // POST TYPE OPTIONS
    $scope.post_types_linear = {
        feature : "Features",
        blog : "Blog",
        link : "Links",
        announcement : "Announcements",
        tribe_events : "Events"
    };

    ///// SELECTED TAXONOMY TERMS /////
    // • Creates an object with singular term data
    //    So that they can be referred to to define subtopics
    // • Manages the values of tax_input
    function selected_tax_terms(){

        // Create selected_tax_terms
        if ( isEmpty( $scope.selected_tax_terms ) )
            $scope.selected_tax_terms = {};
        
        // Simplify variable for tax_input
        var tax_input = $scope.post_data.tax_input;

        // EACH TAXONOMY : Cycle through each taxonomy
        angular.forEach( $scope.tax_terms, function( terms, taxonomy ){
            
            // Setup Object
            if ( isEmpty( tax_input[taxonomy] ) )
                tax_input[taxonomy] = [];

            // SET TERM : Cycle through each term
            // Set the selected taxonomy terms object
            angular.forEach( terms, function( term ){
                // If the term is selected, add it to the selected object
                if ( term.slug == $scope.post_data.tax_input[taxonomy][0] ){
                    $scope.selected_tax_terms[taxonomy] = term;
                }
            });

            ///// CLEAR SUBTERM /////
            // If there is a sub-term defined and it has children
            // Check to see if that child term exists in the main term
            
            // The set term object of the current taxonomy
            var term_set = $scope.selected_tax_terms[taxonomy];

            // Does the currently selected term of this taxonomy have children
            if ( typeof term_set !== 'undefined' ){
                if ( typeof term_set.children !== 'undefined' )
                    var term_has_children = true;
                else
                    var term_has_children = false; 
            }
            else
                var term_has_children = false;
            // Is the child term set for this taxonomy in tax_input?
            var child_term_is_set = !isEmpty( tax_input[taxonomy][1] );
            if ( term_has_children ){
                // Default
                var is_subterm = false;
                // Cycle through current sub-terms, and see if it exists
                angular.forEach( term_set.children, function( child_term_value, child_term_key ){
                    if ( child_term_key == tax_input[taxonomy][1] )
                        is_subterm = true;
                });
                // If it doesn't exist as a sub-term, clear it
                if ( is_subterm == false )
                    tax_input[taxonomy].splice(1,1);
            }
            // Otherwise clear it
            else if ( child_term_is_set )
                tax_input[taxonomy].splice(1,1);
        });
    };
    

    //selected_tax_terms();
    // TAXONOMY TERM WATCH : Watch for any changes to the post_data.tax_input
    // Make a new object which contains only the selected sub-objects
    $scope.$watch( "post_data.tax_input",
    //$scope.$watchCollection('[post_data.link_url, post_data.post_format]',
        function (){
            //alert("taxonomy change!");
            selected_tax_terms();
        }, 1 );


    // TAXONOMY SILO
    $scope.tax_term_silo = {

    };


    // POST STATUS OPTIONS
    $scope.post_status_options = {
        publish : "Published",
        draft : "Draft",
        pending : "Pending",
    };

    // POST FORMAT OPTIONS
    $scope.post_format_options = {
        standard : "Standard",
        video : "Video",
        audio : "Audio",
    };

    // POST FORMAT META
    $scope.post_format_meta = [
        {
            name:"Standard",
            slug:"standard",
            domains:[],
            icon:"<i class='icon-circle-blank'></i>"
        },
        {
            name:"Video",
            slug:"video",
            domains:["youtube.com","youtu.be","vimeo.com"],
            icon:"<i class='icon-youtube-play'></i>"
        },
        {
            name:"Audio",
            slug:"audio",
            domains:["soundcloud.com"],
            icon:"<i class='icon-headphones'></i>"
        },
    ];


    ///// EVALUATE AND SET POST_FORMAT DEPENDING ON LINK_URL /////
    evalPostFormat();
    function evalPostFormat(){
        var default_format = "standard";
        var link_url = $scope.post_data.link_url;
        var post_format_meta = $scope.post_format_meta;
        var set = "";
        function set_default_post_format(){
            $scope.post_data.post_format = default_format;
        }
        // If link_url has a value
        if ( !isEmpty( link_url ) && !isEmpty(post_format_meta) ){
            ///// FOR EACH POST FORMAT : Go through each post format
            angular.forEach( post_format_meta, function( post_format ){
                ///// FOR EACH DOMAIN : Go through each domain
                angular.forEach( post_format.domains, function( domain ){
                // If domain exists in the link_url, set that format
                    if ( isInArray( domain, link_url ) ){
                        //alert("post_format :" + post_format.slug);
                        $scope.post_data.post_format = post_format.slug;
                        set = post_format.slug;
                    }
                });
            });
            // If no matches, set default
            if ( set == "" )
                set_default_post_format();
        }
        // Otherwise, set default
        else {
            set_default_post_format();
        }
    };


    // LINK_URL WATCH : Watch for changes in link_url
    // Evaluate the post_format
    //$scope.$watch( "post_data.link_url",
    $scope.$watchCollection('[post_data.link_url, post_data.post_format]',
        function ( newValue, oldValue ){
            evalPostFormat();
        });

    // NG-CHANGE LINK_URL : Add this to link_url for optional method
    // ng-change="changeLinkUrl"
    $scope.changeLinkUrl = function(){
        evalPostFormat();
    };


    /*
    angular.forEach( items, function( value, key ){
        var id = key;
        var label = items[key];
        if ( isInArray( id, selected ) )
            var selected_attribute = ' selected ';
        else
            var selected_attribute = '';
        select_items += "<option value='" + id + "' "+selected_attribute+" >" + label + "</option>";

        //$rootScope.$apply();
    
    });
    */

    // SAVE POST FUNCTION
    $scope.savePost = function(){
        alert( JSON.stringify( $scope.post_data ) );
    }


    // DEV
    $scope.post_types = [
        {
            slug:"feature",
            name:"Feature",
            access:true
        },
        {
            slug:"blog",
            name:"Blog",
            access:true
        },
        {
            slug:"link",
            name:"Link",
            access:true
        },
        {
            slug:"announcement",
            name:"Announcements",
            access:false
        },
        {
            slug:"tribe_events",
            name:"Events",
            access:false
        }
    ];

    $scope.post_type = $scope.post_types[2];


    ////////// DATE & TIME PROCESSING //////////
    // The date format takes in the post_date_gmt, GMT/UTC
    // It is converted it to the local time zone
    // The user transforms the time in local time zone
    // Then it is saved to the model in GMT/UTC

    function clientTimeZoneAbbr (dateInput) {
    // Friendly timezone abbreviations in client-side JavaScript
        var dateObject = dateInput || new Date(),
            dateString = dateObject + "",
            tzAbbr = (
                // Works for the majority of modern browsers
                dateString.match(/\(([^\)]+)\)$/) ||
                // IE outputs date strings in a different format:
                dateString.match(/([A-Z]+) [\d]{4}$/)
            );
     
        if (tzAbbr) {
            // Old Firefox uses the long timezone name (e.g., "Central
            // Daylight Time" instead of "CDT")
            tzAbbr = tzAbbr[1].match(/[A-Z]/g).join("");
        }
        return tzAbbr;
    };

    // SET UTC TIME OBJECT
    // Casts a time object from the specified time zone into UTC time
    // ie. timeString = 2013-09-16 18:24:16
    // ie. timezone = PDT (optional) (default:UTC)
    function setTimeObjectUTC ( timeString, timezone ){
        if( typeof timezone === "undefined" )
            var timezone = " UTC";
        else
            var timezone = " " + timezone;
        var timeString = timeString + timezone;
        var timeObject = new Date( timeString );
        return timeObject;
    }

    // TIME OBJECT TO WORDPRESS TIME STRING : ie. 2013-10-12 18:24:16
    // Converts a JS time object into a WP string in local time
    // timeObject = a Javascript time object
    // type = 'array'/'string' - how to return the result
    // UTC = boolean (optional) - return the UTC time, if false returns the local time
    function timeObjectToWP ( timeObject, returnType, UTC ){

        if( UTC == true ){
            var year = timeObject.getUTCFullYear();
            var month = (timeObject.getUTCMonth() +1) ;
            var date = timeObject.getUTCDate();
            var hours = timeObject.getUTCHours();
            var minutes = timeObject.getUTCMinutes();
            var seconds = timeObject.getUTCSeconds();
        } else {
            var year = timeObject.getFullYear();
            var month = (timeObject.getMonth() +1) ;
            var date = timeObject.getDate();
            var hours = timeObject.getHours();
            var minutes = timeObject.getMinutes();
            var seconds = timeObject.getSeconds();
        }

        if( typeof returnType === "undefined" )
            var returnType = "string";

        if( returnType == "string" ){
            return year + "-" + month + "-" + date + " " + hours + ":" + minutes + ":" + seconds;
        }
        else if( returnType == "array" ){
            var timeArray = [];
            timeArray.push( year, month, date, hours, minutes, seconds );
            return timeArray;
        }

    }

    function getPostDate( dateField ){ // post_date_gmt / post_date
        if ( typeof dateField === 'undefined' ){
            // RETURN THE CURRENT DATE
            var currentDate = new Date();
            return currentDate;
        }
        else
            // RETURN THE GIVEN DATE
            return new Date( dateField );
    }

    //var gmt_time = "2013-09-16 18:24:16";
    // Get the post's time in GMT/UTC
    $scope.post_date_gmt = getPostDate( $scope.post_data.post_date_gmt );

    // TIME IN : local time zone
    $scope.timeString = timeObjectToWP( $scope.post_date_gmt, 'string' );
    $scope.timeArray = timeObjectToWP( $scope.post_date_gmt, 'array' );

    // TIME IN : UTC
    $scope.timeStringUTC = timeObjectToWP ( setTimeObjectUTC( $scope.timeString, clientTimeZoneAbbr() ), "string", 1 );

    // SANITIZE THE UPDATED FIELDS TO POST OBJECT
    $scope.$watch( "timeArray",
        function (){
            var timeArray = $scope.timeArray;
            var dateObj = $scope.post_date_gmt;

            // YEAR
            timeArray[0] = parseInt(timeArray[0]);
            if ( timeArray[0] > 9999 )
                timeArray[0] = timeArray[0].slice(0,4);
            if ( timeArray[0] < 1 || isNaN( timeArray[0] ) )
                timeArray[0] = dateObj.getUTCFullYear();

            // MONTH
            timeArray[1] = parseInt(timeArray[1]);
            if ( timeArray[1] > 12 )
                timeArray[1] = 12;
            if ( timeArray[1] < 1 || isNaN( timeArray[1] ) )
                timeArray[1] = (dateObj.getUTCMonth()+1);

            // DATE
            timeArray[2] = parseInt(timeArray[2]);
            if ( timeArray[2] > 31 )
                timeArray[2] = 31;
            if ( timeArray[2] < 1 || isNaN( timeArray[2] ) )
                timeArray[2] = 1;

            // HOURS
            timeArray[3] = parseInt(timeArray[3]);
            if ( timeArray[3] > 23 )
                timeArray[3] = 23;
            if ( timeArray[3] < 0 || isNaN( timeArray[3] ) )
                timeArray[3] = 0;

            // MINUTES
            timeArray[4] = parseInt(timeArray[4]);
            if ( timeArray[4] > 59 )
                timeArray[4] = 59;
            if ( timeArray[4] < 0 || isNaN( timeArray[4] ) )
                timeArray[4] = 0;

            // SECONDS
            timeArray[5] = parseInt(timeArray[5]);
            if ( timeArray[5] > 59 )
                timeArray[5] = 59;
            if ( timeArray[5] < 0 || isNaN( timeArray[5] ) )
                timeArray[5] = 0;

            // TIME IN : local time zone
            $scope.timeArray = timeArray;
            $scope.timeString = timeArray[0] + "-" + timeArray[1] + "-" + timeArray[2] + " " + timeArray[3] + ":" + timeArray[4] + ":" + timeArray[5];

            // TIME IN : UTC
            $scope.timeStringUTC = timeObjectToWP ( setTimeObjectUTC( $scope.timeString, clientTimeZoneAbbr() ), "string", 1 );

        },1);


        // WRITE THE UPDATED DATE TO POST OBJECT
        $scope.$watch( "timeStringUTC",
            function ( newValue, oldValue ){
                $scope.post_data.post_date_gmt = $scope.timeStringUTC;
            });

}


var blurFocusDirective = function () {
    return {
        restrict: 'E',
        require: '?ngModel',
        link: function (scope, elm, attr, ctrl) {
            if (!ctrl) {
                return;
            }

            elm.on('focus', function () {
                elm.addClass('has-focus');

                scope.$apply(function () {
                    ctrl.hasFocus = true;
                });
            });

            elm.on('blur', function () {
                elm.removeClass('has-focus');
                elm.addClass('has-visited');

                scope.$apply(function () {
                    ctrl.hasFocus = false;
                    ctrl.hasVisited = true;
                });
            });

            elm.closest('form').on('submit', function () {
                elm.addClass('has-visited');

                scope.$apply(function () {
                    ctrl.hasFocus = false;
                    ctrl.hasVisited = true;
                });
            });

        }
    };
};

postworld.directive('input', blurFocusDirective);
postworld.directive('select', blurFocusDirective);






