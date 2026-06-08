<?php

namespace WPForms\Admin\Tools\Views;

use WPForms\Integrations\WPCode\WPCode;

/**
 * Class WPCode view.
 *
 * @since 1.8.5
 */
class CodeSnippets extends View {

	/**
	 * View slug.
	 *
	 * @since 1.8.5
	 *
	 * @var string
	 */
	protected $slug = 'wpcode';

	/**
	 * WPCode action required.
	 *
	 * @since 1.8.5
	 *
	 * @var string
	 */
	private $action;

	/**
	 * WPCode snippets.
	 *
	 * @since 1.8.5
	 *
	 * @var array
	 */
	private $snippets;

	/**
	 * WPCode plugin slug or download URL.
	 *
	 * @since 1.8.5
	 *
	 * @var string
	 */
	private $plugin;

	/**
	 * Whether WPCode action is required.
	 *
	 * @since 1.8.5
	 *
	 * @var bool
	 */
	private $action_required;

	/**
	 * Init view.
	 *
	 * @since 1.8.5
	 */
	public function init() {

		$wpcode = new WPCode();

		$this->snippets        = $wpcode->load_wpforms_snippets();
		$plugin_slug           = $wpcode->is_pro_installed() ? $wpcode->pro_plugin_slug : $wpcode->lite_plugin_slug;
		$update_required       = $wpcode->is_plugin_installed() && version_compare( $wpcode->plugin_version(), '2.0.10', '<' );
		$installed_action      = $update_required ? 'update' : 'activate';
		$this->action_required = $update_required || ! $wpcode->is_plugin_installed() || ! $wpcode->is_plugin_active();
		$this->action          = $wpcode->is_plugin_installed() ? $installed_action : 'install';
		$this->plugin          = $this->action === 'activate' ? $plugin_slug : $wpcode->lite_download_url;

		$this->hooks();
	}

	/**
	 * Add hooks.
	 *
	 * @since 1.8.5
	 *
	 * @return void
	 */
	private function hooks() {

		if ( $this->action !== 'update' ) {
			return;
		}

		add_filter(
			'upgrader_package_options',
			static function ( $options ) {
				$options['clear_destination'] = true;

				return $options;
			}
		);
	}

	/**
	 * Get view label.
	 *
	 * @since 1.8.5
	 *
	 * @return string
	 * @noinspection PhpMissingReturnTypeInspection
	 * @noinspection ReturnTypeCanBeDeclaredInspection
	 */
	public function get_label() {

		return esc_html__( 'Code Snippets', 'wpforms-lite' );
	}

	/**
	 * Checking user capability to view.
	 *
	 * @since 1.8.5
	 *
	 * @return bool
	 * @noinspection  PhpMissingReturnTypeInspection
	 * @noinspection  ReturnTypeCanBeDeclaredInspection
	 */
	public function check_capability() {

		return wpforms_current_user_can();
	}

	/**
	 * Display view content.
	 *
	 * @since 1.8.5
	 *
	 * @noinspection  PhpMissingReturnTypeInspection
	 * @noinspection  ReturnTypeCanBeDeclaredInspection
	 */
	public function display() {

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wpforms_render(
			'integrations/wpcode/code-snippets',
			[
				'snippets'        => $this->snippets,
				'action_required' => $this->action_required,
				'action'          => $this->action,
				'plugin'          => $this->plugin,
			],
			true
		);
	}
}
