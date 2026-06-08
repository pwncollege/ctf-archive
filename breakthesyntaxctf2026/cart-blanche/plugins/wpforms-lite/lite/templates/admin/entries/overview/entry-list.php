<?php
/**
 * Entries List page with sample data.
 *
 * @since 1.8.9
 *
 * @var bool $is_lite_connect_enabled Whether Lite Connect is enabled.
 * @var bool $is_lite_connect_allowed Whether Lite Connect is allowed.
 * @var int $entries_count Entries count.
 * @var string $enabled_since Enabled since.
 * @var array $sample_entries Sample entries.
 * @var array $utm UTM data.
 */

$sample_entry_enabled = ! empty( $_COOKIE['wpforms_sample_entries'] );

?>
<div id="wpforms-entries-list" class="wrap wpforms-admin-wrap wpforms-entries-list-upgrade <?php echo $sample_entry_enabled ? esc_attr( 'wpforms-entires-sample-view' ) : ''; ?>">
	<h1 class="page-title">Entries</h1>
	<div id="wpforms-sample-entry-main-notice" class="wpforms-sample-entry-notice <?php echo $sample_entry_enabled ? esc_attr( 'wpf-no-animate' ) : ''; ?>">
		<?php
		echo wpforms_render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'admin/entries/notice',
			[
				'btn_utm'  => $utm['entries_list_button'],
				'link_utm' => $utm['entries_list_link'],
			],
			true
		);
		?>
	</div>
	<div class="wpforms-admin-content-wrap">

		<?php
		echo wpforms_render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'admin/entries/overview/modal',
			[
				'is_lite_connect_enabled' => $is_lite_connect_enabled,
				'is_lite_connect_allowed' => $is_lite_connect_allowed,
				'entries_count'           => $entries_count,
				'enabled_since'           => $enabled_since,
				'is_enabled'              => $sample_entry_enabled,
			],
			true
		);
		?>
		<div id="wpforms-sample-entries-main" class="wpforms-admin-content">
			<div class="form-details">
				<span class="form-details-sub">Select Form</span>
				<h3 class="form-details-title">
					General Inquiry Form
					<div class="form-selector wpforms-input-disabled" aria-disabled="true">
						<a href="#" class="toggle dashicons dashicons-arrow-down-alt2"></a>
					</div>
				</h3>
				<div class="form-details-actions wpforms-input-disabled" aria-disabled="true">
					<a href="#" class="form-details-actions-entries"><span class="dashicons dashicons-list-view"></span> All Entries	</a>
					<a href="#" class="form-details-actions-edit"><span class="dashicons dashicons-edit"></span> Edit This Form</a>
					<a href="#" class="form-details-actions-preview"><span class="dashicons dashicons-visibility"></span> Preview Form</a>
					<a href="#" class="form-details-actions-export"><span class="dashicons dashicons-migrate"></span> Export All</a>
					<a href="#" class="form-details-actions-read"><span class="dashicons dashicons-marker"></span> Mark All Read</a>
					<a href="#" class="form-details-actions-removeall"><span class="dashicons dashicons-trash"></span>Trash All</a>
				</div>
			</div>
			<form id="wpforms-entries-table">
				<?php
				echo wpforms_render( 'admin/entries/overview/header' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

				echo wpforms_render(  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					'admin/entries/overview/table',
					[ 'entries' => $sample_entries ],
					true
				);
				?>
			</form>
			<div class="tablenav bottom">
				<?php
				echo wpforms_render(  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					'admin/entries/overview/bulk-actions'
				);
				?>
			</div>
		</div>
	</div>
</div>
<div class="clear"></div>
