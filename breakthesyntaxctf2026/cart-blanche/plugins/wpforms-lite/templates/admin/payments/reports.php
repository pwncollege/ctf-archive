<?php
/**
 * Payments overview reports (summary metrics).
 * i.e. Total Payments, Total Sales, etc.
 *
 * @since 1.8.2
 *
 * @var string $current   The active stat card upon page load.
 * @var array  $statcards Payments report stat cards (clickable list-items).
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

// Bail early, if stat cards are empty or not assigned.
if ( empty( $statcards ) ) {
	return;
}

?>
<div role="menu" class="wpforms-payments-overview-reports" aria-orientation="horizontal" aria-label="<?php esc_attr_e( 'Payments report indicators', 'wpforms-lite' ); ?>" aria-describedby="wpforms-payments-overview-reports-helptext">
	<p id="wpforms-payments-overview-reports-helptext" class="screen-reader-text">
		<?php esc_html_e( 'List of data points available for filtering. Click a data point for a detailed report.', 'wpforms-lite' ); ?>
	</p>
	<ul class="wpforms-payments-overview-reports-legend">

		<?php
		foreach ( $statcards as $chart => $attributes ) :

			// Skip stat card, if it's not supposed to be displayed.
			if ( isset( $attributes['condition'] ) && ! $attributes['condition'] ) {
				continue;
			}

			$button_classes = ! empty( $attributes['button_classes'] ) ? (array) $attributes['button_classes'] : [];

			// To highlight the stats being displayed in the chart at the moment, identify the selected stat card.
			if ( $chart === $current ) {
				$button_classes[] = 'is-selected';
			}
		?>
			<li class="wpforms-payments-overview-reports-statcard">
				<button class="<?php echo wpforms_sanitize_classes( $button_classes, true ); ?>" data-stats="<?php echo esc_attr( $chart ); ?>">
					<span class="statcard-label"><?php echo esc_html( $attributes['label'] ); ?></span>
					<span class="statcard-value"><?php echo ! empty( $attributes['value'] ) ? esc_html( $attributes['value'] ) : '0'; ?></span>
					<span class="statcard-delta" role="presentation" title="<?php esc_attr_e( 'Comparison to previous period', 'wpforms-lite' ); ?>"><?php echo ! empty( $attributes['delta'] ) ? (int) $attributes['delta'] : ''; ?></span>
				</button>
			</li>
		<?php endforeach; ?>

	</ul>
</div>

<?php
/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */
