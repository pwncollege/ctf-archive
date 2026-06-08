<?php
/**
 * Number Slider field builder preview.
 *
 * @since 1.5.7
 *
 * @var int    $field_id      Field ID.
 * @var string $value_display Value display.
 * @var string $value_hint    Value hint.
 * @var float  $default_value Default value.
 * @var float  $min           Minimum value.
 * @var float  $max           Maximum value.
 * @var float  $step          Step value.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<input type="range" readonly
	class="wpforms-number-slider"
	id="wpforms-number-slider-<?php echo (int) $field_id; ?>"
	value="<?php echo (float) $default_value; ?>"
	min="<?php echo (float) $min; ?>"
	max="<?php echo (float) $max; ?>"
	step="<?php echo (float) $step; ?>">

<div
	id="wpforms-number-slider-hint-<?php echo (int) $field_id; ?>"
	data-hint="<?php echo esc_attr( wp_kses_post( wpforms_html_entity_decode_deep( $value_display ) ) ); ?>"
	class="wpforms-number-slider-hint">
	<?php echo wp_kses_post( wpforms_html_entity_decode_deep( $value_hint ) ); ?>
</div>
