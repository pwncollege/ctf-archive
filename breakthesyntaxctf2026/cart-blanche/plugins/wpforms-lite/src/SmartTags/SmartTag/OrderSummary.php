<?php

namespace WPForms\SmartTags\SmartTag;

/**
 * Class Order Summary.
 *
 * @since 1.8.7
 */
class OrderSummary extends SmartTag {

	/**
	 * Get smart tag value.
	 *
	 * @since 1.8.7
	 *
	 * @param array  $form_data Form data.
	 * @param array  $fields    List of fields.
	 * @param string $entry_id  Entry ID.
	 *
	 * @return string
	 */
	public function get_value( $form_data, $fields = [], $entry_id = '' ): string {

		if ( empty( $fields ) && ! $entry_id ) {
			return '';
		}

		if ( empty( $fields ) ) {
			$entry  = wpforms()->obj( 'entry' )->get( $entry_id );
			$fields = isset( $entry->fields ) ? (array) wpforms_decode( $entry->fields ) : [];
		}

		$fields = $this->prepare_fields( $fields, $form_data );

		[ $items, $foot, $total_width ] = $this->prepare_payment_fields_data( $fields );

		$preview = wpforms_render(
			'fields/total/summary-preview',
			[
				'items'       => $this->filter_items( $items ),
				'foot'        => $foot,
				'total_width' => $total_width,
				'context'     => 'smart_tag',
			],
			true
		);

		if ( $this->context === 'email' ) {
			// Remove new lines for the legacy Notification template to prevent HTML markup breaks.
			// We remove only new lines before closing HTML tag symbol to keep new lines inside the table content.
			return preg_replace( '/(>$\n)/m', '>', $preview );
		}

		return $preview;
	}

	/**
	 * Filter items.
	 *
	 * @since 1.9.3
	 *
	 * @param array $items Items data.
	 *
	 * @return array
	 */
	private function filter_items( array $items ): array {

		// Bail early if not in notification context.
		if ( $this->context !== 'notification' ) {
			return $items;
		}

		return array_filter(
			$items,
			function ( $item ) {
				// Return items that are not hidden.
				return empty( $item['is_hidden'] );
			}
		);
	}

	/**
	 * Prepare fields data for summary preview.
	 * Add label_hide property to fields if needed.
	 *
	 * @since 1.9.2
	 *
	 * @param array $fields    Fields data.
	 * @param array $form_data Form data and settings.
	 *
	 * @return array
	 */
	private function prepare_fields( array $fields, array $form_data ): array {

		return array_map(
			function ( $field ) use ( $form_data ) {
				return $this->prepare_field( $field, $form_data );
			},
			$fields
		);
	}

	/**
	 * Prepare field data for summary preview.
	 *
	 * @since 1.9.3
	 *
	 * @param array $field     Field data.
	 * @param array $form_data Form data and settings.
	 *
	 * @return array
	 */
	private function prepare_field( array $field, array $form_data ): array {

		$form_data_fields = $form_data['fields'] ?? [];
		$field_data       = $form_data_fields[ $field['id'] ] ?? [];

		if ( isset( $field_data['label_hide'] ) ) {
			$field['label_hide'] = true;
		}

		if ( isset( $field_data['format'] ) && $field_data['format'] === 'hidden' ) {
			$field['is_hidden'] = true;
		}

		return $field;
	}

	/**
	 * Prepare payment fields data for summary preview.
	 *
	 * @since 1.8.7
	 *
	 * @param array $fields Fields data.
	 *
	 * @return array
	 */
	private function prepare_payment_fields_data( array $fields ): array {

		$payment_fields = wpforms_payment_fields();
		$items          = [];
		$coupon         = [];
		$foot           = [];
		$total          = 0;
		$total_width    = 0;

		foreach ( $fields as $field ) {

			if (
				empty( $field['value'] ) ||
				! in_array( $field['type'], $payment_fields, true )
			) {
				continue;
			}

			if ( $field['type'] === 'payment-coupon' ) {
				$coupon = $field;

				continue;
			}

			$this->prepare_single_item( $field, $items, $total );
			$this->prepare_multiple_item( $field, $items, $total );
		}

		$this->prepare_coupon_item( $coupon, $foot, $total, $total_width );

		$total = wpforms_format_amount( $total, true );

		$foot[] = [
			'label'    => __( 'Total', 'wpforms-lite' ),
			'quantity' => '',
			'amount'   => $total,
			'class'    => 'wpforms-order-summary-preview-total',
		];

		// Add two extra characters units to accommodate symbols that can be wider than one character (e.g. “€”),
		// and to normalize the ch-unit width discrepancy between Windows and Unix-based operating systems.
		$total_width = max( $total_width, mb_strlen( html_entity_decode( $total, ENT_COMPAT, 'UTF-8' ) ) + 2 );

		return [ $items, $foot, $total_width ];
	}

