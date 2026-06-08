<?php
/**
 * Display extra filters for the payment table.
 *
 * @since 1.8.4
 *
 * @var string $filters Tablenav filters.
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

// Leave early if no filters are provided.
if ( empty( $filters ) ) {
	return;
}

// Allowed HTML tags, and attributes for the filters.
$allowed_filters_attributes = [
	'select' => [
		'name'          => [],
		'class'         => [],
		'multiple'      => [],
		'placeholder'   => [],
		'data-settings' => [],
	],
	'option' => [
		'value'    => [],
		'selected' => [],
	],
];

?>
<div class="wpforms-tablenav-filters">
	<?php echo wp_kses( $filters, $allowed_filters_attributes ); ?>
	<input type="submit" class="button" value="<?php esc_attr_e( 'Filter', 'wpforms-lite' ); ?>">
</div>
<?php
/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */
