<?php
global $pw;
$pwInject = $pw['inject'];
// Get icons if they're defined
$icons = _get( $vars, 'icons' );
$controller_id = 'iconDataCtrl_'.pw_random_string();

pw_print_ng_controller(array(
	'app' => 'postworldAdmin',
	'controller' => $controller_id,
	'vars' => array(
		'customIconOptions' => $icons,
		),
	));

?>

<div class="postworld" ng-controller="<?php echo $controller_id ?>">

	<!-- DROPDOWN -->
	<div
		uib-dropdown
		class="dropdown select-icon"
		pw-ui
		pw-admin-icon-data>

		<!-- SELECTED ITEM -->
		<span uib-dropdown-toggle>
			<button
				type="button"
				class="area-select area-select-icon"
				ng-click="uiFocusElement('#filterString')"
				ng-show="uiBool(<?php echo $vars['ng_model']; ?>)">
				<i
					ng-show="uiBool(<?php echo $vars['ng_model']; ?>)"
					ng-class="<?php echo $vars['ng_model']; ?>"
					class="<?php if( $vars['icon_spin'] == true ) echo 'icon-spin' ?>">
				</i>
			</button>

			<button
				type="button"
				class="button"
				ng-click="uiFocusElement('#filterString')"
				ng-hide="uiBool(<?php echo $vars['ng_model']; ?>)">
				<i class="pwi-target"></i>
				<?php _e( 'Select an Icon', 'postworld' ) ?>
			</button>
		</span>

		<!-- MENU -->
		<div class="dropdown-menu grid" role="menu" aria-labelledby="dLabel" >

			<?php
			///// CUSTOM ICONS ARRAY /////
			if( is_array( $icons ) ) : ?>
				<ul class="iconset">
					<li
						class="select-icon"
						ng-repeat="icon in customIconOptions"
						ng-click="<?php echo $vars['ng_model']; ?> = icon">
						<i
							class="{{ icon }}"></i>
					</li>
				</ul>
			<?php endif; ?>



			<?php
			///// REGISTERED ICONSETS /////
			if( !is_array( $icons ) ) : ?>

				<div class="search-input-wrapper">
					<i class="input-icon pwi-search"></i>
					<input
						class="input-icon-left"
						id="filterString"
						type="text"
						ng-model="filterString"
						placeholder="Search Icons..."
						prevent-default-click
						stop-propagation-click>
				</div>

				<div ng-repeat="iconset in iconsets">
					<div
						class="iconset-wrapper"
						ng-show="filterIconset(iconset.classes)">
						<h4>
							{{ iconset.name }}
						</h4>
						<ul class="iconset">
							<li
								class="select-icon"
								ng-repeat="icon in iconset.classes"
								ng-show="filterIcons(icon)"
								ng-click="<?php echo $vars['ng_model']; ?> = icon"
								ng-class="iconSelectedClass(icon,<?php echo $vars['ng_model']; ?>)">
								<i class="{{ icon }}"></i>
							</li>
						</ul>
					</div>
				</div>

			<?php endif; ?>

		</div>

		<button
			class="button select-icon-none"
			style="vertical-align:top;"
			ng-show="uiBool(<?php echo $vars['ng_model']; ?>)"
			ng-click="<?php echo $vars['ng_model']; ?> = ''"
			prevent-default-click>
			<span><i class="pwi-close"></i></span>
		</button>
	</div>
</div>