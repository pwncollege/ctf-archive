<?php
/**
 * Single Payment page - Payment entry field template.
 *
 * @since 1.8.9
 *
 * @var array $field Field data.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="wpforms-payment-entry-field <?php echo wpforms_sanitize_classes( $field['field_class'] ); ?>" >

	<p class="wpforms-payment-entry-field-name">
		<?php echo esc_html( wp_strip_all_tags( $field['field_name'] ) ); ?>
	</p>

	<div class="wpforms-payment-entry-field-value">
		<?php echo wp_kses_post( nl2br( make_clickable( $field['field_value'] ) ) ); ?>
	</div>
</div>