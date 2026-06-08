<?php

namespace WPForms\Admin\Education\Builder;

use WPForms\Admin\Education\Helpers;

/**
 * PDF educational popup.
 *
 * @since 1.9.7.3
 */
class PDF {

	/**
	 * Addon slug.
	 *
	 * @since 1.9.7.3
	 *
	 * @var string
	 */
	private $slug = 'wpforms-pdf';

	/**
	 * Addon data.
	 *
	 * @since 1.9.7.3
	 *
	 * @var array
	 */
	private $addon_data;

	/**
	 * Initialize.
	 *
	 * @since 1.9.7.3
	 */
	public function init(): void {

		$this->addon_data = $this->get_addon_data();

		if ( ! $this->should_show_popup() ) {
			return;
		}

		$this->hooks();
	}

	/**
	 * Should show popup.
	 *
	 * @since 1.9.7.3
	 *
	 * @return bool
	 */
	private function should_show_popup(): bool {

		if ( ! wpforms_is_admin_page( 'builder' ) && ! wpforms_is_admin_ajax() ) {
			return false;
		}

		if ( ! current_user_can( wpforms_get_capability_manage_options() ) ) {
			return false;
		}

		$challenge = wpforms()->obj( 'challenge' );

		if ( ! $challenge || $challenge->challenge_active() ) {
			return false;
		}

		return $this->is_popup_visible();
	}

	/**
	 * Is popup visible.
	 *
	 * @since 1.9.7.3
	 *
	 * @return bool
	 */
	private function is_popup_visible(): bool {

		$action = $this->addon_data['action'] ?? 'install';

		if (
			empty( $this->addon_data ) ||
			( $action === 'install' && empty( $this->addon_data['url'] ) ) // The install action requires a valid URL.
		) {
			return false;
		}

		$meta = get_user_meta( get_current_user_id(), 'wpforms_dismissed', true );

		return empty( $meta['edu-builder-pdf'] );
	}

	/**
	 * Hooks.
	 *
	 * @since 1.9.7.3
	 */
	private function hooks(): void {

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_filter( 'wpforms_builder_output_before_toolbar', [ $this, 'popup_html' ] );
	}

	/**
	 * Enqueue scripts.
	 *
	 * @since 1.9.7.3
	 */
	public function enqueue_scripts(): void {

		$min = wpforms_get_min_suffix();

		wp_enqueue_script(
			'wpforms-pdf-popup',
			WPFORMS_PLUGIN_URL . "assets/js/admin/education/pdf$min.js",
			[],
			WPFORMS_VERSION,
			true
		);
	}

	/**
	 * Popup HTML.
	 *
	 * @since 1.9.7.3
	 *
	 * @param string|mixed $html HTML.
	 *
	 * @return string
	 * @noinspection HtmlUnknownTarget
	 */
	public function popup_html( $html ): string {

		$html = (string) $html;

		$popup = sprintf(
			'<div id="wpforms-pdf-popup" class="wpforms-pdf-popup wpforms-hidden wpforms-dismiss-container" role="dialog" aria-modal="true" aria-labelledby="wpforms-pdf-popup-title" aria-describedby="wpforms-pdf-popup-description">
				<div class="wpforms-pdf-popup-content">
					<div class="icon">
						<img src="%1$s" alt="PDF Icon">
					</div>
					<div class="close-popup wpforms-dismiss-button dashicons-no-alt" data-section="builder-pdf"></div>
					<div class="badge">%2$s</div>
					<h2 id="wpforms-pdf-popup-title">%3$s</h2>
					<p id="wpforms-pdf-popup-description">%4$s</p>
					%5$s
				</div>
			</div>',
			esc_url( WPFORMS_PLUGIN_URL . 'assets/images/pdf-education/pdf.svg' ),
			__( 'NEW FEATURE', 'wpforms-lite' ),
			__( 'PDF Addon', 'wpforms-lite' ),
			__( 'Easily turn form entry data into beautifully designed PDFs and attach them to notifications.', 'wpforms-lite' ),
			$this->get_button_html()
		);

		return $popup . $html;
	}

	/**
	 * Get button HTML.
	 *
	 * @since 1.9.7.3
	 *
	 * @return string
	 * @noinspection HtmlUnknownAttribute
	 */
	private function get_button_html(): string {

		$addon  = $this->addon_data;
		$action = $addon['action'] ?? 'switch';

		[ $button_label, $button_utm, $button_class, $button_attr ] = $this->get_button_data( $action, $addon );

		return sprintf(
			'<button class="wpforms-btn wpforms-btn-sm wpforms-btn-orange %1$s" data-action="%2$s" %4$s data-license="%5$s" data-utm-content="%6$s">%3$s</button>',
			esc_attr( $button_class ),
			esc_attr( $action ),
			esc_html( $button_label ),
			$button_attr,
			esc_attr( $addon['license_level'] ?? 'pro' ),
			esc_attr( $button_utm )
		);
	}

	/**
	 * Get addon data.
	 *
	 * @since 1.9.7.3
	 *
	 * @return array
	 */
	private function get_addon_data(): array {

		/**
		 * Filter the slug for the PDF educational popup.
		 *
		 * @since 1.9.7.3
		 *
		 * @param string $slug The slug for the PDF educational popup.
		 */
		$slug   = apply_filters( 'wpforms_admin_education_builder_pdf_get_addon_data_slug', $this->slug );
		$addons = Helpers::get_edu_addons();

		return $addons[ $slug ] ?? [];
	}

	/**
	 * Get button data.
	 *
	 * @since 1.9.7.3
	 *
	 * @param string $action Action type (switch, upgrade, etc.).
	 * @param array  $addon  Addon data.
	 *
	 * @return array
	 */
	protected function get_button_data( string $action, array $addon ): array {

		$button_label = $action === 'upgrade'
			? esc_html__( 'Upgrade to Pro', 'wpforms-lite' )
			: esc_html__( 'Try it Out', 'wpforms-lite' );
		$button_utm   = 'PDF Addon Pop-up';
		$button_class = 'education-action-button';
		$button_attr  = '';

		if ( $action === 'switch' ) {
			$button_class = 'education-modal education-switch-button';
			$button_attr  = 'data-target="wpforms-pdf"';
		} elseif ( $action !== 'upgrade' ) {
			$button_class = 'education-modal';
			$button_attr  = sprintf(
				'data-nonce="%1$s" data-path="%2$s" data-url="%3$s" data-message="" data-name="%4$s"',
				esc_attr( wp_create_nonce( 'wpforms-admin' ) ),
				$addon['path'] ?? '',
				$addon['url'] ?? '',
				esc_html__( 'WPForms PDF Addon', 'wpforms-lite' )
			);
		}

		return [ $button_label, $button_utm, $button_class, $button_attr ];
	}
}
