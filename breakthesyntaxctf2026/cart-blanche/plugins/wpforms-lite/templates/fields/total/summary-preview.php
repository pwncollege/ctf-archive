<?php
/**
 * The Order Summary preview for the Total payment field.
 *
 * @since 1.8.7
 *
 * @var array  $items       Order items.
 * @var array  $foot        Order footer (subtotal, discount, total).
 * @var string $total_width Total width.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rows_html   = '';
$total_width = $total_width ? 'width:' . $total_width . 'ch;' : '';
$fields      = array_merge( (array) $items, (array) $foot );

// Display the placeholder by default.
$is_placeholder_visible = true;

// $context is set for the smart tag.
if ( isset( $context ) ) {
	$is_placeholder_visible = empty( $items );
}

// Go through table items (rows).
foreach ( $fields as $field ) :

	// Open a row.
	$rows_html .= sprintf(
		'<tr %1$s %2$s>',
		wpforms_html_attributes( '', isset( $field['class'] ) ? (array) $field['class'] : [] , $field['data'] ?? [] ),
		! empty( $field['is_hidden'] ) ? 'style="display:none;"' : ''
	);

	// Item column.
	$rows_html .= sprintf(
		'<td class="wpforms-order-summary-item-label" valign="top">%s</td>',
		wp_kses_post( $field['label'] )
	);

	// Quantity column.
	$rows_html .= sprintf(
		'<td class="wpforms-order-summary-item-quantity" valign="top">%s</td>',
		esc_html( $field['quantity'] )
	);

	// Price column.
	$rows_html .= sprintf(
		'<td class="wpforms-order-summary-item-price" valign="top" style="%1$s">%2$s</td>',
		esc_attr( $total_width ),
		esc_html( $field['amount'] )
	);

	// Close a row.
	$rows_html .= '</tr>';

endforeach;

$visible_items = array_filter(
	$items,
	function ( $item ) {
		return ! isset( $item['is_hidden'] ) || $item['is_hidden'] === false;
	}
);

$is_placeholder_visible = empty( $visible_items );

$placeholder_display = $is_placeholder_visible ? 'display: table-row;' : 'display: none;';
$placeholder_classes = $is_placeholder_visible ? 'wpforms-order-summary-placeholder' : 'wpforms-order-summary-placeholder wpforms-order-summary-placeholder-hidden';

?>
<div class="wpforms-order-summary-container">
	<table class="wpforms-order-summary-preview" cellpadding="0" cellspacing="0" width="100%" role="presentation">
		<caption style="display: none;"><?php esc_html_e( 'Order Summary', 'wpforms-lite' ); ?></caption>
		<thead>
			<tr>
				<th class="wpforms-order-summary-item-label" valign="top"><?php esc_html_e( 'Item', 'wpforms-lite' ); ?></th>
				<th class="wpforms-order-summary-item-quantity" valign="top">
					<span class="wpforms-order-summary-item-quantity-label-full"><?php esc_html_e( 'Quantity', 'wpforms-lite' ); ?></span>
					<span class="wpforms-order-summary-item-quantity-label-short"><?php esc_html_e( 'Qty', 'wpforms-lite' ); ?></span>
				</th>
				<th class="wpforms-order-summary-item-price" valign="top" style="<?php echo esc_attr( $total_width ); ?>"><?php esc_html_e( 'Total', 'wpforms-lite' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr class="<?php echo esc_attr( $placeholder_classes ); ?>" style="<?php echo esc_attr( $placeholder_display ); ?>">
				<td colspan="3" valign="top"><?php echo esc_html__( 'There are no products selected.', 'wpforms-lite' ); ?></td>
			</tr>
			<?php echo $rows_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</tbody>
	</table>
</div>