	/**
	 * Prepare single item for summary preview.
	 *
	 * @since 1.8.7
	 *
	 * @param array  $field Field data.
	 * @param array  $items Summary items.
	 * @param string $total Form total.
	 */
	private function prepare_single_item( array $field, array &$items, string &$total ) {

		// Single value.
		if ( ! in_array( $field['type'], [ 'payment-single', 'payment-multiple', 'payment-select' ], true ) ) {
			return;
		}

		$quantity = $this->get_payment_field_quantity( $field );

		if ( ! $quantity ) {
			return;
		}

		$value_raw = $field['value_raw'] ?? '';
		/* translators: %s - item number. */
		$value_choice = ! empty( $field['value_choice'] ) ? $field['value_choice'] : sprintf( esc_html__( 'Item %s', 'wpforms-lite' ), $value_raw );

		$label  = ! empty( $value_raw ) ? $field['name'] . ' - ' . $value_choice : $field['name'];
		$amount = $field['amount_raw'] * $quantity;

		$items[] = [
			'label'     => ! empty( $field['label_hide'] ) ? $value_choice : $label,
			'quantity'  => $quantity,
			'amount'    => wpforms_format_amount( $amount, true ),
			'is_hidden' => ! empty( $field['is_hidden'] ),
		];

		$total += $amount;
	}

	/**
	 * Prepare multiple item for summary preview.
	 *
	 * @since 1.8.7
	 *
	 * @param array  $field Field data.
	 * @param array  $items Summary items.
	 * @param string $total Form total.
	 */
	private function prepare_multiple_item( array $field, array &$items, string &$total ) {

		if ( $field['type'] !== 'payment-checkbox' ) {
			return;
		}

		$quantity = $this->get_payment_field_quantity( $field );

		if ( ! $quantity ) {
			return;
		}

		// Multiple values.
		$value_choices = explode( "\n", $field['value'] );

		foreach ( $value_choices as $key => $value_choice ) {

			$choice_data = explode( ' - ', $value_choice );
			$labels      = $this->get_multiple_item_labels( $choice_data, $field, $key );

			$items[] = [
				'label'    => ! empty( $field['label_hide'] ) ? implode( ' - ', $labels ) : $field['name'] . ' - ' . implode( ' - ', $labels ),
				'quantity' => $quantity,
				'amount'   => end( $choice_data ),
			];
		}

		$total += $field['amount_raw'];
	}

	/**
	 * Get multiple item labels.
	 *
	 * @since 1.9.3
	 *
	 * @param array $choice_data Choice data.
	 * @param array $field       Field data.
	 * @param int   $key         Choice key.
	 *
	 * @return array
	 */
	private function get_multiple_item_labels( array $choice_data, array $field, int $key ): array {

		$labels = array_slice( $choice_data, 0, -1 );

		if ( ! empty( $labels ) ) {
			return $labels;
		}

		$raw_values = explode( ',', $field['value_raw'] );
		/* translators: %s - item number. */
		return [ sprintf( esc_html__( 'Item %s', 'wpforms-lite' ), $raw_values[ $key ] ?? '' ) ];
	}

	/**
	 * Prepare coupon item for summary preview.
	 *
	 * @since 1.8.7
	 *
	 * @param array  $coupon      Coupon data.
	 * @param array  $foot        Summary footer.
	 * @param string $total       Form total.
	 * @param string $total_width Total width.
	 */
	private function prepare_coupon_item( array $coupon, array &$foot, string &$total, string &$total_width ) {

		if ( empty( $coupon ) ) {
			return;
		}

		$foot[] = [
			'label'    => __( 'Subtotal', 'wpforms-lite' ),
			'quantity' => '',
			'amount'   => wpforms_format_amount( $total, true ),
			'class'    => 'wpforms-order-summary-preview-subtotal',
		];

		$coupon_label = sprintf( /* translators: %s - Coupon value. */
			__( 'Coupon (%s)', 'wpforms-lite' ),
			$coupon['value']
		);

		$coupon_amount = $this->get_coupon_amount( $coupon );

		$foot[] = [
			'label'    => $coupon_label,
			'quantity' => '',
			'amount'   => $coupon_amount,
			'class'    => 'wpforms-order-summary-preview-coupon-total',
		];

		// Coupon value saved as negative.
		$total += $coupon['amount_raw'];

		$total_width = strlen( html_entity_decode( $coupon_amount, ENT_COMPAT, 'UTF-8' ) );
	}

	/**
	 * Get coupon amount.
	 *
	 * @since 1.8.7
	 *
	 * @param array $coupon Coupon data.
	 *
	 * @return string Formatted coupon amount.
	 */
	private function get_coupon_amount( array $coupon ): string {
		// Coupon amount saved as negative, so we need to format it nicely.
		$coupon_amount = '- ' . wpforms_format_amount( abs( $coupon['amount_raw'] ), true );

		/**
		 * Allow to filter order summary coupon amount.
		 *
		 * @since 1.8.7
		 *
		 * @param string $coupon_amount Coupon amount.
		 * @param array  $coupon        Coupon data.
		 */
		return apply_filters( 'wpforms_smart_tags_smart_tag_order_summary_coupon_amount', $coupon_amount, $coupon );
	}

	/**
	 * Get payment field quantity.
	 *
	 * @since 1.8.7
	 *
	 * @param array $field Field data.
	 *
	 * @return int
	 */
	private function get_payment_field_quantity( array $field ): int {
		// phpcs:ignore WPForms.Formatting.EmptyLineBeforeReturn.RemoveEmptyLineBeforeReturnStatement
		return isset( $field['quantity'] ) ? (int) $field['quantity'] : 1;
	}
}
