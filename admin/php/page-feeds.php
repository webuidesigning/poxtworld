<?php
	///// GET DATA /////
	// Feeds
	$pwFeeds = pw_get_option( array( 'option_name' => PW_OPTIONS_FEEDS ) );
	// Feed Settings
	$pwFeedSettings = i_get_option( array( 'option_name' => PW_OPTIONS_FEED_SETTINGS ) );
	// Feed Templates
	$htmlFeedTemplates = pw_get_templates(
		array(
			'subdirs' => array('feeds'),
			'path_type' => 'url',
			'ext'=>'html',
			)
		)['feeds'];	
	// Aux Feed Templates
	$phpFeedTemplates = pw_get_templates(
		array(
			'subdirs' => array('feeds'),
			'path_type' => 'url',
			'ext'=>'php',
			)
		)['feeds'];
?>
<script>
	postworldAdmin.controller( 'pwFeedsDataCtrl',
		[ '$scope', '_', '$timeout', 'pwPostOptions',
		function( $scope, $_, $timeout, $pwPostOptions ){
		$scope.pwFeeds = <?php echo json_encode( $pwFeeds ); ?>;
		$scope.pwFeedSettings = <?php echo json_encode( $pwFeedSettings ); ?>;
		if( _.isEmpty($scope.pwFeedSettings) )
			$scope.pwFeedSettings = {};

		$scope.htmlFeedTemplates = <?php echo json_encode( $htmlFeedTemplates ); ?>;
		$scope.phpFeedTemplates = <?php echo json_encode( $phpFeedTemplates ); ?>;
		$scope.contexts = <?php echo json_encode( pw_get_contexts( array( 'default', 'standard', 'archive', 'search', 'taxonomy', 'post-type' ) ) ); ?>;
	
		// Watch Feed Settings
		$scope.$watch( 'pwFeedSettings', function(val){
			// Delete empty values
			$_.removeEmpty( $scope.pwFeedSettings );
		}, 1);

		// Watch Feed Settings
		$scope.$watch( 'pwFeeds', function(val){
			// Delete empty values
			$_.removeEmpty( $scope.pwFeeds );
		}, 1);

		// Get Taxonomy Terms
		$pwPostOptions.taxTerms( $scope, 'taxTerms' );


		///// TRANSFORM QUERIES /////
		$scope.addTaxQuery = function( query ){
			if( !$_.objExists( query, 'tax_query' ) )
				query.tax_query = [];
			var addTaxQuery = {
				query_id: $_.randomString(8),
				include_children: true,
				operator: 'IN',
				field: 'term_id'
			};
			query.tax_query.push(addTaxQuery);
		}
		$scope.removeTaxQuery = function( query, taxQuery ){
			query.tax_query = _.reject(
				query.tax_query,
				function( thisQuery ){ return ( thisQuery.query_id == taxQuery.query_id ); }
				);
		}

	}]);
</script>

