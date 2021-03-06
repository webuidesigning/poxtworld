<?php
// Enable Media Library
wp_enqueue_media();

$pwSiteOptions = pw_get_option( array( 'option_name' => PW_OPTIONS_SITE ) );
// Set default empty value as object, not array
if( empty($pwSiteOptions) )
	$pwSiteOptions = array( '_' => 0 );

$pw_header_code = get_option( PW_OPTIONS_HEADER_CODE, '' );

pw_print_ng_controller(array(
	'app' => 'postworldAdmin',
	'controller' => 'pwOptionsDataCtrl',
	'vars' => array(
		'pwSiteOptions' => $pwSiteOptions,
		'pwHeaderCode' => $pw_header_code,
		'images' => array( '_' => 0 ),
		'memoryOptions' => apply_filters('pw_options_site_memory',array()),
		'postworldModeOptions' => apply_filters('pw_options_postworld_mode',array()),

		),
	));

?>

<?php do_action( 'postworld_admin_header' ) ?>

<div pw-admin-options class="postworld wrap">

	<h1 class="primary">
		<i class="icon pwi-gear"></i>
		<?php _e('Site Options','postworld') ?>
	</h1>
	
	<div ng-controller="pwOptionsDataCtrl" class="pw-cloak">

		<!--///// THEME OPTIONS /////-->
		<hr class="thick">

		<div class="row">
			<div class="col-sm-6 pad-col-md">

				<?php
				/**
				 * This is now natively supported by WordPress core
				 * In the theme customizer
				 */
				/*
				<!-- FAVICON -->
				<div class="well">
					<div class="save-right">
						<?php pw_save_option_button( PW_OPTIONS_SITE, 'pwSiteOptions'); ?>
					</div>
					<h2>
						<span class="icon-md"><i class="pwi-image"></i></span>
						<?php _e('Favicon','postworld') ?>
					</h2>
					<div class="well">
						<?php _e( 'Select the favicon, which appears as the tab icon in the browser.', 'postwold' ) ?>
						<hr class="thin">
						<?php
							echo pw_select_image_id( array(
								'ng_model'		=>	'pwSiteOptions.images.favicon',
								'slug'			=>	'favicon',
								'label'			=>	'Favicon',
								'display'		=>	true,
								'width'			=> 	'64px',
							 	));?>
					</div>
				</div>

				*/ ?>

				<!-- AVATAR IMAGE -->
				<?php if( pw_config('wp_admin.user_meta.pw_avatar') ): ?>
					<div class="well">
						<div class="save-right">
							<?php pw_save_option_button( PW_OPTIONS_SITE, 'pwSiteOptions'); ?>
						</div>
						<h2>
							<span class="icon-md"><i class="pwi-image"></i></span>
							<?php _e('Default Avatar','postworld') ?>
						</h2>
						<div class="well">
							<?php _e( 'Select the default avatar image.', 'postwold' ) ?>
							<hr class="thin">
							<?php
								echo pw_select_image_id( array(
									'ng_model'		=>	'pwSiteOptions.images.avatar',
									'slug'			=>	'avatar',
									'label'			=>	'Default Avatar',
									'display'		=>	true,
									'width'			=> 	'256px',
								 	));?>
						</div>
					</div>
				<?php endif ?>

				<!-- WORDPRESS CORE -->
				<div class="well">
					<div class="save-right">
						<?php pw_save_option_button( PW_OPTIONS_SITE, 'pwSiteOptions'); ?>
					</div>
					<h2>
						<span class="icon-md"><i class="pwi-wordpress"></i></span>
						<?php _e('WordPress Core','postworld') ?>
					</h2>
					<div class="well">
						<label>
							<input type="checkbox" ng-model="pwSiteOptions.wp_core.disable_wp_emojicons">
							<b><?php _e('Disable WP Emojicons','postworld') ?></b>
							<small><?php _e('Increases load speed.','postworld') ?></small>
						</label>
					</div>
				</div>


				<div class="well">
					<div class="save-right">
						<?php pw_save_option_button( PW_OPTIONS_HEADER_CODE, 'pwHeaderCode'); ?>
					</div>
					<h2>
						<i class="pwi-code"></i>
						<?php _e('Header Code','postworld') ?>
					</h2>
					<small>
						<?php _e('This code will be inserted into the page header. Here is a good place to post tracking codes such as Google Analytics, or third-party additions.','postworld') ?>
					</small>
					<hr class="thin">
					<div>
						<textarea
							msd-elastic
							class="form-control"
							ng-model="pwHeaderCode"></textarea>
					</div>
				</div>

			</div>
			<div class="col-sm-6 pad-col-md">
				

				<!-- SECURITY -->
				<div class="well">
					<div class="save-right">
						<?php pw_save_option_button( PW_OPTIONS_SITE, 'pwSiteOptions'); ?>
					</div>
					<h2>
						<span class="icon-md"><i class="pwi-lock"></i></span>
						<?php _e('Security','postworld') ?>
					</h2>
					<div class="well">
						<label>
							<input type="checkbox" ng-model="pwSiteOptions.security.disable_xmlrpc">
							<b><?php _e('Disable XMLRPC API','postworld') ?></b>
							<small><?php _ex( 'Commonly an access point for DDoS attacks.', 'XMLRPC API', 'postwold' ) ?></small>
						</label>
						<hr class="thin">
						<label>
							<input type="checkbox" ng-model="pwSiteOptions.security.require_login">
							<b><?php _e('Require Login','postworld') ?></b>
							<small><?php _e('Require login to access site.','postworld') ?></small>
						</label>
					</div>
				</div>


				<!-- PERFORMANCE -->
				<div class="well">
					<div class="save-right">
						<?php pw_save_option_button( PW_OPTIONS_SITE, 'pwSiteOptions'); ?>
					</div>
					<h2>
						<span class="icon-md"><i class="pwi-fire"></i></span>
						<?php _ex('Performance', 'website performance', 'postworld') ?>
					</h2>
					<div class="well">
						<label>
							<select
								ng-model="pwSiteOptions.memory.image_memory_limit"
								ng-options="option.value as option.label for option in memoryOptions">
							</select>
							<b><?php _e('Image Memory Limit','postworld') ?></b>
							<small><?php _e('The maximum amount of memory used for processing images.','postworld') ?></small>
						</label>
					</div>
				</div>

				<!-- POSTWORLD MODE -->
				<div class="well">
					<div class="save-right">
						<?php pw_save_option_button( PW_OPTIONS_SITE, 'pwSiteOptions'); ?>
					</div>
					<h2>
						<span class="icon-md"><i class="pwi-postworld"></i></span>
						<?php _e('Postworld','postworld') ?>
					</h2>
					<div class="well">
						<label>
							<select
								ng-model="pwSiteOptions.postworld.mode"
								ng-options="option.value as option.label for option in postworldModeOptions">
							</select>
							<b><?php _e('Mode','postworld') ?></b>
							<small><?php _ex('Keep it in production mode unless you know what you are doing.', 'mode switch', 'postworld') ?></small>
						</label>
					</div>
				</div>

			</div>
		</div>


		<?php if( pw_dev_mode() ): ?>
			<hr class="thick">
			<div class="well">
				<h3><i class="pwi-merkaba"></i> <?php _e('Development Mode','postworld') ?></h3>
				<pre><code>pwSiteOptions: {{ pwSiteOptions | json }}</code></pre>
			</div>
		<?php endif; ?>

	</div>
</div>