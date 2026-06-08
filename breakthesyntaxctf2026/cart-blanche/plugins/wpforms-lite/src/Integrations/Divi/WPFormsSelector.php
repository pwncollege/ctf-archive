<?php

// phpcs:ignore Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpUndefinedClassInspection */

namespace WPForms\Integrations\Divi;

use ET_Builder_Module;
use WP_Post;
use WPForms\Integrations\Divi\Interfaces\FormsResolverInterface;
use WPForms\Integrations\Divi\Interfaces\LocalizedDataInterface;
use WPForms\Integrations\Divi\Traits\FormsResolverTrait;
use WPForms\Integrations\Divi\Traits\LocalizedDataTrait;

/**
 * Class WPFormsSelector.
 *
 * @since 1.6.3
 */
class WPFormsSelector extends ET_Builder_Module implements LocalizedDataInterface, FormsResolverInterface {

	use LocalizedDataTrait;
	use FormsResolverTrait;

	/**
	 * Module slug.
	 *
	 * @since 1.6.3
	 *
	 * @var string
	 */
	public $slug = 'wpforms_selector';

	/**
	 * VB support.
	 *
	 * @since 1.6.3
	 *
	 * @var string
	 */
	public $vb_support = 'on';

	/**
	 * Module name.
	 *
	 * @since 1.6.3
	 *
	 * @var string
	 */
	public $name;

	/**
	 * Init module.
	 *
	 * @since 1.6.3
	 */
	public function init(): void {

		$this->name = esc_html__( 'WPForms', 'wpforms-lite' );
	}

	/**
	 * Adds a form to the option array, using the form's ID as the key and a decoded title as the value.
	 *
	 * @since 1.9.9
	 *
	 * @param array   $options The option array to be updated.
	 * @param WP_Post $form    The form object containing the ID and title to be added.
	 *
	 * @return array Updated options array with the form added.
	 */
	public function add_form_in_options( array $options, WP_Post $form ): array {

		$options[ $form->ID ] = htmlspecialchars_decode( $form->post_title, ENT_QUOTES );

		return $options;
	}

	/**
	 * Get a list of settings.
	 *
	 * @since 1.6.3
	 *
	 * @return array
	 */
	public function get_fields(): array {

		$forms         = $this->get_form_options();
		$default_value = '';

		if ( ! empty( $forms ) ) {
			$forms[0]      = esc_html__( 'Select form', 'wpforms-lite' );
			$default_value = 0;
		}

		return [
			'form_id'    => [
				'label'           => esc_html__( 'Form', 'wpforms-lite' ),
				'type'            => 'select',
				'option_category' => 'basic_option',
				'toggle_slug'     => 'main_content',
				'options'         => $forms,
				'default'         => $default_value,
			],
			'show_title' => [
				'label'           => esc_html__( 'Show Title', 'wpforms-lite' ),
				'type'            => 'yes_no_button',
				'option_category' => 'basic_option',
				'toggle_slug'     => 'main_content',
				'options'         => [
					'off' => esc_html__( 'Off', 'wpforms-lite' ),
					'on'  => esc_html__( 'On', 'wpforms-lite' ),
				],
			],
			'show_desc'  => [
				'label'           => esc_html__( 'Show Description', 'wpforms-lite' ),
				'option_category' => 'basic_option',
				'type'            => 'yes_no_button',
				'toggle_slug'     => 'main_content',
				'options'         => [
					'off' => esc_html__( 'Off', 'wpforms-lite' ),
					'on'  => esc_html__( 'On', 'wpforms-lite' ),
				],
			],
		];
	}


	/**
	 * Disable advanced fields configuration.
	 *
	 * @since 1.6.3
	 *
	 * @return array
	 */
	public function get_advanced_fields_config(): array {

		return [
			'link_options' => false,
			'text'         => false,
			'background'   => false,
			'borders'      => false,
			'box_shadow'   => false,
			'button'       => false,
			'filters'      => false,
			'fonts'        => false,
		];
	}

	/**
	 * Render module on the frontend.
	 *
	 * @since 1.6.3
	 *
	 * @param array  $attrs       List of unprocessed attributes.
	 * @param string $content     Content being processed.
	 * @param string $render_slug Slug of module that is used for rendering output.
	 *
	 * @return string
	 * @noinspection PhpMissingParamTypeInspection
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function render( $attrs, $content = null, $render_slug = '' ): string {

		if ( empty( $this->props['form_id'] ) ) {
			return '';
		}

		$form_id    = absint( $this->props['form_id'] );
		$show_title = ( $this->props['show_title'] ?? '' ) === 'on';
		$show_desc  = ( $this->props['show_desc'] ?? '' ) === 'on';

		return do_shortcode(
			sprintf(
				'[wpforms id="%1$s" title="%2$s" description="%3$s"]',
				$form_id,
				/**
				 * Filters form title display flag.
				 *
				 * @since 1.6.3
				 *
				 * @param bool $show_title Show form title.
				 * @param int  $form_id    Form ID.
				 */
				(bool) apply_filters( 'wpforms_divi_builder_form_title', $show_title, $form_id ), // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
				/**
				 * Filters form description display flag.
				 *
				 * @since 1.6.3
				 *
				 * @param bool $show_desc Show form description.
				 * @param int  $form_id   Form ID.
				 */
				(bool) apply_filters( 'wpforms_divi_builder_form_desc', $show_desc, $form_id ) // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
			)
		);
	}
}
