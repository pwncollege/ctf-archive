<?php

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpIllegalPsrClassPathInspection */
/** @noinspection AutoloadingIssuesInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

namespace WPForms\Integrations\AI;

use WPForms\Integrations\IntegrationInterface;
use WPForms\Integrations\AI\Admin\Ajax\Choices as ChoicesAjax;
use WPForms\Integrations\AI\Admin\Ajax\Forms as FormsAjax;
use WPForms\Integrations\AI\Admin\Builder\Enqueues;
use WPForms\Integrations\AI\Admin\Builder\FieldOption;
use WPForms\Integrations\AI\Admin\Builder\Forms as FormsEnqueues;
use WPForms\Integrations\AI\Admin\Pages\Templates as TemplatesPage;

/**
 * Integration of the AI features.
 *
 * @since 1.9.1
 */
class AI implements IntegrationInterface {

	/**
	 * Determine whether the integration is allowed to load.
	 *
	 * @since 1.9.1
	 *
	 * @return bool
	 */
	public function allow_load(): bool {

		// Always load the Settings class to register the toggle.
		if ( wpforms_is_admin_page( 'settings', 'misc' ) ) {
			( new Admin\Settings() )->init();
		}

		return ! Helpers::is_disabled();
	}

	/**
	 * Load the integration classes.
	 *
	 * @since 1.9.1
	 */
	public function load() {

		if ( wpforms_is_admin_page( 'builder' ) ) {
			( new Enqueues() )->init();
			( new FieldOption() )->init();
			( new FormsEnqueues() )->init();
		}

		if ( wpforms_is_admin_page( 'templates' ) ) {
			( new TemplatesPage() )->init();
		}

		if ( wpforms_is_admin_ajax() ) {
			$this->load_ajax_classes();
		}
	}

	/**
	 * Load AJAX classes.
	 *
	 * @since 1.9.1
	 */
	protected function load_ajax_classes() {

		( new FieldOption() )->init();
		( new ChoicesAjax() )->init();
		( new FormsAjax() )->init();
	}
}
