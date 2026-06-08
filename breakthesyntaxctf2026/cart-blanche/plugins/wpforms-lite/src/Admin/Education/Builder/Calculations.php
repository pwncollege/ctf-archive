<?php

namespace WPForms\Admin\Education\Builder;

use WPForms\Admin\Education\AddonsItemBase;
use WPForms\Admin\Education\Helpers;
use WPForms\Integrations\AI\Helpers as AIHelpers;

/**
 * Builder/Calculations Education feature for Lite and Pro.
 *
 * @since 1.8.4.1
 */
class Calculations extends AddonsItemBase {

	/**
	 * Support calculations in these field types.
	 *
	 * @since 1.8.4.1
	 *
	 * @var array
	 */
	public const ALLOWED_FIELD_TYPES = [ 'text', 'textarea', 'number', 'hidden', 'payment-single' ];

	/**
	 * Field types that should display educational notice in the basic field options tab.
	 *
	 * @since 1.8.4.1
	 *
	 * @var array
	 */
	public const BASIC_OPTIONS_NOTICE_FIELD_TYPES = [ 'number', 'payment-single' ];

	/**
	 * Indicate if the current Education feature is allowed to load.
	 *
	 * @since 1.8.4.1
	 *
	 * @return bool
	 *
	 * @noinspection PhpMissingReturnTypeInspection
	 * @noinspection ReturnTypeCanBeDeclaredInspection
	 */
	public function allow_load() {

		return wpforms_is_admin_page( 'builder' ) || wpforms_is_admin_ajax();
	}

	/**
	 * Hooks.
	 *
	 * @since 1.8.4.1
	 *
	 * @noinspection ReturnTypeCanBeDeclaredInspection
	 */
	public function hooks() {

		add_action( 'wpforms_field_options_bottom_basic-options', [ $this, 'basic_options' ], 20, 2 );
		add_action( 'wpforms_field_options_bottom_advanced-options', [ $this, 'advanced_options' ], 20, 2 );
	}

