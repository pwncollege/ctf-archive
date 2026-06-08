<?php
/**
 * HTML for the columns multiselect menu.
 *
 * @since 1.8.9
 */
?>
<div class="wpforms-entries-settings-menu" aria-expanded="true">
	<div class="wpforms-entries-settings-menu-wrap wpforms-entries-settings-menu-items wpforms-input-disabled" aria-disabled="true">
		<span class="wpforms-settings-title first">Form Fields</span>
		<label role="option"><input type="checkbox" value="1" aria-disabled="false" checked><span>Name</span></label>
		<label role="option"><input type="checkbox" value="1" aria-disabled="false" checked><span>Email</span></label>

		<span class="wpforms-settings-title">Entry Meta</span>
		<label role="option"><input type="checkbox" value="1" aria-disabled="false" checked><span>Date</span></label>
		<label role="option"><input type="checkbox" value="1" aria-disabled="false"><span>Entry ID</span></label>
		<label role="option"><input type="checkbox" value="1" aria-disabled="false"><span>Entry Type</span></label>
		<label role="option"><input type="checkbox" value="1" aria-disabled="false"><span>User IP</span></label>
		<label role="option"><input type="checkbox" value="1" aria-disabled="false"><span>User Agent</span></label>
		<label role="option"><input type="checkbox" value="1" aria-disabled="false"><span>Unique User ID</span></label>
	</div>

	<button type="button" class="button button-secondary" id="wpforms-list-table-ext-edit-columns-select-submit" data-value="save-table-columns">Save Changes</button>
</div>
