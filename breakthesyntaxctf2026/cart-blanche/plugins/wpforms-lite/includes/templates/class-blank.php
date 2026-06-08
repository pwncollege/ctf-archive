<?php

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection AutoloadingIssuesInspection */
/** @noinspection PhpIllegalPsrClassPathInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Blank form template.
 *
 * @since 1.0.0
 */
class WPForms_Template_Blank extends WPForms_Template {

	/**
	 * Template slug.
	 *
	 * @since 1.9.2
	 */
	const SLUG = 'blank';

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		$this->priority    = 1;
		$this->name        = esc_html__( 'Blank Form', 'wpforms-lite' );
		$this->slug        = 'blank';
		$this->source      = 'wpforms-core';
		$this->categories  = 'all';
		$this->description = esc_html__( 'The blank form allows you to create any type of form using our drag & drop builder.', 'wpforms-lite' );
		$this->includes    = '';
		$this->icon        = '';
		$this->modal       = '';
		$this->core        = true;
		$this->data        = self::get_data();
	}

	/**
	 * Get template data.
	 *
	 * @since 1.9.2
	 *
	 * @return array
	 */
	public static function get_data(): array {

		return [
			'field_id' => '1',
			'fields'   => [],
			'settings' => [
				'antispam_v3'                 => '1',
				'ajax_submit'                 => '1',
				'confirmation_message_scroll' => '1',
				'submit_text_processing'      => esc_html__( 'Sending...', 'wpforms-lite' ),
				'store_spam_entries'          => '1',
				'anti_spam'                   => [
					'time_limit' => [
						'enable'   => '1',
						'duration' => '2',
					],
				],
			],
			'meta'     => [
				'template' => self::SLUG,
			],
		];
	}
}

new WPForms_Template_Blank();
