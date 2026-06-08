<?php

namespace WPMailSMTP\Providers\AmazonSES;

use WPMailSMTP\Providers\OptionsAbstract;

/**
 * Class Options.
 *
 * @since 1.7.0
 */
class Options extends OptionsAbstract {

	/**
	 * AmazonSES Options constructor.
	 *
	 * @since 1.7.0
	 */
	public function __construct() {

		parent::__construct(
			array(
				'logo_url' => wp_mail_smtp()->assets_url . '/images/providers/aws.svg',
				'slug'     => 'amazonses',
				'title'    => esc_html__( 'Amazon SES', 'wp-mail-smtp' ),
				'disabled' => true,
			)
		);
	}

	/**
	 * @inheritdoc
	 */
	public function display_options() {

		?>
		<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-content wp-mail-smtp-clear section-heading">
			<p>
				<?php esc_html_e( 'We\'re sorry, the Amazon SES mailer is not available on your plan. Please upgrade to the PRO plan to unlock all these awesome features.', 'wp-mail-smtp' ); ?>
			</p>
		</div>
		<?php
	}
}