<div class="postworld feeds wrap" ng-cloak>
	<div
		pw-admin
		pw-admin-feeds
		ng-controller="pwFeedsDataCtrl"
		ng-cloak>

		<!--{{taxTerms}}-->

		
		<h1>
			<i class="pwi-th-small"></i>
			Feeds
			<button class="add-new-h2" ng-click="newFeed()">Add New Feed</button>
		</h1>

		<hr class="thick">

		<div class="pw-row">

			<!-- ///// ITEMS MENU ///// -->
			<div class="pw-col-3">
				<ul class="list-menu">
					<li
						ng-click="selectItem('settings');"
						ng-class="menuClass('settings')">
						<i class="pwi-gear"></i> Settings
					</li>
				</ul>
					<hr class="thin">
				<ul class="list-menu">
					<li
						ng-repeat="item in pwFeeds"
						ng-click="selectItem(item)"
						ng-class="menuClass(item)">
						{{ item.name }}
					</li>
				</ul>
				<div class="space-6"></div>
			</div>

			<div class="pw-col-9">
				<!-- ///// EDIT SETTINGS ///// -->
				<div ng-show="showView('settings')">
					
					<div class="well">
						<!-- SAVE BUTTON -->
						<div class="save-right"><?php pw_save_option_button( PW_OPTIONS_FEED_SETTINGS,'pwFeedSettings'); ?></div>
		
						<h3>Contexts</h3>

						<table
							width="100%"
							pw-ui
							ui-views="{}">
							<tr ng-repeat="context in contexts"
								valign="top">
								<th scope="row" align="left" width="25%">
									<span
										tooltip="{{context.name}}"
										tooltip-popup-delay="333">
										<i class="{{context.icon}}"></i>
										{{context.label}}
										</th>
									</span>
								<td>

									<button
										type="button"
										class="button"
										ng-class="uiSetClass('template_'+context.name)"
										ng-click="uiToggleView('template_'+context.name)">
										<i class="pwi-th-large"></i>
										Template
									</button>

									<button
										type="button"
										class="button"
										ng-class="uiSetClass('options_'+context.name)"
										ng-click="uiToggleView('options_'+context.name)">
										<i class="pwi-gear"></i>
										Options
									</button>

									<div
										ng-show="uiShowView('template_'+context.name)">
										<?php echo pw_feed_template_options( array( 'ng_model' => 'pwFeedSettings.context[context.name]' ) ); ?>
										<hr class="thin">
										<?php echo pw_feed_variable_options( array( 'ng_model' => 'pwFeedSettings.context[context.name]' ) ); ?>
										<hr class="thin">
									</div>

									<div
										ng-show="uiShowView('options_'+context.name)">
										OPTIONS
									</div>

								</td>
							</tr>
						</table>

					</div>
					

				</div>


				<!-- ///// EDIT SETTINGS ///// -->
				<div ng-show="showView('editItem')">



					<h3><i class="pwi-gear"></i> <?php ___('feeds.item_title'); ?></h3>

					<div class="pw-row">
						<div class="pw-col-6">
							<label
								for="item-name"
								class="inner"
								tooltip="<?php ___('feeds.name_info'); ?>"
								tooltip-popup-delay="333">
								<?php ___('feeds.name') ?>
								<i class="pwi-info-circle"></i>
							</label>
							<input
								id="item-name"
								class="labeled"
								type="text"
								ng-model="selectedItem.name">
						</div>
						<div class="pw-col-6">
							<label
								for="item-id"
								class="inner"
								tooltip="<?php ___('feeds.id_info'); ?>"
								tooltip-popup-delay="333">
								<?php ___('feeds.id') ?>
								<i class="pwi-info-circle"></i>
							</label>
							<button
								class="inner inner-bottom-right inner-controls"
								ng-click="enableInput('#item-id');focusInput('#item-id')"
								tooltip="<?php ___('feeds.id_edit_info'); ?>"
								tooltip-placement="left"
								tooltip-popup-delay="333">
								<i class="pwi-edit"></i>
							</button>
							<input
								id="item-id"
								class="labeled"
								type="text"
								ng-model="selectedItem.id"
								disabled
								pw-sanitize="id"
								ng-blur="disableInput('#item-id');">
						</div>
					</div>

					<div class="pw-row">
						<div class="pw-col-3">
							<label
								for="item-preload"
								class="inner"
								tooltip="<?php ___('feeds.preload_info'); ?>"
								tooltip-popup-delay="333">
								<?php ___('feeds.preload'); ?>
								<i class="pwi-info-circle"></i>
							</label>
							<input
								id="item-preload"
								class="labeled"
								type="number"
								ng-model="selectedItem.preload">
						</div>
						<div class="pw-col-3">
							<label
								for="item-load_increment"
								class="inner"
								tooltip="<?php ___('feeds.increment_info'); ?>"
								tooltip-popup-delay="333">
								<?php ___('feeds.increment'); ?>
								<i class="pwi-info-circle"></i>
							</label>
							<input
								id="item-load_increment"
								class="labeled"
								type="number"
								ng-model="selectedItem.load_increment">
						</div>
						<div class="pw-col-3">
							<label
								for="item-offset"
								class="inner"
								tooltip="<?php ___('feeds.offset_info'); ?>"
								tooltip-popup-delay="333">
								<?php ___('feeds.offset'); ?>
								<i class="pwi-info-circle"></i>
							</label>
							<input
								id="item-offset"
								class="labeled"
								type="number"
								ng-model="selectedItem.offset">
						</div>

					</div>

					<hr class="thin">

					<h3
						tooltip="{{ selectedItem.query | json }}"
						tooltip-popup-delay="333">
						<i class="pwi-search"></i> Query
					</h3>

					<?php echo pw_feed_query_options( array( 'ng_model' => 'selectedItem' ) ); ?>

					<div class="space-2"></div>

					<hr class="thin">
					
					<h3><i class="pwi-cube"></i> <?php ___('feeds.view.title'); ?></h3>
					<?php echo pw_feed_template_options( array( 'ng_model' => 'selectedItem' ) ); ?>
					<hr class="thin">
					<?php echo pw_feed_variable_options( array( 'ng_model' => 'selectedItem' ) ); ?>

					<h3>
						<i class="pwi-code"></i>
						Shortcode
					</h3>
					<input
						type="text"
						class="un-disabled"
						style="width:100%;"
						value='[pw-feed id="{{ selectedItem.id }}"]'
						
						select-on-click>

					<hr class="thick">

					<!-- SAVE BUTTON -->
					<div class="save-right"><?php pw_save_option_button( PW_OPTIONS_FEEDS,'pwFeeds'); ?></div>
		
					<!-- DELETE BUTTON -->
					<button
						class="button deletion"
						ng-click="deleteItem(selectedItem,'pwFeeds')">
						<i class="pwi-close"></i>
						<?php ___('feeds.delete'); ?>
					</button>

					<!-- DUPLICATE BUTTON -->
					<button
						class="button deletion"
						ng-click="duplicateItem(selectedItem,'pwFeeds')">
						<i class="pwi-copy-2"></i>
						<?php ___('feeds.duplicate'); ?>
					</button>

				</div>
			</div>
		</div>

		<hr>

		<hr class="thick">

		<!--
		<pre>pwFeedSettings : {{ pwFeedSettings | json }}</pre>
		<pre>pwFeeds : {{ pwFeeds | json }}</pre>
		-->

		<!--
		RADIO BUTTONS
		<b><i class="pwi-calendar"></i> Events Filter</b>
		<br>
		<div class="btn-group">
			<label
				ng-repeat="option in eventOptions.timeFilter"
				class="btn"
				ng-model="eventInput.timeFilter"
				btn-radio="option.value">
				{{ option.name }}
			</label>
		</div>
		-->



	</div>

</div>