	/**
	 * Display notice on basic options.
	 *
	 * @since        1.8.4.1
	 *
	 * @param array  $field    Field data.
	 * @param object $instance Builder instance.
	 *
	 * @noinspection HtmlUnknownTarget
	 * @noinspection ReturnTypeCanBeDeclaredInspection
	 * @noinspection PhpMissingParamTypeInspection
	 * @noinspection HtmlUnknownAnchorTarget
	 */
	public function basic_options( $field, $instance ) {

		// Display notice in basic options only in numbers and payment-single fields.
		if ( ! in_array( $field['type'], self::BASIC_OPTIONS_NOTICE_FIELD_TYPES, true ) ) {
			return;
		}

		$dismissed       = get_user_meta( get_current_user_id(), 'wpforms_dismissed', true );
		$form_id         = $instance->form_id ?? 0;
		$dismiss_section = "builder-form-$form_id-field-options-calculations-notice";

		// Check whether it is dismissed.
		if ( ! empty( $dismissed[ 'edu-' . $dismiss_section ] ) ) {
			return;
		}

		// Display notice only if Calculations addon is released (available in the `addons.json` file).
		$addon = $this->addons->get_addon( 'calculations' );

		if ( ! $addon ) {
			return;
		}

		if (
			AIHelpers::is_disabled() ||
			(
				wpforms_version_compare(
					$addon['version'] ?? '1.5.0',
					'1.5.0',
					'<='
				)
			)
		) {
			$this->print_standard_education( $dismiss_section );

			return;
		}

		$badge         = esc_html__( 'NEW FEATURE', 'wpforms-lite' );
		$notice_header = esc_html__( 'AI Calculations Are Here!', 'wpforms-lite' );

		$notice = sprintf(
			wp_kses( /* translators: %1$s - link to the WPForms.com doc article. */
				__( 'Easily create advanced calculations with WPForms AI. Head over to the <a href="#advanced-tab">Advanced Tab</a> to get started or read <a href="%1$s" target="_blank" rel="noopener noreferrer">our documentation</a> to learn more.', 'wpforms-lite' ),
				[
					'a' => [
						'href'   => [],
						'rel'    => [],
						'target' => [],
					],
				]
			),
			esc_url( wpforms_utm_link( 'https://wpforms.com/docs/generating-calculation-formulas-with-wpforms-ai/', 'Calculations Education', 'Calculations Documentation' ) )
		);

		printf(
			'<div class="wpforms-alert-ai wpforms-alert wpforms-educational-alert wpforms-calculations wpforms-field-educational-alert wpforms-dismiss-container">
				<span class="wpforms-badge wpforms-badge-sm wpforms-badge-block wpforms-badge-purple wpforms-badge-rounded">
					%5$s
				</span>
				<button type="button" class="wpforms-dismiss-button" title="%1$s" data-section="%2$s"></button>
				<h3>%4$s</h3>
				<p>%3$s</p>
			</div>',
			esc_html__( 'Dismiss this notice.', 'wpforms-lite' ),
			esc_attr( $dismiss_section ),
			$notice, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$notice_header, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$badge // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
	}

	/**
	 * Print standard education notice.
	 *
	 * @since 1.9.4
	 *
	 * @param string $dismiss_section Dismiss section.
	 *
	 * @noinspection HtmlUnknownAnchorTarget
	 * @noinspection HtmlUnknownTarget
	 */
	private function print_standard_education( string $dismiss_section ): void {

		$notice = sprintf(
			wp_kses( /* translators: %1$s - link to the WPForms.com doc article. */
				__( 'Easily perform calculations based on user input. Head over to the <a href="#advanced-tab">Advanced Tab</a> to get started or read <a href="%1$s" target="_blank" rel="noopener noreferrer">our documentation</a> to learn more.', 'wpforms-lite' ),
				[
					'a' => [
						'href'   => [],
						'rel'    => [],
						'target' => [],
					],
				]
			),
			esc_url( wpforms_utm_link( 'https://wpforms.com/docs/calculations-addon/', 'Calculations Education', 'Calculations Documentation' ) )
		);

		printf(
			'<div class="wpforms-alert-info wpforms-alert wpforms-educational-alert wpforms-calculations wpforms-field-educational-alert wpforms-dismiss-container">
				<button type="button" class="wpforms-dismiss-button" title="%1$s" data-section="%2$s"></button>
				<p>%3$s</p>
			</div>',
			esc_html__( 'Dismiss this notice.', 'wpforms-lite' ),
			esc_attr( $dismiss_section ),
			$notice // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
	}

	/**
	 * Display advanced options.
	 *
	 * @since 1.8.4.1
	 *
	 * @param array  $field    Field data.
	 * @param object $instance Builder instance.
	 *
	 * @noinspection ReturnTypeCanBeDeclaredInspection
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function advanced_options( $field, $instance ) {

		if ( ! in_array( $field['type'], self::ALLOWED_FIELD_TYPES, true ) ) {
			return;
		}

		$addon = $this->addons->get_addon( 'calculations' );

		if ( ! $this->is_edu_required_by_status( $addon ) ) {
			return;
		}

		$row_args            = $this->get_row_attributes( $addon );
		$row_args['content'] = $instance->field_element(
			'toggle',
			$field,
			$this->get_field_attributes( $addon ),
			false
		);

		$instance->field_element( 'row', $field, $row_args );
	}

	/**
	 * Get row attributes.
	 *
	 * @since 1.8.4.1
	 *
	 * @param array $addon Addon data.
	 *
	 * @return array
	 */
	private function get_row_attributes( array $addon ): array {

		$data    = $this->prepare_field_action_data( $addon );
		$default = [
			'slug' => 'calculation_is_enabled',
		];

		if ( ! empty( $data ) ) {
			return wp_parse_args( $data, $default );
		}

		return wp_parse_args(
			[
				'data'  => [
					'action'      => 'upgrade',
					'name'        => esc_html__( 'Calculations', 'wpforms-lite' ),
					'utm-content' => 'Enable Calculations',
					'license'     => $addon['license_level'],
				],
				'class' => 'education-modal',
			],
			$default
		);
	}

	/**
	 * Get attributes for the Enable Calculation field.
	 *
	 * @since 1.8.4.1
	 *
	 * @param array $addon Addon data.
	 *
	 * @return array
	 */
	private function get_field_attributes( array $addon ): array {

		$default = [
			'slug'  => 'calculation_is_enabled',
			'value' => '0',
			'desc'  => esc_html__( 'Enable Calculation', 'wpforms-lite' ),
		];

		if ( $addon['plugin_allow'] ) {
			return $default;
		}

		return wp_parse_args(
			[
				'desc'  => sprintf(
					'%1$s%2$s',
					esc_html__( 'Enable Calculation', 'wpforms-lite' ),
					Helpers::get_badge( $addon['license_level'], 'sm', 'inline', 'slate' )
				),
				'attrs' => [
					'disabled' => 'disabled',
				],
			],
			$default
		);
	}

	/**
	 * Determine if we require displaying educational items according to the addon status.
	 *
	 * @since 1.8.4.1
	 *
	 * @param array $addon Addon data.
	 *
	 * @return bool
	 */
	private function is_edu_required_by_status( array $addon ): bool {

		return ! (
			empty( $addon ) ||
			empty( $addon['action'] ) ||
			empty( $addon['status'] ) || (
				$addon['status'] === 'active' && $addon['action'] !== 'upgrade'
			)
		);
	}
}
