<?php

namespace WPForms\Integrations\ConstantContact\V3\Settings;

use WPForms\Providers\Provider\Settings\PageIntegrations as PageIntegrationsAbstract;
use WPForms\Providers\Provider\Core;

/**
 * Class PageIntegrations.
 *
 * @since 1.9.3
 */
class PageIntegrations extends PageIntegrationsAbstract {

	/**
	 * Constructor.
	 *
	 * @since 1.9.3
	 *
	 * @param Core $core Provider core class.
	 */
	public function __construct( Core $core ) {

		parent::__construct( $core );

		$this->hooks();
	}

	/**
	 * Hooks.
	 *
	 * @since 1.9.3
	 */
	private function hooks() {

		add_action( 'wpforms_providers_provider_settings_page_integrations_display_connected_account_item_before', [ $this, 'display_re_auth' ], 10, 2 );
	}

	/**
	 * Display reauthorization notice.
	 *
	 * @since 1.9.3
	 *
	 * @param string $account_id Account ID.
	 * @param array  $account    Account data.
	 *
	 * @noinspection PhpMissingParamTypeInspection
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function display_re_auth( $account_id, $account ) {

		if ( empty( $account['accounts'] ) || empty( $account['email'] ) ) {
			return;
		}

		?>
		<div class="wpforms-alert wpforms-alert-danger wpforms-alert-dismissible">
			<div class="wpforms-alert-message">
				<p>
				<?php
					esc_html_e(
						'Your Constant Contact account connection has expired. Please reconnect your account.',
						'wpforms-lite'
					);
				?>
				</p>
			</div>

			<div class="wpforms-alert-buttons wpforms-alert-buttons-constant-contact-v3">
					<a class="wpforms-btn wpforms-btn-md wpforms-btn-light-grey wpforms-constant-contact-v3-auth"
						href="#"
						data-login-hint="<?php echo esc_attr( $account['email'] ); ?>">
						<i class="fa fa-repeat"></i> <?php esc_html_e( 'Reconnect Account', 'wpforms-lite' ); ?>
					</a>
			</div>
		</div>
		<?php
	}

	/**
	 * Display Constants Contact V3 integrations tab.
	 *
	 * @since 1.9.3
	 *
	 * @noinspection HtmlUnknownTarget
	 */
	protected function display_add_new() {
		?>
		<p>
			<a class="wpforms-btn wpforms-btn-md wpforms-btn-light-grey wpforms-constant-contact-v3-auth" href="#">
				<i class="fa fa-plus"></i> <?php esc_html_e( 'Add New Account', 'wpforms-lite' ); ?>
			</a>
		</p>
		<p>
			<?php
			printf(
				'<a href="%1$s" target="_blank" rel="noopener noreferrer" class="secondary-text">%2$s</a>',
				// @todo: confirm the link.
				// @see: https://github.com/awesomemotive/wpforms-plugin/issues/12504
				esc_url( wpforms_utm_link( 'https://wpforms.com/docs/how-to-connect-constant-contact-with-wpforms/', 'Settings - Integration', 'ConstantContact V3 Documentation' ) ),
				esc_html__( 'Click here for documentation on connecting WPForms with Constant Contact.', 'wpforms-lite' )
			);
			?>
		</p>
		<?php
	}
}
