<?php

namespace WPMailSMTP\Providers\Zoho;

use WPMailSMTP\Providers\OptionsAbstract;

/**
 * Class Options.
 *
 * @since 2.3.0
 */
class Options extends OptionsAbstract {

	/**
	 * Zoho Options constructor.
	 *
	 * @since 2.3.0
	 */
	public function __construct() {

		parent::__construct(
			array(
				'logo_url' => wp_mail_smtp()->assets_url . '/images/providers/zoho.svg',
				'slug'     => 'zoho',
				'title'    => esc_html__( 'Zoho Mail', 'wp-mail-smtp' ),
				'disabled' => true,
			)
		);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.3.0
	 */
	public function display_options() {

		?>
		<div class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-content wp-mail-smtp-clear section-heading">
			<p>
				<?php esc_html_e( 'We\'re sorry, the Zoho Mail mailer is not available on your plan. Please upgrade to the PRO plan to unlock all these awesome features.', 'wp-mail-smtp' ); ?>
			</p>
		</div>
		<?php
	}
}
