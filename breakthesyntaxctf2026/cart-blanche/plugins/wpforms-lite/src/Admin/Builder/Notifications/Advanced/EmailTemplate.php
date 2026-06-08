<?php

namespace WPForms\Admin\Builder\Notifications\Advanced;

use WPForms_Builder_Panel_Settings;
use WPForms\Emails\Helpers;
use WPForms\Admin\Education\Helpers as EducationHelpers;

/**
 * Email Template.
 * This class will register the Email Template field in the "Notification" → "Advanced" settings.
 * The Email Template field will allow users to override the default email template for a specific notification.
 *
 * @since 1.8.5
 */
class EmailTemplate {

	/**
	 * Initialize class.
	 *
	 * @since 1.8.5
	 */
	public function init() {

		$this->hooks();
	}

	/**
	 * Hooks.
	 *
	 * @since 1.8.5
	 */
	private function hooks() {

		add_action( 'wpforms_builder_enqueues', [ $this, 'builder_assets' ] );
		add_action( 'wpforms_builder_print_footer_scripts', [ $this, 'builder_footer_scripts' ] );
		add_filter( 'wpforms_lite_admin_education_builder_notifications_advanced_settings_content', [ $this, 'settings' ], 5, 3 );
		add_filter( 'wpforms_pro_admin_builder_notifications_advanced_settings_content', [ $this, 'settings' ], 5, 3 );
	}

	/**
	 * Enqueue assets for the builder.
	 *
	 * @since 1.8.5
	 */
	public function builder_assets() {

		$min = wpforms_get_min_suffix();

		wp_enqueue_script(
			'wpforms-builder-email-template',
			WPFORMS_PLUGIN_URL . "assets/js/admin/builder/email-template{$min}.js",
			[ 'jquery', 'jquery-confirm', 'wpforms-builder' ],
			WPFORMS_VERSION,
			true
		);

		wp_localize_script(
			'wpforms-builder-email-template',
			'wpforms_builder_email_template',
			[
				'is_pro'    => wpforms()->is_pro(),
				'templates' => Helpers::get_email_template_choices( false ),
			]
		);
	}

	/**
	 * Output Email Template modal.
	 *
	 * @since 1.8.5
	 */
	public function builder_footer_scripts() {

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wpforms_render(
			'builder/notifications/email-template-modal',
			[
				'pro_badge' => ! wpforms()->is_pro() ? EducationHelpers::get_badge( 'Pro' ) : '',
			],
			true
		);
	}

	/**
	 * Add Email Template settings.
	 *
	 * @since 1.8.5
	 *
	 * @param string                         $content  Notification → Advanced content.
	 * @param WPForms_Builder_Panel_Settings $settings Builder panel settings.
	 * @param int                            $id       Notification id.
	 *
	 * @return string
	 */
	public function settings( $content, $settings, $id ) {

		// Retrieve email template choices and disabled choices.
		// A few of the email templates are only available in the Pro version and will be disabled for non-Pro users.
		// The disabled choices will be added to the select field with a "(Pro)" label appended to the name.
		list( $options, $disabled_options ) = $this->get_email_template_options();

		// Add Email Template field.
		$content .= wpforms_panel_field(
			'select',
			'notifications',
			'template',
			$settings->form_data,
			esc_html__( 'Email Template', 'wpforms-lite' ),
			[
				'default'          => '',
				'options'          => $options,
				'disabled_options' => $disabled_options,
				'class'            => 'wpforms-panel-field-email-template-wrap',
				'input_class'      => 'wpforms-panel-field-email-template',
				'parent'           => 'settings',
				'subsection'       => $id,
				'after'            => $this->get_template_modal_link_content(),
				'tooltip'          => esc_html__( 'Override the default email template for this specific notification.', 'wpforms-lite' ),
			],
			false
		);

		return $content;
	}

	/**
	 * Get Email template choices.
	 *
	 * This function will return an array of email template choices and an array of disabled choices.
	 * The disabled choices are templates that are only available in the Pro version.
	 *
	 * @since 1.8.5
	 *
	 * @return array
	 */
	private function get_email_template_options() {

		// Retrieve the available email template choices.
		$choices = Helpers::get_email_template_choices( false );

		// If there are no templates or the choices are not an array, return empty arrays.
		if ( empty( $choices ) || ! is_array( $choices ) ) {
			return [ [], [] ];
		}

		// Check if the Pro version is active.
		$is_pro = wpforms()->is_pro();

		// Initialize arrays for options and disabled options.
		$options          = [];
		$disabled_options = [];

		// Iterate through the templates and build the $options array.
		foreach ( $choices as $key => $choice ) {
			$value       = esc_attr( $key );
			$name        = esc_html( $choice['name'] );
			$is_disabled = ! $is_pro && isset( $choice['is_pro'] ) && $choice['is_pro'];

			// If the option is disabled for non-Pro users, add it to the disabled options array.
			if ( $is_disabled ) {
				$disabled_options[] = $value;
			}

			// Build the $options array with appropriate labels.
			// Pro badge labels are not meant to be translated.
			$options[ $key ] = $is_disabled ? sprintf( '%s (Pro)', $name ) : $name;
		}

		// Add an empty option to the beginning of the $options array.
		// This is a placeholder option that will be replaced with the default template name.
		$options = array_merge( [ '' => esc_html__( 'Default Template', 'wpforms-lite' ) ], $options );

		// Return the options and disabled options arrays.
		return [ $options, $disabled_options ];
	}

	/**
	 * Get Email template modal link content.
	 *
	 * @since 1.8.5
	 *
	 * @return string
	 */
	private function get_template_modal_link_content() {

		return wpforms_render( 'builder/notifications/email-template-link' );
	}
}
