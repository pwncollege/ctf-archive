<?php
/**
 * Payments Overview Mode Toggle template.
 *
 * @since 1.8.2
 *
 * @var string $mode Current mode (live or test).
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

?>
<span id="wpforms-payments-overview-mode-toggle" class="wpforms-toggle-control">
	<input type="checkbox" id="wpforms-payments-mode-toggle" name="wpforms-payments-mode-toggle" value="1" <?php checked( $mode, 'test' ); ?>>
	<label class="wpforms-toggle-control-icon" for="wpforms-payments-mode-toggle"></label>
	<label
		for="wpforms-payments-mode-toggle"
		aria-label="<?php esc_attr_e( 'Toggle between live and test data', 'wpforms-lite' ); ?>"
	>
		<?php esc_html_e( 'Test Data', 'wpforms-lite' ); ?>
	</label>
</span>
