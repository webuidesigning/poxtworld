<!-- SAVE BUTTON -->
<button
	pw-save-option
	ng-click='saveOption("<?php echo $vars['option_name']; ?>","<?php echo $vars['option_model']; ?>",<?php echo json_encode($vars['callbacks']) ;?>)'
	class="button button-primary">
	<span ng-show="status != 'saving'"><i class="pwi-disk" style="opacity:.5"></i> &nbsp; Save</span>
	<span ng-show="status == 'saving'"><i class="pwi-spinner-2 pwi-spin"></i> &nbsp; Save</span>
</button>