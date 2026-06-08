<?php
/**
 * Table for the Entries List page.
 *
 * @since 1.8.9
 *
 * @var array $entries Entry sample data.
 */

$time = time();
?>
<table class="wp-list-table widefat striped table-view-list has-many-columns wpforms-table-container">
	<thead>
		<tr class="ui-sortable">
			<td id="cb" class="manage-column column-cb check-column wpforms-input-disabled">
				<label class="screen-reader-text" for="cb-select-all-1">Select All</label>
				<input id="cb-select-all-1" type="checkbox">
			</td>
			<th scope="col" id="indicators" class="manage-column column-indicators"></th>
			<th scope="col" id="wpforms_field_1" class="manage-column column-wpforms_field_1 column-primary">Name</th>
			<th scope="col" id="wpforms_field_2" class="manage-column column-wpforms_field_2">Email</th>
			<th scope="col" id="date" class="manage-column column-date">Date</th>
			<th scope="col" id="actions" class="manage-column column-actions wpforms-input-disabled" aria-disabled="true" style="position: relative";>
				Actions
				<a href="#" title="Change columns to display" id="wpforms-list-table-ext-edit-columns-cog"><i class="fa fa-cog" aria-hidden="true"></i></a>
				<?php echo wpforms_render( 'admin/entries/overview/actions' ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</th>
		</tr>
	</thead>
	<tbody id="the-list" data-wp-lists="list:entry">
		<?php foreach ( $entries as $entry ) { ?>
			<tr>
				<th scope="row" class="check-column wpforms-input-disabled"><input type="checkbox" name="entry_id[]" value=""></th>
				<td class="indicators column-indicators has-row-actions wpforms-input-disabled" aria-disabled="true" data-colname="">
					<span class="indicator-star <?php echo ! empty( $entry['star'] ) ? 'unstar' : 'star'; ?>" aria-label="Star entry"><span class="dashicons dashicons-star-filled"></span></span>
					<span class="indicator-read <?php echo ! empty( $entry['read'] ) ? 'read' : 'unread'; ?>" aria-label="Mark entry read"></span>
				</td>
				<td class="wpforms_field_1 column-wpforms_field_1 column-primary" data-colname="Name">
					<?php echo esc_html( $entry['name'] ); ?>
					<button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button>
				</td>
				<td class="wpforms_field_2 column-wpforms_field_2" data-colname="Email"><?php echo esc_html( strtolower( str_replace( ' ', '.', $entry['name'] ) ) . '@example.com' ); ?> </td>
				<td class="date column-date" data-colname="Date"><?php echo esc_html( gmdate( 'm/d/Y h:i A', wp_rand( 1704067200, $time ) ) ); ?></td>
				<td class="actions column-actions" data-colname="Actions">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpforms-entries&view=sample' ) ); ?>" title="View Form Entry" class="view">View</a>
					<span class="sep">|</span>
					<span class="wpforms-input-disabled" aria-disabled="true"><a href="#" title="Edit Form Entry" class="edit">Edit</a></span>
					<span class="sep">|</span>
					<span class="wpforms-input-disabled" aria-disabled="true"><a href="#" title="Spam Form Entry" class="spam">Spam</a></span>
					<span class="sep">|</span>
					<span class="wpforms-input-disabled" aria-disabled="true"><a href="#" title="Delete Form Entry" class="trash">Trash</a></span>
				</td>
			</tr>
		<?php } ?>
	</tbody>
	<tfoot>
		<tr>
			<td class="manage-column column-cb check-column wpforms-input-disabled">
				<label class="screen-reader-text" for="cb-select-all-2">Select All</label>
				<input id="cb-select-all-2" type="checkbox">
			</td>
			<th scope="col" class="manage-column column-indicators"></th>
			<th scope="col" class="manage-column column-wpforms_field_0 column-primary">Name</th>
			<th scope="col" class="manage-column column-wpforms_field_1">Email</th>
			<th scope="col" class="manage-column column-date">Date</th>
			<th scope="col" class="manage-column column-actions">Actions
			</th>
		</tr>
	</tfoot>
</table>
