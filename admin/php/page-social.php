<script>
	postworldAdmin.controller( 'pwSocialDataCtrl',
		[ '$scope', 'iOptionsData', function( $scope, $iOptionsData ){
		// Social Option Values
		$scope.pwSocial = <?php echo json_encode( pw_get_option( array( 'option_name' => PW_OPTIONS_SOCIAL ) ) ); ?>;
		$scope['options'] = $iOptionsData['options'];

		// Social Meta Data
		$scope.socialMeta = <?php echo json_encode( pw_social_meta() ); ?>;
	}]);
</script>

<div class="postworld wrap social" ng-cloak>

	<h1>
		<i class="pwi-profile"></i>
		<?php _ex('Social','module','postworld' )?>
	</h1>
	
	<hr class="thick">

	<div
		pw-admin-social
		ng-controller="pwSocialDataCtrl"
		ng-cloak>

		<!-- SHARE SOCIAL -->
		<div class="well">
			<div class="save-right">
				<?php pw_save_option_button( PW_OPTIONS_SOCIAL, 'pwSocial'); ?>
			</div>
			<h2>
				<i class="pwi-share"></i>
				<?php _ex('Sharing','social sharing','postworld' )?>
			</h2>
			<small><?php _e('Include share links on each post for the following networks:','postworld' )?></small>
			<hr class="thin">
			<?php echo pw_share_social_options(); ?>
			<div style="clear:both"></div>
		</div>


		<!-- SOCIAL WIDGETS -->
		<div class="well">
			<div class="save-right">
				<?php pw_save_option_button( PW_OPTIONS_SOCIAL, 'pwSocial'); ?>
			</div>
			<h2>
				<i class="pwi-cube"></i>
				<?php _ex('Social Widgets','like button, tweet button, etc','postworld' )?>
			</h2>
			<small><?php _ex('Customize which sharing widgets appear on each post.','like button, tweet button, etc','postworld' )?></small>
			<hr class="thin">
			
			<div class="well">
				<label>
					<input type="checkbox" ng-model="pwSocial.widgets.facebook.enable">
					<i class="icon pwi-facebook"></i>
					<b><?php _e('Facebook Like Button','postworld' )?></b>
				</label>
				<div class="indent" ng-show="pwSocial.widgets.facebook.enable">
					<hr class="thin">
					<label>
						<input type="checkbox" ng-model="pwSocial.widgets.facebook.settings.share">
						<?php _ex('Include share button','next to facebook like button','postworld' )?>
					</label>
				</div>
			</div>

			<div class="well">
				<label>
					<input type="checkbox" ng-model="pwSocial.widgets.twitter.enable">
					<i class="icon pwi-twitter"></i>
					<b><?php _e('Twitter Tweet Button','postworld' )?></b>
				</label>
			</div>

		</div>

		<!-- FIELDS -->
		<div class="well">
			<!-- NG REPEAT : SECTIONS -->
			<div 
				ng-repeat="sectionMeta in socialMeta">
				<!-- SAVE BUTTON -->
				<div class="save-right"><?php pw_save_option_button( PW_OPTIONS_SOCIAL,'pwSocial'); ?></div>
				<h2><i class="{{ sectionMeta.icon }}"></i> {{ sectionMeta.name }}</h2>
				<table class="form-table pad">
					<tr ng-repeat="inputMeta in sectionMeta.fields"
						valign="top"
						class="module layout">
						<th scope="row">
							<b>
								<span class="icon-md"><i class="{{inputMeta.icon}}"></i></span>
								{{inputMeta.name}}
							</b>
						</th>
						<td>
							
							<!-- PROPERTIES -->
							<div>
								<input
									type="text"
									ng-model="pwSocial[sectionMeta.id][inputMeta.id]">
									<small>{{ inputMeta.description }}</small>
							</div>
						</td>
					</tr>
				</table>
				<hr>
			</div>
		</div>

		<?php if( pw_dev_mode() ): ?>
			<hr class="thick">
			<div class="well">
				<h3><i class="pwi-merkaba"></i> <?php _e('Development Mode','postworld') ?></h3>
				<pre><code>PW_OPTIONS_SOCIAL : wp_options.<?php echo PW_OPTIONS_SOCIAL ?> : $scope.pwSocial : {{ pwSocial | json }}</code></pre>
				<pre><code>socialMeta : {{ socialMeta | json }}</code></pre>
			</div>
		<?php endif; ?>

	</div>

</div>