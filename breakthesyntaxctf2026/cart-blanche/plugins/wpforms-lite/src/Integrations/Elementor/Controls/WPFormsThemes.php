<?php

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpUndefinedNamespaceInspection */
/** @noinspection PhpUndefinedClassInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

namespace WPForms\Integrations\Elementor\Controls;

use Elementor\Base_Data_Control;

/**
 * Custom WPForms Themes control for Elementor editor.
 *
 * @since 1.9.6
 */
class WPFormsThemes extends Base_Data_Control {

	/**
	 * Get the control type.
	 *
	 * @since 1.9.6
	 *
	 * @return string Control type.
	 */
	public function get_type() {

		return 'wpforms_themes';
	}

	/**
	 * Get the control's default settings.
	 *
	 * @since 1.9.6
	 *
	 * @return array Control default settings.
	 */
	protected function get_default_settings() {

		return [
			'label_block' => true,
		];
	}

	/**
	 * Render control output in the editor.
	 *
	 * @since 1.9.6
	 */
	public function content_template() {
		?>
		<div class="elementor-control-field">
			<div class="elementor-control-input-wrapper">
				<div class="wpforms-elementor-themes-control"></div>
			</div>
		</div>
		<?php
	}
}
