<?php
/**
 * File upload on the entry page
 *
 * @var int    $max_file_number Max file number.
 * @var string $preview_hint    Hint message on the preview.
 * @var string $modern_classes  Modern classes.
 * @var string $classic_classes Classic classes.
 * @var bool   $is_camera       Has camera enabled.
 * @var string $classic_camera  Classic camera text.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( $is_camera ) {
	/* translators: %1$s: Choose File to Upload opening tag, %2$s: Closing tag, %3$s: Capture With Camera opening tag. */
	$description = esc_html( _n( 'Drag & Drop File, %1$sChoose File to Upload%2$s, or %3$sCapture With Camera%2$s', 'Drag & Drop Files, %1$sChoose Files to Upload%2$s, or %3$sCapture With Camera%2$s', $max_file_number, 'wpforms-lite' ) );
	$description = sprintf( $description, '<span class="wpforms-file-upload-choose-file">', '</span>', '<span class="wpforms-file-upload-capture-camera">' );
} else {
	/* translators: %1$s: Choose File to Upload opening tag, %2$s: Choose File to Upload closing tag. */
	$description = esc_html( _n( 'Drag & Drop File or %1$sChoose File to Upload%2$s', 'Drag & Drop Files or %1$sChoose Files to Upload%2$s', $max_file_number, 'wpforms-lite' ) );
	$description = sprintf( $description, '<span class="wpforms-file-upload-choose-file">', '</span>' );
}

$hidden_class = $max_file_number < 2 ? 'wpforms-hide' : '';

?>
<div class="<?php echo wpforms_sanitize_classes( $modern_classes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
	<svg viewBox="0 0 640 640" focusable="false" data-icon="inbox" width="50px" height="50px" fill="#B1B1B1" aria-hidden="true">
		<path d="M352 173.3L352 384C352 401.7 337.7 416 320 416C302.3 416 288 401.7 288 384L288 173.3L246.6 214.7C234.1 227.2 213.8 227.2 201.3 214.7C188.8 202.2 188.8 181.9 201.3 169.4L297.3 73.4C309.8 60.9 330.1 60.9 342.6 73.4L438.6 169.4C451.1 181.9 451.1 202.2 438.6 214.7C426.1 227.2 405.8 227.2 393.3 214.7L352 173.3zM320 464C364.2 464 400 428.2 400 384L480 384C515.3 384 544 412.7 544 448L544 480C544 515.3 515.3 544 480 544L160 544C124.7 544 96 515.3 96 480L96 448C96 412.7 124.7 384 160 384L240 384C240 428.2 275.8 464 320 464zM464 488C477.3 488 488 477.3 488 464C488 450.7 477.3 440 464 440C450.7 440 440 450.7 440 464C440 477.3 450.7 488 464 488z"/>
	</svg>
	<span class="modern-title">
		<?php echo wp_kses_post( $description ); ?>
	</span>
	<span class="modern-hint <?php echo sanitize_html_class( $hidden_class ); ?>">
		<?php echo esc_html( $preview_hint ); ?>
	</span>
</div>
<div class="<?php echo wpforms_sanitize_classes( $classic_classes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
	<input type="file" class="primary-input" readonly>

	<span class="wpforms-file-upload-capture-camera wpforms-file-upload-capture-camera-classic <?php echo $is_camera ? '' : 'wpforms-hidden'; ?>">
		<?php echo esc_html( $classic_camera ); ?>
	</span>
</div>
