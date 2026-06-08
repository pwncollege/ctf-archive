<?php
/**
 * Form Builder themes notices template.
 *
 * @since 1.9.7
 *
 * @var bool $is_modern      Modern render engine active status.
 * @var bool $is_full_styles Full styling using status.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$plugins_page_url = add_query_arg(
	[
		'page' => 'wpforms-settings',
		'view' => 'general',
	],
	admin_url( 'admin.php' )
);

?>

<div class="wpforms-alert wpforms-alert-warning wpforms-alert-warning-wide wpforms-builder-themes-style-notice wpforms-hidden">
	<?php

	$notice_text = '';

	if ( ! $is_modern ) {
		$notice_text = __( 'Upgrade your forms to use our modern markup and unlock extensive style controls.', 'wpforms-lite' );
	} elseif ( ! $is_full_styles ) {
		$notice_text = __( 'Update your forms to use base and form theme styling and unlock extensive style controls.', 'wpforms-lite' );
	}

	// phpcs:ignore Generic.Commenting.DocComment.MissingShort
	/** @noinspection HtmlUnknownTarget */
	printf(
		wp_kses( /* translators: %s - WPForms documentation link. */
			__( '<h4>Want to customize your form styles without editing CSS?</h4> <p>%1$s</p> <a href="%2$s" target="_blank" rel="noopener noreferrer">Go to Settings</a>', 'wpforms-lite' ),
			[
				'h4' => [],
				'p'  => [],
				'a'  => [
					'href'   => [],
					'rel'    => [],
					'target' => [],
				],
			]
		),
		esc_html( $notice_text ),
		esc_url( $plugins_page_url )
	);

	?>
</div>
<div class="wpforms-alert wpforms-alert-warning wpforms-alert-warning-wide wpforms-builder-themes-lf-notice wpforms-hidden">
	<?php

	echo wp_kses( /* translators: %s - WPForms documentation link. */
		__( '<h4>Form styles are disabled because Lead Form Mode is turned on.</h4> <p>To change the styling for this form, edit the options in the <a href="#">Lead Forms settings.</a></p>', 'wpforms-lite' ),
		[
			'h4' => [],
			'p'  => [],
			'a'  => [
				'href' => [],
			],
		]
	);

	?>
</div>
<div class="wpforms-alert wpforms-alert-warning wpforms-alert-warning-wide wpforms-builder-themes-cf-notice wpforms-hidden">
	<?php

	echo wp_kses( /* translators: %s - WPForms documentation link. */
		__( '<h4>Form styles are disabled because Conversational Forms addon is turned on.</h4> <p>To change the styling for this form, edit the options in the <a href="#">Conversational Forms settings.</a></p>', 'wpforms-lite' ),
		[
			'h4' => [],
			'p'  => [],
			'a'  => [
				'href' => [],
			],
		]
	);

	?>
</div>

<div id="wpforms-page-forms-fbst-notice" class="wpforms-alert wpforms-alert-warning wpforms-alert-warning-wide wpforms-hidden">
	<?php

	$notice_text = __( 'Your version of Form Pages is out of date. For the best experience and access to all features, please update to the latest version.', 'wpforms-lite' );

	// phpcs:ignore Generic.Commenting.DocComment.MissingShort
	/** @noinspection HtmlUnknownTarget */
	printf(
		wp_kses( /* translators: %s - Plugins page url. */
			__( '<h4>Update Available</h4> <p>%1$s</p> <a href="%2$s" target="_blank" rel="noopener noreferrer">Update Now</a>', 'wpforms-lite' ),
			[
				'h4' => [],
				'p'  => [],
				'a'  => [
					'href'   => [],
					'rel'    => [],
					'target' => [],
				],
			]
		),
		esc_html( $notice_text ),
		esc_url( admin_url( 'plugins.php' ) )
	);

	?>
</div>
