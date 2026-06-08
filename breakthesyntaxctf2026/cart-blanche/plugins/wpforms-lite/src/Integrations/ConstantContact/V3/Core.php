<?php

// phpcs:ignore Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpUndefinedConstantInspection */

namespace WPForms\Integrations\ConstantContact\V3;

use WPForms\Integrations\ConstantContact\V3\Settings\FormBuilder;
use WPForms\Integrations\ConstantContact\V3\Settings\PageIntegrations;
use WPForms\Providers\Provider\Core as ProviderCore;

/**
 * Class Constant Contact V3 Core.
 *
 * @since 1.9.3
 */
class Core extends ProviderCore {

	/**
	 * Priority for a provider, that will affect loading/placement order.
	 *
	 * @since 1.9.3
	 */
	const PRIORITY = 14;

	/**
	 * Unique provider slug.
	 *
	 * @since 1.9.3
	 *
	 * @var string
	 */
	const SLUG = 'constant-contact-v3';

	/**
	 * Core constructor.
	 *
	 * @since 1.9.3
	 */
	public function __construct() {

		parent::__construct(
			[
				'slug' => self::SLUG,
				'name' => $this->get_name(),
				'icon' => WPFORMS_PLUGIN_URL . 'assets/images/icon-provider-constant-contact.png',
			]
		);
	}

	/**
	 * Provide an instance of the object, that should process the submitted entry.
	 * It will use data from an already saved entry to pass it further to a Provider.
	 *
	 * @since 1.9.3
	 *
	 * @return Process
	 */
	public function get_process(): Process {

		static $process;

		if ( ! $process ) {
			$process = new Process( $this );
		}

		return $process;
	}

	/**
	 * Provide an instance of the object, that should display provider settings
	 * on Settings > Integrations page in the admin area.
	 *
	 * @since 1.9.3
	 *
	 * @return PageIntegrations
	 */
	public function get_page_integrations(): PageIntegrations {

		static $integration;

		if ( ! $integration ) {
			$integration = new PageIntegrations( static::get_instance() );
		}

		return $integration;
	}

	/**
	 * Provide an instance of the object, that should display provider settings in the Form Builder.
	 *
	 * @since 1.9.3
	 *
	 * @return FormBuilder
	 */
	public function get_form_builder(): FormBuilder {

		static $builder;

		if ( ! $builder ) {
			$builder = new FormBuilder( $this );
		}

		return $builder;
	}

	/**
	 * Provider account name.
	 *
	 * Adds "(V3)" to the name if WPFORMS_DEBUG is defined.
	 *
	 * @since 1.9.3
	 *
	 * @return string
	 */
	private function get_name(): string {

		$base = 'Constant Contact';

		if ( ! defined( 'WPFORMS_DEBUG' ) || ! WPFORMS_DEBUG ) {
			return $base;
		}

		return $base . ' (V3)';
	}
}
