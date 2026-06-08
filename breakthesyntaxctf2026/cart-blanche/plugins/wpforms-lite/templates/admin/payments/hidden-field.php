<?php
/**
 * Display a hidden field for the purpose of form submission.
 *
 * @since 1.8.4
 *
 * @var string $name  Name of the hidden field.
 * @var array  $value Value of the hidden field.
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

// Leave early if no field name or value is provided.
if ( empty( $name ) || empty( $value ) ) {
	return;
}

?>

<input
	type="hidden"
	name="<?php echo esc_attr( $name ); ?>"
	value="<?php echo esc_attr( $value ); ?>"
/>

<?php
/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */
