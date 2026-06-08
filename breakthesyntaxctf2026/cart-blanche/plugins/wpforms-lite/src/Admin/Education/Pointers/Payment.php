<?php

namespace WPForms\Admin\Education\Pointers;

use WPForms\Integrations\Stripe;
use WPForms\Admin\Payments\Views\Overview\Page as PaymentsPage;
use WPForms\Migrations\Base as MigrationsBase;

/**
 * Education class for handling Payments education pointer functionality.
 *
 * This class extends the abstract Pointers class and provides functionality
 * specific to the Payments feature in WPForms.
 *
 * @since 1.8.8
 */
class Payment extends Pointer {

	/**
	 * Unique ID for the pointer.
	 *
	 * @since 1.8.8
	 *
	 * @var string
	 */
	protected $pointer_id = 'admin_menu_payments';

	/**
	 * Selector for the pointer.
	 *
	 * @since 1.8.8
	 *
	 * @var string
	 */
	protected $selector = '[href$="-payments"]';

	/**
	 * Make sure that the pointer is visible across other dashboard pages.
	 *
	 * @since 1.8.8
	 *
	 * @var bool
	 */
	protected $top_level_visible = true;

	/**
	 * Determine if the Payments feature pointer is allowed to load.
	 *
	 * Checks various conditions to determine if the Payments feature pointer
	 * should be allowed to load for the current user.
	 *
	 * @since 1.8.8
	 *
	 * @return bool
	 */
	protected function allow_load(): bool {

		// Bail early if the user doesn't have a Lite, Basic, or Plus license.
		if ( ! in_array( $this->get_license_type(), [ 'lite', 'basic', 'plus' ], true ) ) {
			return false;
		}

		// Bail early if it has been less than 90 days since activation or the installation wasn't upgraded.
		if (
			! get_option( MigrationsBase::PREVIOUS_CORE_VERSION_OPTION_NAME ) ||
			wpforms_get_activated_timestamp() > ( time() - 90 * DAY_IN_SECONDS )
		) {
			return false;
		}

		// Bail early if a Stripe account is connected.
		if ( Stripe\Helpers::has_stripe_keys() ) {
			return false;
		}

		// Bail early if the user doesn't have the capability to manage options.
		if ( ! wpforms_current_user_can() ) {
			return false;
		}

		// Bail early if there are no published forms.
		$forms_obj = wpforms()->obj( 'form' );

		return $forms_obj && $forms_obj->forms_exist();
	}

	/**
	 * Enqueue assets for the pointer.
	 *
	 * @since 1.8.8
	 */
	public function enqueue_assets() {

		// Enqueue the pointer static assets.
		parent::enqueue_assets();

		$min = wpforms_get_min_suffix();

		wp_enqueue_script(
			'wpforms-education-pointers-payment',
			WPFORMS_PLUGIN_URL . "assets/js/admin/education/pointers/payment{$min}.js",
			[ 'wp-pointer' ],
			WPFORMS_VERSION,
			true
		);

		$admin_l10n = [
			'pointer' => sanitize_key( $this->pointer_id ),
			'nonce'   => sanitize_text_field( $this->get_nonce_token() ),
		];

		wp_localize_script(
			'wpforms-education-pointers-payment',
			'wpforms_education_pointers_payment',
			$admin_l10n
		);
	}

	/**
	 * Set arguments for the Payments feature pointer.
	 *
	 * @since 1.8.8
	 *
	 * @noinspection HtmlUnknownTarget
	 */
	protected function set_args() {

		$this->args['title']   = __( 'Payment and Donation Forms are here!', 'wpforms-lite' );
		$this->args['message'] = sprintf( /* translators: %1$s - Payments page URL. */
			__(
				'Now available for you: create forms that accept credit cards, Apple Pay, and Google Pay payments. Visit our new <a href="%1$s" id="wpforms-education-pointers-payments">Payments area</a> to get started.',
				'wpforms-lite'
			),
			esc_url( PaymentsPage::get_url() )
		);
	}

	/**
	 * Retrieve the current installation license type in the lowercase.
	 * If no license type is found, defaults to 'lite'.
	 *
	 * @since 1.8.8
	 *
	 * @return string
	 */
	private function get_license_type(): string {

		$type = wpforms_get_license_type();

		// Set the default to 'lite' if no license type is detected.
		if ( empty( $type ) ) {
			$type = 'lite';
		}

		return $type;
	}
}
