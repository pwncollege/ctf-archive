<?php
/**
 * Sample data upsell notice.
 *
 * @since 1.8.9
 *
 * @var string $btn_utm  UTM link for the button.
 * @var string $link_utm UTM link for the text link.
 */
?>
<div class="wpforms-admin-content">
	<div class="wpforms-sample-notification">
		<div class="wpforms-sample-notification-content">
			<h2>You’re Viewing Sample Data</h2>
			<p>Like what you see? <a href="<?php echo esc_url( $link_utm ); ?>">Upgrade to Pro</a> to get access to Entries and dozens of awesome features and addons!</p>
		</div>
		<p class="notice-buttons">
			<a class="wpforms-btn wpforms-btn-orange wpforms-btn-md" href="<?php echo esc_url( $btn_utm ); ?>">Upgrade Now</a>
		</p>
		<a id="wpforms-hide-sample-data" href="#"><span class="dashicons dashicons-hidden"></span> Hide Sample Data</a>
	</div>
</div>
