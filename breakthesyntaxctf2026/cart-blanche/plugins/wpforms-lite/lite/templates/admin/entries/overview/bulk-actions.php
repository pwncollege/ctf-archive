<?php
/**
 * Bulk action for the Entries List page.
 *
 * @since 1.8.9
 *
 * @var bool $show_filter Whether to show the filter block.
 */
?>
<div class="alignleft actions bulkactions wpforms-input-disabled" aria-disabled="true">
	<label for="bulk-action-selector-top" class="screen-reader-text">Select bulk action</label>
	<select name="action" id="bulk-action-selector-top">
		<option value="-1">Bulk actions</option>
	</select>
	<input type="submit" id="doaction" class="button action" value="Apply">
</div>

<?php if ( ! empty( $show_filter ) ) { ?>
<div class="alignleft actions wpforms-filter-date wpforms-input-disabled" aria-disabled="true">
	<input class="regular-text wpforms-filter-date-selector form-control input active" placeholder="Select a date range" tabindex="0" type="text" readonly="readonly">
	<button type="submit" name="action" value="filter_date" class="button">Filter</button>
</div>
<?php } ?>
<div class="tablenav-pages one-page wpforms-input-disabled" aria-disabled="true">
	<span class="displaying-num">12 items</span>
	<span class="pagination-links">
		<span class="tablenav-pages-navspan button disabled" aria-hidden="true">«</span>
		<span class="tablenav-pages-navspan button disabled" aria-hidden="true">‹</span>
		<span class="paging-input">
			<label for="current-page-selector" class="screen-reader-text">Current Page</label>
			<input class="current-page" id="current-page-selector" type="text" name="paged" value="1" size="1" aria-describedby="table-paging">
			<span class="tablenav-paging-text"> of <span class="total-pages">1</span></span>
		</span>
		<span class="tablenav-pages-navspan button disabled" aria-hidden="true">›</span>
		<span class="tablenav-pages-navspan button disabled" aria-hidden="true">»</span>
	</span>
</div>
<br class="clear">
