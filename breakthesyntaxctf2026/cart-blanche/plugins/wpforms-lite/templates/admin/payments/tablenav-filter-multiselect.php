<?php
/**
 * Display a multiselect field for filtering the payments overview table.
 *
 * @since 1.8.4
 *
 * @var string $name          Name of the select field.
 * @var array  $options       Select field options.
 * @var array  $selected      Array of selected options.
 * @var array  $data_settings Data settings for the multiselect JS instance.
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

// Leave early if no filters are provided.
if ( empty( $options ) ) {
	return '';
}

?>
<select
	multiple
	class="wpforms-multiselect wpforms-hide"
	name="<?php echo esc_attr( $name ); ?>"
	placeholder="<?php echo esc_attr( $data_settings['i18n']['all'] ); ?>"
	data-settings="<?php echo esc_attr( wp_json_encode( $data_settings ) ); ?>"
>
	<?php foreach ( $options as $key => $label ) : ?>
	<option value="<?php echo esc_attr( $key ); ?>" <?php selected( true, in_array( $key, $selected, true ) ); ?>>
		<?php echo esc_html( $label ); ?>
	</option>
	<?php endforeach; ?>
</select>
