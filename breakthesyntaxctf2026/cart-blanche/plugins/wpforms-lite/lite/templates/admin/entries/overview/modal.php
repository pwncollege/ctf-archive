<?php
/**
 * Modal for the Entries List page.
 *
 * @since 1.8.9
 *
 * @var bool $is_lite_connect_enabled Whether Lite Connect is enabled.
 * @var bool $is_lite_connect_allowed Whether Lite Connect is allowed.
 * @var int $entries_count Entries count.
 * @var string $enabled_since Enabled since.
 * @var bool $is_enabled Whether sample entries are enabled.
 */
?>
<div id="wpforms-sample-entry-modal" class="wpforms-sample-entries-modal entries-modal" style="<?php echo $is_enabled ? esc_attr( 'display: none' ) : ''; ?>">
	<?php if ( ! $is_lite_connect_enabled ) : ?>
		<div class="entries-modal-content-top-notice">
			<i class="wpforms-icon"></i><?php esc_html_e( 'Form entries are not stored in WPForms Lite.', 'wpforms-lite' ); ?>
		</div>
	<?php endif; ?>
	<div class="entries-modal-content">
		<h2>
			<?php esc_html_e( 'View and Manage Your Form Entries inside WordPress', 'wpforms-lite' ); ?>
		</h2>
		<p>
			<?php esc_html_e( 'Once you upgrade to WPForms Pro, all future form entries will be stored in your WordPress database and displayed on this Entries screen.', 'wpforms-lite' ); ?>
		</p>
		<div class="wpforms-clear">
			<ul class="left">
				<li><i class="fa fa-check" aria-hidden="true"></i> <?php esc_html_e( 'View Entries in Dashboard', 'wpforms-lite' ); ?></li>
				<li><i class="fa fa-check" aria-hidden="true"></i> <?php esc_html_e( 'Export Entries in a CSV File', 'wpforms-lite' ); ?></li>
				<li><i class="fa fa-check" aria-hidden="true"></i> <?php esc_html_e( 'Add Notes / Comments', 'wpforms-lite' ); ?></li>
				<li><i class="fa fa-check" aria-hidden="true"></i> <?php esc_html_e( 'Save Favorite Entries', 'wpforms-lite' ); ?></li>
			</ul>
			<ul class="right">
				<li><i class="fa fa-check" aria-hidden="true"></i> <?php esc_html_e( 'Mark Read / Unread', 'wpforms-lite' ); ?></li>
				<li><i class="fa fa-check" aria-hidden="true"></i> <?php esc_html_e( 'Print Entries', 'wpforms-lite' ); ?></li>
				<li><i class="fa fa-check" aria-hidden="true"></i> <?php esc_html_e( 'Resend Notifications', 'wpforms-lite' ); ?></li>
				<li><i class="fa fa-check" aria-hidden="true"></i> <?php esc_html_e( 'See Geolocation Data', 'wpforms-lite' ); ?></li>
			</ul>
		</div>
	</div>
	<div class="entries-modal-button">
		<?php if ( $is_lite_connect_enabled && $is_lite_connect_allowed ) : ?>

			<p class="entries-modal-button-before">
				<?php

				printf(
					'<strong>' . esc_html( /* translators: %d - backed up entries count. */
						_n(
							'%d entry has been backed up',
							'%d entries have been backed up',
							$entries_count,
							'wpforms-lite'
						)
					) . '</strong>',
					absint( $entries_count )
				);

				if ( ! empty( $enabled_since ) ) {
					echo '<br>';
					printf(
						/* translators: %s - time when Lite Connect was enabled. */
						esc_html__( 'since you enabled Lite Connect on %s', 'wpforms-lite' ),
						esc_html( wpforms_date_format( $enabled_since, '', true ) )
					);
				}
				// phpcs:ignore Squiz.PHP.EmbeddedPhp.ContentAfterEnd
				?>.</p>
			<a href="<?php echo esc_url( wpforms_admin_upgrade_link( 'entries', 'Upgrade to WPForms Pro & Restore Form Entries Button' ) ); ?>" class="wpforms-btn wpforms-btn-lg wpforms-btn-orange wpforms-upgrade-modal" target="_blank" rel="noopener noreferrer">
				<?php esc_html_e( 'Upgrade to WPForms Pro & Restore Form Entries', 'wpforms-lite' ); ?>
			</a>

		<?php else : ?>

			<a href="<?php echo esc_url( wpforms_admin_upgrade_link( 'entries', 'Upgrade to WPForms Pro Now Button' ) ); ?>" class="wpforms-btn wpforms-btn-lg wpforms-btn-orange wpforms-upgrade-modal" target="_blank" rel="noopener noreferrer">
				<?php esc_html_e( 'Upgrade to WPForms Pro Now', 'wpforms-lite' ); ?>
			</a>

		<?php endif; ?>

		<p class="wpforms-entries-sample">
			<a id="wpforms-entries-explore" href="#">
				<?php esc_html_e( 'Explore Entries & Learn More', 'wpforms-lite' ); ?>
			</a>
		</p>
	</div>
</div>
