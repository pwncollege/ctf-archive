<?php

namespace WPForms\Integrations\Square\Admin\Builder;

use WPForms\Integrations\Square\Helpers;

/**
 * Square Form Builder related functionality.
 *
 * @since 1.9.5
 */
class Settings {

	use Traits\Content;

	/**
	 * Slug of the integration.
	 *
	 * @since 1.9.5
	 *
	 * @var string
	 */
	private $slug = 'square';

	/**
	 * Name of the integration.
	 *
	 * @since 1.9.5
	 *
	 * @var string
	 */
	private $name = 'Square';

	/**
	 * Icon URL.
	 *
	 * @since 1.9.5
	 *
	 * @var string
	 */
	private $icon = WPFORMS_PLUGIN_URL . 'assets/images/addon-icon-square.png';

	/**
	 * Form data.
	 *
	 * @since 1.9.5
	 *
	 * @var array $form_data
	 */
	private $form_data = [];

	/**
	 * Initialize.
	 *
	 * @since 1.9.5
	 */
	public function init() {

		$this->form_data = $this->get_form_data();

		$this->hooks();
	}

	/**
	 * Builder hooks.
	 *
	 * @since 1.9.5
	 */
	private function hooks() {

		add_filter( 'wpforms_payments_available', [ $this, 'register_payment' ] );
		add_action( 'wpforms_payments_panel_content', [ $this, 'builder_output' ], 1 );
		add_action( 'wpforms_payments_panel_sidebar', [ $this, 'builder_sidebar' ], 1 );
		add_filter( 'wpforms_admin_education_addons_item_base_display_single_addon_hide', [ $this, 'should_hide_educational_menu_item' ], 10, 2 );
	}

	/**
	 * Register the payment gateway.
	 *
	 * @since 1.9.5
	 *
	 * @param array $payments_available List of available payment gateways.
	 *
	 * @return array
	 */
	public function register_payment( $payments_available ): array {

		$payments_available = (array) $payments_available;

		$payments_available[ $this->slug ] = $this->name;

		return $payments_available;
	}

	/**
	 * Output the gateway menu item.
	 *
	 * @since 1.9.5
	 */
	public function builder_sidebar() {

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wpforms_render(
			'builder/payment/sidebar',
			[
				'configured'  => $this->is_payments_enabled() ? 'configured' : '',
				'slug'        => $this->slug,
				'icon'        => $this->icon,
				'name'        => $this->name,
				'recommended' => true,
			],
			true
		);
	}

	/**
	 * Output the gateway settings.
	 *
	 * @since 1.9.5
	 */
	public function builder_output() {
		?>
		<div class="wpforms-panel-content-section wpforms-panel-content-section-<?php echo esc_attr( $this->slug ); ?>"
			id="<?php echo esc_attr( $this->slug ); ?>-provider" data-provider="<?php echo esc_attr( $this->slug ); ?>" data-provider-name="<?php echo esc_attr( $this->name ); ?>">

			<div class="wpforms-panel-content-section-title">
				<?php echo esc_html( $this->name ); ?>
			</div>

			<div class="wpforms-payment-settings wpforms-clear">
				<?php $this->builder_content(); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Check if it is going to be displayed Square educational menu item and hide it.
	 *
	 * @since 1.9.5
	 *
	 * @param bool  $hide  Whether to hide the menu item.
	 * @param array $addon Addon data.
	 *
	 * @return bool
	 */
	public function should_hide_educational_menu_item( $hide, array $addon ): bool {

		return isset( $addon['clear_slug'] ) && $this->slug === $addon['clear_slug'] ? true : (bool) $hide;
	}


	/**
	 * Get form data.
	 *
	 * @since 1.9.5
	 *
	 * @return array
	 */
	private function get_form_data(): array {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$form_id = isset( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : 0;

		if ( ! $form_id ) {
			return [];
		}

		$form_data = wpforms()->obj( 'form' )->get(
			$form_id,
			[
				'content_only' => true,
			]
		);

		return is_array( $form_data ) ? $form_data : [];
	}

	/**
	 * Check if payments enabled.
	 *
	 * @since 1.9.5
	 *
	 * @return bool
	 */
	private function is_payments_enabled(): bool {

		return ! empty( $this->form_data['payments'][ $this->slug ]['enable'] ) || ! empty( $this->form_data['payments'][ $this->slug ]['enable_one_time'] );
	}

	/**
	 * Get single payments conditional logic for the Square settings panel.
	 *
	 * @since 1.9.5
	 *
	 * @return string
	 */
	private function single_payments_conditional_logic_section(): string {

		return $this->get_conditional_logic_toggle();
	}

	/**
	 * Get education toggle for the conditional logic.
	 *
	 * @since 1.9.5
	 *
	 * @param bool $is_recurring Is recurring section.
	 *
	 * @return string
	 */
	private function get_conditional_logic_toggle( bool $is_recurring = false ): string {

		return wpforms_panel_field(
			'toggle',
			$this->slug,
			'conditional_logic',
			$this->form_data,
			esc_html__( 'Enable Conditional Logic', 'wpforms-lite' ),
			[
				'value'       => 0,
				'input_class' => 'education-modal',
				'parent'      => 'payments',
				'subsection'  => $is_recurring ? 'recurring' : '',
				'pro_badge'   => ! Helpers::is_allowed_license_type(),
				'data'        => $this->get_conditional_logic_section_data(),
				'attrs'       => [
					'disabled' => 'disabled',
				],
			],
			false
		);
	}

	/**
	 * Get conditional logic section data.
	 *
	 * @since 1.9.5
	 *
	 * @return array
	 */
	private function get_conditional_logic_section_data(): array {

		$addon = wpforms()->obj( 'addons' )->get_addon( $this->slug );

		if (
			empty( $addon ) ||
			empty( $addon['action'] ) ||
			empty( $addon['status'] ) || (
				$addon['status'] === 'active' &&
				$addon['action'] !== 'upgrade'
			)
		) {
			return [];
		}

		if ( $addon['plugin_allow'] && $addon['action'] === 'install' ) {
			return [
				'action'  => 'install',
				'message' => esc_html__( 'The Square Pro addon is required to enable conditional logic for payments. Would you like to install and activate it?', 'wpforms-lite' ),
				'url'     => $addon['url'],
				'nonce'   => wp_create_nonce( 'wpforms-admin' ),
				'license' => 'pro',
			];
		}

		if ( $addon['plugin_allow'] && $addon['action'] === 'activate' ) {
			return [
				'action'  => 'activate',
				'message' => esc_html__( 'The Square Pro addon is required to enable conditional logic for payments. Would you like to activate it?', 'wpforms-lite' ),
				'path'    => $addon['path'],
				'nonce'   => wp_create_nonce( 'wpforms-admin' ),
			];
		}

		return [
			'action'      => 'upgrade',
			'name'        => esc_html__( 'Smart Conditional Logic', 'wpforms-lite' ),
			'utm-content' => 'Builder Square Conditional Logic',
			'licence'     => 'pro',
		];
	}

	/**
	 * Get recurring payments conditional logic for the Square settings panel.
	 *
	 * @since 1.9.5
	 *
	 * @param string $plan_id Plan ID.
	 *
	 * @return string
	 */
	private function recurring_payments_conditional_logic_section( string $plan_id ): string { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found

		return $this->get_conditional_logic_toggle( true );
	}
}
