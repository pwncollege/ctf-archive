<?php
/**
 * Display reset filter notice above the payment table.
 *
 * @since 1.8.4
 *
 * @var string $total           Total number of payments.
 * @var array  $applied_filters Applied filters.
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

// Leave early if no filters are applied.
if ( empty( $applied_filters ) ) {
	return;
}

?>
<div id="wpforms-reset-filter" class="wpforms-reset-filter">
	<?php
	printf(
		wp_kses( /* translators: %d - number of payments found. */
			_n(
				'Found <strong>%d payment</strong>',
				'Found <strong>%d payments</strong>',
				$total,
				'wpforms-lite'
			),
			[
				'strong' => [],
			]
		),
		(int) $total
	);

	$is_more_than_one_filter = count( $applied_filters ) > 1;
	$last_applied_filter     = end( $applied_filters );

	// Display applied filters in a safe way.
	foreach ( $applied_filters as $filter ) :

		// Skip empty filters with no results.
		if ( empty( $filter['results'] ) ) {
			continue;
		}

		echo wp_kses( $filter['results'], [ 'em' => [] ] );
		?>
		<a
			class="reset fa fa-times-circle"
			href="<?php echo esc_url( $filter['reset_url'] ); ?>"
			title="<?php esc_attr_e( 'Reset search', 'wpforms-lite' ); ?>"
		></a>
	<?php
	// Add "and" after the first filter if there are more than one and not the last one.
	if ( $is_more_than_one_filter && $filter !== $last_applied_filter ) {
		esc_html_e( 'and', 'wpforms-lite' );
	}

	endforeach;
	?>
</div>

<?php
/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */
