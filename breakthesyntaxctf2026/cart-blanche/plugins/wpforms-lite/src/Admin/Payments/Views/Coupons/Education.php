<?php

namespace WPForms\Admin\Payments\Views\Coupons;

use WPForms\Admin\Payments\Views\Overview\Helpers;
use WPForms\Admin\Payments\Views\PaymentsViewsInterface;

/**
 * Payments Coupons Education class.
 *
 * @since 1.8.2.2
 */
class Education implements PaymentsViewsInterface {

	/**
	 * Coupons addon data.
	 *
	 * @since 1.8.2.2
	 *
	 * @var array
	 */
	private $addon;

	/**
	 * Initialize class.
	 *
	 * @since 1.8.2.2
	 */
	public function init() {

		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.8.2.2
	 */
	private function hooks() {

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Get the page label.
	 *
	 * @since 1.8.2.2
	 *
	 * @return string
	 */
	public function get_tab_label() {

		return __( 'Coupons', 'wpforms-lite' );
	}

	/**
	 * Enqueue scripts.
	 *
	 * @since 1.8.2.2
	 */
	public function enqueue_scripts() {

		// Lity - lightbox for images.
		wp_enqueue_style(
			'wpforms-lity',
			WPFORMS_PLUGIN_URL . 'assets/lib/lity/lity.min.css',
			null,
			'3.0.0'
		);

		wp_enqueue_script(
			'wpforms-lity',
			WPFORMS_PLUGIN_URL . 'assets/lib/lity/lity.min.js',
			[ 'jquery' ],
			'3.0.0',
			true
		);
	}

	/**
	 * Check if the current user has the capability to view the page.
	 *
	 * @since 1.8.2.2
	 *
	 * @return bool
	 */
	public function current_user_can() {

		if ( ! wpforms_current_user_can() ) {
			return false;
		}

		$this->addon = wpforms()->obj( 'addons' )->get_addon( 'coupons' );

		if (
			empty( $this->addon ) ||
			empty( $this->addon['status'] ) ||
			empty( $this->addon['action'] )
		) {
			return false;
		}

		return true;
	}

	/**
	 * Page heading content.
	 *
	 * @since 1.8.2.2
	 */
	public function heading() {

		Helpers::get_default_heading();
	}

	/**
	 * Page content.
	 *
	 * @since 1.8.2.2
	 */
	public function display() {

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wpforms_render( 'education/admin/page', $this->template_data(), true );
	}

	/**
	 * Get the template data.
	 *
	 * @since 1.8.6
	 *
	 * @return array
	 */
	private function template_data(): array {

		$images_url   = WPFORMS_PLUGIN_URL . 'assets/images/coupons-education/';
		$utm_medium   = 'Payments - Coupons';
		$utm_content  = 'Coupons Addon';
		$upgrade_link = $this->addon['action'] === 'upgrade'
			? sprintf( /* translators: %1$s - WPForms.com Upgrade page URL. */
				' <strong><a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a></strong>',
				esc_url( wpforms_admin_upgrade_link( $utm_medium, $utm_content ) ),
				esc_html__( 'Upgrade to WPForms Pro', 'wpforms-lite' )
			)
			: '';
		$params       = [
			'features'             => [
				__( 'Custom Coupon Codes', 'wpforms-lite' ),
				__( 'Percentage or Fixed Discounts', 'wpforms-lite' ),
				__( 'Start and End Dates', 'wpforms-lite' ),
				__( 'Maximum Usage Limit', 'wpforms-lite' ),
				__( 'Once Per Email Address Limit', 'wpforms-lite' ),
				__( 'Usage Statistics', 'wpforms-lite' ),
			],
			'images'               => [
				[
					'url'   => $images_url . 'coupons-addon-thumbnail-01.png',
					'url2x' => $images_url . 'coupons-addon-screenshot-01.png',
					'title' => __( 'Coupons Overview', 'wpforms-lite' ),
				],
				[
					'url'   => $images_url . 'coupons-addon-thumbnail-02.png',
					'url2x' => $images_url . 'coupons-addon-screenshot-02.png',
					'title' => __( 'Coupon Settings', 'wpforms-lite' ),
				],
			],
			'utm_medium'           => $utm_medium,
			'utm_content'          => $utm_content,
			'upgrade_link'         => $upgrade_link,
			'heading_description'  => '<p>' . sprintf( /* translators: %1$s - WPForms.com Upgrade page URL. */
				esc_html__( 'With the Coupons addon, you can offer customers discounts using custom coupon codes. Create your own percentage or fixed rate discount, then add the Coupon field to any payment form. When a customer enters your unique code, they’ll receive the specified discount. You can also add limits to restrict when coupons are available and how often they can be used. The Coupons addon requires a license level of Pro or higher.%s', 'wpforms-lite' ),
				wp_kses(
					$upgrade_link,
					[
						'a'      => [
							'href'   => [],
							'rel'    => [],
							'target' => [],
						],
						'strong' => [],
					]
				)
			) . '</p>',
			'features_description' => __( 'Easy to Use, Yet Powerful', 'wpforms-lite' ),
		];

		return array_merge( $params, $this->addon );
	}
}
