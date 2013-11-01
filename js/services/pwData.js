/**
 * Created by Michel on 9/22/13.
 * 	Development Note:-
 *	To make Ajax request work as a form post, we need to do 3 things:
 *	1- Use AngularJS version 1.2
 * 	2- change content type in the header:-  headers: {'Content-Type': 'application/x-www-form-urlencoded', 'charset':'UTF-8'}
 * 	3- Transform data to url encoded format using the following function http://victorblog.com/2012/12/20/make-angularjs-http-service-behave-like-jquery-ajax/
 *	http://stackoverflow.com/questions/11442632/how-can-i-make-angular-js-post-data-as-form-data-instead-of-a-request-payload
 *  Otherwise, if you don't want to go through the above hassle, you can just do the following on the server:- 
 * 	$args_text = file_get_contents("php://input");
 *	$args = json_decode($args_text);
 * */

postworld.factory('pwData', function ($resource, $q, $log) {	  
	// Used for Wordpress Security http://codex.wordpress.org/Glossary#Nonce
	var nonce = 0;
	// Check feed_settigns to confirm we have valid settings
	var validSettings = true;
	// Set feed_settings and feed_data in pwData Singleton
	var feed_settings = window['feed_settings'];
	// TODO check mandatory fields
	if (feed_settings == null) {
		validSettings = false;
		$log.error('Service: pwData Method:Constructor  no valid feed_settings defined');
	}
	
	var feed_data = {};
	
	$log.info('pwData() Registering feed_settings', feed_settings);
	
	var	getTemplate = function(pwData,grp,type,name) {
			var template;
			// TODO can we make this lookup dynamic?
			// $log.info('template here',grp,type,name);
			switch (grp) {
				case 'posts':
					if (type) {
						template = pwData.templatesFinal.posts[type][name];						
					} else {
						template = jsVars.pluginurl+'/postworld/templates/posts/post-list.html';						
					}
					// $log.info('post template:',pwData.templatesFinal.posts);
					break;
				case 'panels':
					template = pwData.templatesFinal.panels[name];
					//template = jsVars.pluginurl+'/postworld/templates/panels/'+name+'.html';
					break;
				case 'comments':
					template = pwData.templatesFinal.comments[name];
					//template = jsVars.pluginurl+'/postworld/templates/panels/'+name+'.html';
					break;
				default:
					template = jsVars.pluginurl+'/postworld/templates/panels/feed_top.html';
					break;
			}
			// $log.info('Service: pwData Method:getTemplate template=',template);
			return template;			
	};
	
	
	// for Ajax Calls
    var resource = $resource(jsVars.ajaxurl, {action:'wp_action'}, 
    							{	wp_ajax: { method: 'POST', isArray: false, },	}
							);
	
    return {
    	feed_settings: feed_settings,
    	feed_data: feed_data,
    	templates: $q.defer(),
    	templatesFinal:{},
    	// Set Nonce Value for Wordpress Security
    	setNonce: function(val) {
    		nonce = val;
    	},
    	// Get Nonce Value
    	getNonce: function() {
    		return nonce;
    	},
    	// A simplified wrapper for doing easy AJAX calls to Wordpress PHP functions
		wp_ajax: function(fname, args) {
			$log.info('pwData.wp_ajax', fname, 'args: ',args);
            var deferred = $q.defer();
            // works only for non array returns
            resource.wp_ajax({action:fname},{args:args,nonce:this.getNonce()},
				function (data) {
                    deferred.resolve(data);
                },
                function (response) {
                    deferred.reject(response);
                });
            return deferred.promise;		
		},
		pw_live_feed: function(args) {
			// args: arguments received from Panel. fargs: is the final args sent along the ajax call.
			// fargs will be filled initially with data from feed settings, 
			// fargs will be filled next from data in the args parameters
			
			var fargs = this.convertFeedSettings(args.feed_id,args); // will read settings and put them in fargs
			fargs = this.mergeFeedQuery(fargs,args); // will read args and override fargs
			   
			var params = {'args':fargs};
			$log.info('pwData.pw_live_feed',fargs);
			return this.wp_ajax('pw_live_feed',params);
		},
		pw_scroll_feed: function(args) {
			$log.info('pwData.pw_scroll_feed',args);
			var params = {args:args};
			return this.wp_ajax('pw_scroll_feed',params);
		},
		o_embed: function(url,args) {
			$log.info('pwData.o_embed',args);
			var params = { url:url, args:args};
			return this.wp_ajax('o_embed',params);
		},
		pw_get_posts: function(args) {
			var feedSettings = feed_settings[args.feed_id];
			var feedData = feed_data[args.feed_id];
			// If already all loaded, then return
			if (feedData.status == 'all_loaded')  {
				$log.info('pwData.pw_get_posts ALL LOADED');
				// TODO should we return or set promise.?
				 //var results = {'status':200,'data':[]};
				 var response = $q.defer();
				 response.promise.resolve(1);				
				return response.promise;
			};
			// else, get posts and recalculate
			
			// Set Post IDs - get ids from outline, [Loaded Length+1 to Loaded Length+Increment]
			// Slice Outline Array
			var idBegin = feedData.loaded;
			var idEnd = idBegin+feedSettings.load_increment;
			// TODO Check if load_increment exists
			// Only when feed_outline exists and this is the first run, load from preload value, not from auto increment value
			if (feedData.loaded==0) {
				if (feedSettings.preload)
					idEnd = idBegin+feedSettings.preload;
					// TODO, use constant here
				else idEnd = idBegin+10;
			}
			var postIDs = feedData.feed_outline.slice(idBegin,idEnd);
			var fields;
			if (feedSettings.query_args) {
				if (feedSettings.query_args.fields != null) {
					fields = feedSettings.query_args.fields;
				}				
			}
			$log.info('pwData.pw_get_posts range:',idBegin, idEnd);
			// Set Fields
			var params = { feed_id:args.feed_id, post_ids:postIDs, fields:fields};
			$log.info('pwData.pw_get_posts',params);
			return this.wp_ajax('pw_get_posts',params);
		},
		pw_get_templates: function(templates_object) {
			// TODO Optimize by running it once and caching it
			$log.info('pwData.pw_get_templates',templates_object);
			var params = { templates_object:templates_object};			
			return this.wp_ajax('pw_get_templates',params);
		},
		pw_register_feed: function(args) {
			$log.info('pwData.pw_register_feed',args);
			var params = {args:args};
			return this.wp_ajax('pw_register_feed',params);
		},
		pw_load_feed: function(args) {
			$log.info('pwData.pw_load_feed',args);
			var params = {args:args};
			return this.wp_ajax('pw_load_feed',params);
		},
		pw_get_post: function(args) {
			$log.info('pwData.pw_get_post',args);
			//var params = {args:args};
			return this.wp_ajax('pw_get_post',args);
		},
		pw_get_template: function(grp,type,name) {
			// if templates object already exists, then get value, if not, then retrieve it first
			var template = getTemplate(this,grp,type,name);
		    return template;
		}, // END OF pw_get_template
		convertFeedSettings: function (feedID,args1) {
			var fargs = {};
			fargs.feed_query = {};
			//if(!args.feed_query) args.feed_query = {};
			// TODO use constants from app settings
			// Get Feed_Settings Parameters
			var feed = feed_settings[feedID];
			// Query Args will fill in the feed_query first, then any other parameter in the feed will override it, then any user parameter will override all
			if (feed.query_args != null) fargs.feed_query = feed.query_args;  
			if (feed.preload != null) fargs.preload = feed.preload; else fargs.preload = 10;  
			if (feed.offset	!= null) fargs.offset = feed.offset; else fargs.offset = 0;  
			if (feed.max_posts != null) fargs.feed_query.posts_per_page = feed.max_posts; else fargs.feed_query.posts_per_page = 1000;
			 
			if (feed.order_by != null) {
				// if + sort Ascending
				if (feed.order_by.charAt(0)=='+') fargs.feed_query.order = 'ASC';
				// if - sort Descending				
				else  if (feed.order_by.charAt(0)=='-') fargs.feed_query.order = 'DESC';
				else fargs.feed_query.order = 'ASC';
				// If + or - then remove the first character
				if ((feed.order_by.charAt(0)=='+') || (feed.order_by.charAt(0)=='-')) {
					fargs.feed_query.order_by = feed.order_by.slice(1);
				}
			}	// else the default whatever it is, is used
			if (feed.offset != null) fargs.feed_query.offset = feed.offset; // else the default is zero 
			fargs.feed_id = feedID;
			return fargs;			
		},
		mergeFeedQuery: function (fargs,args) {
			if (args.feed_query) {
				for (var prop in args.feed_query) {
				    fargs.feed_query[prop] = args.feed_query[prop];
				    //$log.info("args.feed_query",prop,args.feed_query[prop],fargs.feed_query[prop]);
				}
			}
			return fargs;
		},
		pw_get_post_types: function(args) {
			//$log.info('pwData.pw_load_feed',args);
			var params = {args:args};
			return this.wp_ajax('pw_get_post_types', params);
		},
		ajax_oembed_get: function(args) {
			$log.info('pwData.ajax_oembed_get',args);
			var params = {args:args};
			return this.wp_ajax('ajax_oembed_get', params);
		},
		pw_save_post: function(args) {
			$log.info('pwData.pw_save_post',args);
			var params = {args:args};
			return this.wp_ajax('pw_save_post_admin', params);
		},
		pw_get_post_edit: function(args) {
			$log.info('pwData.pw_get_post_edit',args);
			var params = {args:args};
			return this.wp_ajax('pw_get_post_edit',params);
		},
		taxonomies_outline_mixed: function(args) {
			$log.info('pwData.taxonomies_outline_mixed',args);
			var params = {args:args};
			return this.wp_ajax('taxonomies_outline_mixed',params);
		},
		user_query_autocomplete: function(args) {
			$log.info('pwData.user_query_autocomplete',args);
			var params = {args:args};
			return this.wp_ajax('user_query_autocomplete',params);
		},
		tags_autocomplete: function(args) {
			$log.info('pwData.tags_autocomplete',args);
			var params = {args:args};
			return this.wp_ajax('tags_autocomplete',params);
		},
		set_post_relationship: function(args) {
			$log.info('pwData.set_post_relationship',args);
			var params = {args:args};
			return this.wp_ajax('set_post_relationship',params);
		},

   }; // END OF pwData return value
});
