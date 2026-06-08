<?php

namespace WPForms\Admin\Education\Builder;

use WPForms\Admin\Education\AddonsItemBase;
use WPForms\Admin\Education\Helpers;


/**
 * Builder/Quiz Education feature for Lite and Pro.
 *
 * @since 1.9.9
 */
class Quiz extends AddonsItemBase {

	/**
	 * Indicate if the current Education feature is allowed to load.
	 *
	 * @since        1.9.9
	 *
	 * @return bool
	 *
	 * @noinspection ReturnTypeCanBeDeclaredInspection
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function allow_load() {

		return wpforms_is_admin_page( 'builder' ) || wp_doing_ajax();
	}

	/**
	 * Hooks.
	 *
	 * @since        1.9.9
	 *
	 * @noinspection ReturnTypeCanBeDeclaredInspection
	 */
	public function hooks() {

		add_action( 'wpforms_field_options_before_description', [ $this, 'quiz_fields' ], 10, 2 );
	}

	/**
	 * Display the Enable Quiz option.
	 *
	 * @since        1.9.9
	 *
	 * @param array  $field    Field data.
	 * @param object $instance Builder instance.
	 *
	 * @noinspection ReturnTypeCanBeDeclaredInspection
	 * @noinspection PhpMissingParamTypeInspection
	 * @noinspection HtmlUnknownTarget
	 */
	public function quiz_fields( $field, $instance ) {

		if ( ! in_array( $field['type'], [ 'radio', 'checkbox', 'select' ], true ) ) {
			return;
		}

		$addon = $this->addons->get_addon( 'quiz' );

		if (
			empty( $addon ) ||
			empty( $addon['action'] ) ||
			empty( $addon['status'] ) || (
				$addon['status'] === 'active' &&
				$addon['action'] !== 'upgrade'
			)
		) {
			return;
		}

		$form_id             = ! empty( $instance->form_id ) ? (int) $instance->form_id : 0;
		$row_args            = $this->get_enable_quiz_row_attributes( $addon, $form_id );
		$row_args['content'] = $instance->field_element(
			'toggle',
			$field,
			$this->get_enable_quiz_field_attributes( $addon ),
			false
		);

		$instance->field_element(
			'row',
			$field,
			$row_args
		);

		$dismissed       = get_user_meta( get_current_user_id(), 'wpforms_dismissed', true );
		$dismiss_section = "builder-form-$form_id-field-options-quiz-notice";

		// Check whether it is dismissed.
		if ( ! empty( $dismissed[ 'edu-' . $dismiss_section ] ) ) {
			return;
		}

		$badge         = esc_html__( 'NEW FEATURE', 'wpforms-lite' );
		$notice_header = esc_html__( 'Turn Your Form Into a Quiz', 'wpforms-lite' );

		$notice = sprintf(
			wp_kses( /* translators: %1$s - link to the WPForms.com doc article. */
				__( 'Easily create interactive quizzes. Add true or false, multiple choice, or checkbox questions. Set correct answers and automatically score submissions. <a href="%1$s" target="_blank" rel="noopener noreferrer">Learn more about the Quiz Addon</a>', 'wpforms-lite' ),
				[
					'a' => [
						'href'   => [],
						'rel'    => [],
						'target' => [],
					],
				]
			),
			esc_url( wpforms_utm_link( 'https://wpforms.com/docs/quiz-addon/', 'Quiz Education', 'Quiz Documentation' ) )
		);

		printf(
			'<div class="wpforms-alert wpforms-alert-info wpforms-educational-alert wpforms-field-educational-alert wpforms-dismiss-container">
				<span class="wpforms-badge wpforms-badge-sm wpforms-badge-block wpforms-badge-green wpforms-badge-rounded">
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
	 * Get attributes for the `Enable Quiz` field option row.
	 *
	 * @since 1.9.9
	 *
	 * @param array $addon   Current addon information.
	 * @param int   $form_id Form ID.
	 *
	 * @return array
	 */
	private function get_enable_quiz_row_attributes( array $addon, int $form_id ): array {

		$data    = $this->prepare_field_action_data( $addon );
		$default = [
			'slug' => 'enable_quiz',
		];

		if ( ! empty( $data ) ) {
			$data = wp_parse_args( $data, $default );

			$data['data']['redirect-url'] = add_query_arg(
				[
					'page'    => 'wpforms-builder',
					'view'    => 'settings',
					'form_id' => $form_id,
					'section' => 'quiz',
				],
				admin_url( 'admin.php' )
			);

			return $data;
		}

		return wp_parse_args(
			[
				'data'  => [
					'action'      => 'upgrade',
					'name'        => esc_html__( 'Quiz Addon', 'wpforms-lite' ),
					'utm-content' => 'Quiz Addon',
					'licence'     => 'pro',
					'message'     => esc_html__( 'We\'re sorry, Enable Quiz is part of the Quiz Addon and not available on your plan. Please upgrade to the PRO plan to unlock all these awesome features.', 'wpforms-lite' ),
				],
				'class' => 'education-modal',
			],
			$default
		);
	}

	/**
	 * Get attributes for the `Enable Quiz` field option.
	 *
	 * @since 1.9.9
	 *
	 * @param array $addon Current addon information.
	 *
	 * @return array
	 */
	private function get_enable_quiz_field_attributes( array $addon ): array {

		$default = [
			'slug'  => 'enable_quiz',
			'value' => '0',
			'desc'  => esc_html__( 'Include in Quiz Scoring', 'wpforms-lite' ),
		];

		if ( $addon['plugin_allow'] ) {
			return $default;
		}

		return wp_parse_args(
			[
				'desc'  => sprintf(
					'%1$s%2$s',
					esc_html__( 'Include in Quiz Scoring', 'wpforms-lite' ),
					Helpers::get_badge( 'Pro', 'sm', 'inline', 'slate' )
				),
				'attrs' => [
					'disabled' => 'disabled',
				],
			],
			$default
		);
	}
}
