<?php

namespace WPForms\Lite\Admin\Education\Builder;

use WPForms\Admin\Education;
use WPForms\Helpers\Form;

/**
 * Builder/Fields Education for Lite.
 *
 * @since 1.6.6
 */
class Fields extends Education\Builder\Fields {

	/**
	 * Hooks.
	 *
	 * @since 1.6.6
	 */
	public function hooks() {

		add_filter( 'wpforms_builder_fields_buttons', [ $this, 'add_fields' ], 500 );
		add_filter( 'wpforms_builder_field_button_attributes', [ $this, 'fields_attributes' ], 100, 2 );
		add_action( 'wpforms_field_options_after_advanced-options', [ $this, 'field_conditional_logic' ] );
		add_action( 'wpforms_builder_panel_fields_panel_content_title_after', [ $this, 'form_preview_notice' ] );
	}

	/**
	 * Add fields.
	 *
	 * @since 1.6.6
	 *
	 * @param array|mixed $fields Form fields.
	 *
	 * @return array
	 */
	public function add_fields( $fields ) {

		$fields = (array) $fields;

		foreach ( $fields as $group => $group_data ) {
			$edu_fields = $this->fields->get_by_group( $group );
			$edu_fields = $this->fields->set_values( $edu_fields, 'class', 'education-modal', 'empty' );

			foreach ( $edu_fields as $edu_field ) {

				// Skip if in the current group already exist field of this type.
				if ( ! empty( wp_list_filter( $group_data, [ 'type' => $edu_field['type'] ] ) ) ) {
					continue;
				}

				$addon = ! empty( $edu_field['addon'] ) ? $this->addons->get_addon( $edu_field['addon'] ) : [];

				if ( ! empty( $addon ) ) {
					$edu_field['license'] = $addon['license_level'] ?? '';
				}

				$fields[ $group ]['fields'][] = $edu_field;
			}
		}

		return $fields;
	}

	/**
	 * Display a conditional logic settings section for fields inside the form builder.
	 *
	 * @since 1.6.6
	 *
	 * @param array $field Field data.
	 */
	public function field_conditional_logic( array $field ): void {

		// Certain fields don't support conditional logic.
		if ( in_array( $field['type'], [ 'pagebreak', 'divider', 'hidden' ], true ) ) {
			return;
		}

		?>
		<div class="wpforms-field-option-group wpforms-field-option-group-conditionals">
			<a href="#"
				class="wpforms-field-option-group-toggle education-modal"
				data-name="<?php esc_attr_e( 'Smart Conditional Logic', 'wpforms-lite' ); ?>"
				data-utm-content="Smart Conditional Logic">
				<?php esc_html_e( 'Smart Logic', 'wpforms-lite' ); ?>
			</a>
		</div>
		<?php
	}

	/**
	 * Adjust attributes on field buttons.
	 *
	 * @since 1.6.6
	 *
	 * @param array|mixed $atts  Button attributes.
	 * @param array       $field Button properties.
	 *
	 * @return array Attributes array.
	 */
	public function fields_attributes( $atts, $field ) {

		$atts = (array) $atts;

		$atts['data']['utm-content'] = ! empty( $field['name_en'] ) ? $field['name_en'] : '';

		if ( ! empty( $field['class'] ) && $field['class'] === 'education-modal' ) {
			$atts['class'][] = 'wpforms-not-available';
		}

		if ( empty( $field['addon'] ) ) {
			return $atts;
		}

		$addon = $this->addons->get_addon( $field['addon'] );

		if ( empty( $addon ) ) {
			return $atts;
		}

		if ( ! empty( $addon['video'] ) ) {
			$atts['data']['video'] = $addon['video'];
		}

		if ( ! empty( $field['license'] ) ) {
			$atts['data']['license'] = $field['license'];
		}

		return $atts;
	}

	/**
	 * The form preview Pro fields notice.
	 *
	 * @since 1.9.4
	 *
	 * @param array $form_data Form data.
	 *
	 * @noinspection HtmlUnknownTarget
	 */
	public function form_preview_notice( array $form_data ): void {

		$dismissed       = get_user_meta( get_current_user_id(), 'wpforms_dismissed', true );
		$pro_fields      = Form::get_form_pro_fields( $form_data );
		$is_quiz_enabled = ! empty( $form_data['settings']['quiz']['enabled'] );

		// Check whether the notice is dismissed OR the form doesn't contain Pro fields.
		if (
			! empty( $dismissed['edu-pro-fields-form-preview-notice'] ) ||
			( empty( $pro_fields ) && ! $is_quiz_enabled )
		) {
			return;
		}

		if ( $is_quiz_enabled ) {
			$this->print_quiz_notice();

			return;
		}

		$content = sprintf(
			wp_kses( /* translators: %s - WPForms.com announcement page URL. */
				__( 'They will not be present in the published form. <a href="%1$s" target="_blank" rel="noopener noreferrer">Upgrade now</a> to unlock these features.', 'wpforms-lite' ),
				[
					'a' => [
						'href'   => [],
						'target' => [],
						'rel'    => [],
					],
				]
			),
			wpforms_admin_upgrade_link( 'Builder - Settings', 'AI Form - Pro Fields in Lite notice' )
		);

		$this->print_form_preview_notice(
			[
				'class'           => 'wpforms-alert-warning',
				'title'           => esc_html__( 'Your Form Contains Pro Fields', 'wpforms-lite' ),
				'content'         => $content,
				'dismiss_section' => 'pro-fields-form-preview-notice',
			]
		);
	}

	/**
	 * Print the Quiz addon notice.
	 *
	 * @since 1.9.9
	 *
	 * @noinspection HtmlUnknownTarget
	 */
	private function print_quiz_notice(): void {

		$content = sprintf(
			wp_kses( /* translators: %s - Upgrade license page URL. */
				__( 'Quiz functionality will not be present in the published form. <a href="%1$s" target="_blank" rel="noopener noreferrer">Upgrade now</a> to unlock the Quiz Addon.', 'wpforms-lite' ),
				[
					'a' => [
						'href'   => [],
						'target' => [],
						'rel'    => [],
					],
				]
			),
			wpforms_admin_upgrade_link( 'Builder - Settings', 'AI Form - Quiz addon in Lite notice' )
		);

		$this->print_form_preview_notice(
			[
				'class'           => 'wpforms-alert-warning',
				'title'           => esc_html__( 'Your Form Uses the Quiz Addon', 'wpforms-lite' ),
				'content'         => $content,
				'dismiss_section' => 'quiz-form-preview-notice',
			]
		);
	}
}
