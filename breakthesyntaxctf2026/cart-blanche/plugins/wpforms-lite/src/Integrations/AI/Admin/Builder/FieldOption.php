<?php

namespace WPForms\Integrations\AI\Admin\Builder;

use WPForms\Integrations\LiteConnect\LiteConnect;

/**
 * AI Field Option class.
 *
 * @since 1.9.1
 */
class FieldOption {

	/**
	 * Initialize.
	 *
	 * @since 1.9.1
	 */
	public function init() {

		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.9.1
	 */
	private function hooks() {

		add_action( 'wpforms_field_option_ai_modal_button', [ $this, 'add_option' ], 10, 4 );
	}

	/**
	 * Add AI Modal button to the field options.
	 *
	 * @since 1.9.1
	 *
	 * @param string|mixed $output        HTML output.
	 * @param array        $field         Field settings.
	 * @param array        $args          Additional arguments.
	 * @param object       $wpforms_field WPForms_Field object.
	 *
	 * @return string
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function add_option( $output, array $field, array $args, $wpforms_field ): string {

		$type    = $args['type'] ?? 'default';
		$data    = [
			'field-id' => $field['id'],
		];
		$classes = [
			'wpforms-btn-purple',
			'wpforms-ai-modal-button',
			'wpforms-ai-' . $type . '-button',
			empty( $field['dynamic_choices'] ) ? '' : 'wpforms-hidden',
		];
		$attrs   = [];

		[ $classes, $data, $attrs ] = $this->maybe_disable_button( $classes, $data, $attrs );

		$button = $wpforms_field->field_element(
			'button',
			$field,
			[
				'slug'  => 'ai_modal_button',
				'value' => $args['value'] ?? esc_html__( 'Open AI Modal', 'wpforms-lite' ),
				'class' => wpforms_sanitize_classes( $classes ),
				'data'  => $data,
				'attrs' => $attrs,
			],
			false
		);

		return (string) $wpforms_field->field_element(
			'row',
			$field,
			[
				'slug'    => 'ai_modal_button',
				'content' => $button,
			],
			false
		);
	}

	/**
	 * Maybe disable the button and show modal.
	 *
	 * @since 1.9.1
	 *
	 * @param array $classes Classes list.
	 * @param array $data    Data arguments list.
	 * @param array $attrs   Attributes list.
	 *
	 * @return array
	 */
	private function maybe_disable_button( array $classes, array $data, array $attrs ): array {

		$is_pro = wpforms()->is_pro();

		// Pro, license is not active.
		if ( $is_pro && ! $this->is_license_active() ) {
			$classes[]           = 'education-modal';
			$classes[]           = 'wpforms-prevent-default';
			$data['action']      = 'license';
			$data['field-name']  = 'AI Choices';
			$data['utm-content'] = 'AI Choices';

			return [ $classes, $data, $attrs ];
		}

		// Lite, LC is not enabled.
		if ( ! $is_pro && ! LiteConnect::is_enabled() && LiteConnect::is_allowed() ) {
			$classes[] = 'enable-lite-connect-modal';
			$classes[] = 'wpforms-prevent-default';
		}

		// Lite, LC is not configured or not allowed.
		if ( ! $is_pro && ! LiteConnect::is_allowed() ) {
			$classes[] = 'wpforms-prevent-default';
			$classes[] = 'wpforms-inactive';
			$classes[] = 'wpforms-help-tooltip';

			$attrs['title'] = esc_html__( 'WPForms AI is not available on local sites.', 'wpforms-lite' );

			$data['tooltip-position'] = 'top';
		}

		return [ $classes, $data, $attrs ];
	}

	/**
	 * Determine whether a license key is active.
	 *
	 * @since 1.9.1
	 *
	 * @return bool
	 */
	private function is_license_active(): bool {

		$license = (array) get_option( 'wpforms_license', [] );

		return ! empty( wpforms_get_license_key() ) &&
			empty( $license['is_expired'] ) &&
			empty( $license['is_disabled'] ) &&
			empty( $license['is_invalid'] );
	}
}
