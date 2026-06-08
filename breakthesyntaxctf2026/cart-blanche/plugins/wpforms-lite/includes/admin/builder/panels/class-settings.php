<?php

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpIllegalPsrClassPathInspection */
/** @noinspection AutoloadingIssuesInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPForms\Admin\Forms\Tags;

/**
 * Settings management panel.
 *
 * @since 1.0.0
 */
class WPForms_Builder_Panel_Settings extends WPForms_Builder_Panel {

	/**
	 * All systems go.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		// Define panel information.
		$this->name    = esc_html__( 'Settings', 'wpforms-lite' );
		$this->slug    = 'settings';
		$this->icon    = 'fa-sliders';
		$this->order   = 10;
		$this->sidebar = true;

		/**
		 * Filters the form data for the form builder.
		 *
		 * @since 1.9.0
		 *
		 * @param array $form_data Form data.
		 */
		$this->form_data = apply_filters( 'wpforms_builder_panel_settings_init_form_data', $this->form_data );
	}

	/**
	 * Output the Settings panel sidebar.
	 *
	 * @since 1.0.0
	 */
	public function panel_sidebar() {

		// Sidebar contents are not valid unless we have a form.
		if ( ! $this->form ) {
			return;
		}

		$sections = [
			'general'       => esc_html__( 'General', 'wpforms-lite' ),
			'anti_spam'     => esc_html__( 'Spam Protection and Security', 'wpforms-lite' ),
			'confirmation'  => esc_html__( 'Confirmations', 'wpforms-lite' ),
			'notifications' => esc_html__( 'Notifications', 'wpforms-lite' ),
			'themes'        => esc_html__( 'Themes', 'wpforms-lite' ),
		];

		/**
		 * Filters builder settings sections.
		 *
		 * @since 1.1.9
		 *
		 * @param array $sections  Sections.
		 * @param array $form_data Form data.
		 *
		 * @return array
		 */
		$sections = (array) apply_filters( 'wpforms_builder_settings_sections', $sections, $this->form_data ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName

		foreach ( $sections as $slug => $section ) {
			$this->panel_sidebar_section( $section, $slug );
		}
	}

	/**
	 * Enqueue assets.
	 *
	 * @since 1.7.5
	 */
	public function enqueues() {

		$min = wpforms_get_min_suffix();

		wp_enqueue_script(
			'wpforms-builder-settings',
			WPFORMS_PLUGIN_URL . "assets/js/admin/builder/settings{$min}.js",
			[ 'wpforms-builder' ],
			WPFORMS_VERSION,
			true
		);

		wp_localize_script(
			'wpforms-builder-settings',
			'wpforms_builder_settings',
			[
				'choicesjs_config' => $this->get_choicesjs_config(),
				'all_tags_choices' => Tags::get_all_tags_choices(),
			]
		);
	}

	/**
	 * Get Choices.js configuration.
	 *
	 * @since 1.7.5
	 *
	 * @return array
	 */
	private function get_choicesjs_config(): array {

		$config = Tags::get_choicesjs_config();

		$config['noResultsText'] = esc_html__( 'Press Enter or "," key to add new tag', 'wpforms-lite' );

		return $config;
	}

	/**
	 * Output the Settings panel primary content.
	 *
	 * @since 1.0.0
	 */
	public function panel_content() {

		// Check if there is a form created.
		if ( ! $this->form ) {
			echo '<div class="wpforms-alert wpforms-alert-info">';
			echo wp_kses(
				__( 'You need to <a href="#" class="wpforms-panel-switch" data-panel="setup">setup your form</a> before you can manage the settings.', 'wpforms-lite' ),
				[
					'a' => [
						'href'       => [],
						'class'      => [],
						'data-panel' => [],
					],
				]
			);
			echo '</div>';

			return;
		}

		/*
		 * General.
		 */
		echo '<div class="wpforms-panel-content-section wpforms-panel-content-section-general">';
			echo '<div class="wpforms-panel-content-section-title">';
				esc_html_e( 'General', 'wpforms-lite' );
			echo '</div>';

			wpforms_panel_field(
				'text',
				'settings',
				'form_title',
				$this->form_data,
				esc_html__( 'Form Name', 'wpforms-lite' ),
				[
					'default' => $this->form->post_title,
				]
			);
			wpforms_panel_field(
				'textarea',
				'settings',
				'form_desc',
				$this->form_data,
				esc_html__( 'Form Description', 'wpforms-lite' ),
				[
					'tooltip'     => esc_html__( 'Enter descriptive text or instructions to help your users understand the requirements of your form.', 'wpforms-lite' ),
					'input_class' => 'wpforms-smart-tags-enabled',
					'data'        => [
						'type'   => 'all',
						'fields' => '',
					],
				]
			);

			if ( $this->form->post_type === 'wpforms-template' ) {
				wpforms_panel_field(
					'textarea',
					'settings',
					'template_description',
					$this->form_data,
					esc_html__( 'Template Description', 'wpforms-lite' ),
					[
						'tooltip' => esc_html__( 'Describe the use case for your template. Only displayed internally.', 'wpforms-lite' ),
					]
				);
			}

			$this->general_setting_tags();

			wpforms_panel_field(
				'text',
				'settings',
				'submit_text',
				$this->form_data,
				esc_html__( 'Submit Button Text', 'wpforms-lite' ),
				[
					'default' => esc_html__( 'Submit', 'wpforms-lite' ),
				]
			);
			wpforms_panel_field(
				'text',
				'settings',
				'submit_text_processing',
				$this->form_data,
				esc_html__( 'Submit Button Processing Text', 'wpforms-lite' ),
				[
					'tooltip' => esc_html__( 'Enter the submit button text you would like the button display while the form submit is processing.', 'wpforms-lite' ),
				]
			);

			$this->general_setting_advanced();

		echo '</div>';

		/*
		 * Notifications.
		 */
		echo '<div class="wpforms-panel-content-section wpforms-panel-content-section-notifications" data-panel="notifications">';

		/**
		 * Output notifications.
		 *
		 * @since 1.6.7.3
		 *
		 * @param WPForms_Builder_Panel_Settings $settings Current settings.
		 */
		do_action( 'wpforms_form_settings_notifications', $this ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName

		echo '</div>';

		/*
		 * Confirmations.
		 */
		echo '<div class="wpforms-panel-content-section wpforms-panel-content-section-confirmation" data-panel="confirmations">';

		/**
		 * Output confirmations.
		 *
		 * @since 1.6.7.3
		 *
		 * @param WPForms_Builder_Panel_Settings $settings Current settings.
		 */
		do_action( 'wpforms_form_settings_confirmations', $this ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName

		echo '</div>';

		/**
		 * Output custom panels.
		 *
		 * @since 1.6.7.3
		 *
		 * @param WPForms_Builder_Panel_Settings $settings Current settings.
		 */
		do_action( 'wpforms_form_settings_panel_content', $this ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
	}

	/**
	 * Output the Tags setting.
	 *
	 * @since 1.7.5
	 */
	private function general_setting_tags() {

		$form_tags = [];

		if ( ! empty( $this->form_data['settings']['form_tags'] ) ) {
			$form_tags = get_terms(
				[
					'taxonomy'   => WPForms_Form_Handler::TAGS_TAXONOMY,
					'name'       => $this->form_data['settings']['form_tags'],
					'hide_empty' => false,
				]
			);
			$form_tags = is_wp_error( $form_tags ) ? [] : (array) $form_tags;
		}

		$tags_value   = wp_list_pluck( $form_tags, 'term_id' );
		$tags_options = wp_list_pluck( $form_tags, 'name', 'term_id' );

		wpforms_panel_field(
			'select',
			'settings',
			'form_tags',
			$this->form_data,
			esc_html__( 'Tags', 'wpforms-lite' ),
			[
				'options'  => $tags_options,
				'value'    => $tags_value,
				'multiple' => true,
				'tooltip'  => esc_html__( 'Mark form with the tags. To create a new tag, simply type it and press Enter.', 'wpforms-lite' ),
			]
		);
	}

	/**
	 * Output the *CAPTCHA settings.
	 *
	 * @since 1.6.8
	 *
	 * @noinspection HtmlUnknownTarget
	 */
	private function general_setting_advanced() {

		ob_start();

		wpforms_panel_field(
			'text',
			'settings',
			'form_class',
			$this->form_data,
			esc_html__( 'Form CSS Class', 'wpforms-lite' ),
			[
				'tooltip' => esc_html__( 'Enter CSS class names for the form wrapper. Multiple class names should be separated with spaces.', 'wpforms-lite' ),
			]
		);

		wpforms_panel_field(
			'text',
			'settings',
			'submit_class',
			$this->form_data,
			esc_html__( 'Submit Button CSS Class', 'wpforms-lite' ),
			[
				'tooltip' => esc_html__( 'Enter CSS class names for the form submit button. Multiple names should be separated with spaces.', 'wpforms-lite' ),
			]
		);

		wpforms_panel_field(
			'toggle',
			'settings',
			'dynamic_population',
			$this->form_data,
			esc_html__( 'Enable Prefill by URL', 'wpforms-lite' ),
			[
				'tooltip' => sprintf(
					'<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
					wpforms_utm_link( 'https://wpforms.com/developers/how-to-enable-dynamic-field-population/', 'Builder Settings', 'Prefill by URL Tooltip' ),
					esc_html__( 'How to use Prefill by URL', 'wpforms-lite' )
				),
			]
		);

		wpforms_panel_field(
			'toggle',
			'settings',
			'ajax_submit',
			$this->form_data,
			esc_html__( 'Enable AJAX form submission', 'wpforms-lite' ),
			[
				'tooltip' => esc_html__( 'Enables form submission without page reload.', 'wpforms-lite' ),
			]
		);

		/**
		 * Fires after general settings.
		 *
		 * @since 1.0.2
		 *
		 * @param WPForms_Builder_Panel_Settings $settings Current settings.
		 */
		do_action( 'wpforms_form_settings_general', $this ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName

		// Wrap advanced settings to the unfoldable group.
		wpforms_panel_fields_group(
			ob_get_clean(),
			[
				'borders'    => [ 'top' ],
				'unfoldable' => true,
				'group'      => 'settings_advanced',
				'title'      => esc_html__( 'Advanced', 'wpforms-lite' ),
			]
		);
	}
}

new WPForms_Builder_Panel_Settings();
