<?php

namespace WPForms\Admin\Education\Builder;

use WPForms\Admin\Education\AddonsItemBase;
use WPForms\Admin\Education\Fields as EducationFields;

/**
 * Base class for Builder/Fields Education feature.
 *
 * @since 1.6.6
 */
abstract class Fields extends AddonsItemBase {

	/**
	 * Instance of the Education\Fields class.
	 *
	 * @since 1.6.6
	 *
	 * @var EducationFields
	 */
	protected $fields;

	/**
	 * Indicate if current Education feature is allowed to load.
	 *
	 * @since 1.6.6
	 *
	 * @return bool
	 */
	public function allow_load(): bool {

		return wp_doing_ajax() || wpforms_is_admin_page( 'builder' );
	}

	/**
	 * Init.
	 *
	 * @since 1.6.6
	 */
	public function init(): void {

		parent::init();

		// Store the instance of the Education\Fields class.
		$this->fields = wpforms()->obj( 'education_fields' );
	}

	/**
	 * Print the form preview notice.
	 *
	 * @since 1.9.4
	 *
	 * @param array $texts Notice texts.
	 */
	protected function print_form_preview_notice( $texts ): void {

		printf(
			'<div class="wpforms-alert %1$s wpforms-alert-dismissible wpforms-pro-fields-notice wpforms-dismiss-container">
				<div class="wpforms-alert-message">
					<h3>%2$s</h3>
					<p>%3$s</p>
				</div>
				<div class="wpforms-alert-buttons">
					<button type="button" class="wpforms-dismiss-button" data-section="%4$s" title="%5$s" />
				</div>
			</div>',
			esc_attr( $texts['class'] ),
			esc_html( $texts['title'] ),
			$texts['content'], // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			esc_html( $texts['dismiss_section'] ),
			esc_attr__( 'Dismiss this notice', 'wpforms-lite' )
		);
	}
}
