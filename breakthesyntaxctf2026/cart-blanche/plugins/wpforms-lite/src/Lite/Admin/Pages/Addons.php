<?php

namespace WPForms\Lite\Admin\Pages;

/**
 * Addons page for Lite.
 *
 * @since 1.6.7
 */
class Addons {

	/**
	 * Page slug.
	 *
	 * @since 1.6.7
	 *
	 * @type string
	 */
	const SLUG = 'addons';

	/**
	 * Determine if current class is allowed to load.
	 *
	 * @since 1.6.7
	 *
	 * @return bool
	 */
	public function allow_load() {

		return wpforms_is_admin_page( self::SLUG );
	}

	/**
	 * Init.
	 *
	 * @since 1.6.7
	 */
	public function init() {

		if ( ! $this->allow_load() ) {
			return;
		}

		// Define hooks.
		$this->hooks();
	}

	/**
	 * Hooks.
	 *
	 * @since 1.6.7
	 */
	public function hooks() {

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueues' ] );
		add_action( 'admin_notices', [ $this, 'notices' ] );
		add_action( 'wpforms_admin_page', [ $this, 'output' ] );
	}

	/**
	 * Add appropriate scripts to the Addons page.
	 *
	 * @since 1.6.7
	 */
	public function enqueues() {

		// JavaScript.
		wp_enqueue_script(
			'listjs',
			WPFORMS_PLUGIN_URL . 'assets/lib/list.min.js',
			[ 'jquery' ],
			'1.5.0',
			false
		);
	}

	/**
	 * Notices.
	 *
	 * @since 1.6.7.1
	 */
	public function notices() {

		$notice = sprintf(
			'<p class="notice-title"><strong>%1$s</strong></p>
             <p>%2$s</p>
             <p class="notice-buttons">
                 <a href="%3$s" class="wpforms-btn wpforms-btn-orange wpforms-btn-md" target="_blank" rel="noopener noreferrer">
                     %4$s
                 </a>
             </p>',
			esc_html__( 'Upgrade to Unlock WPForms Addons', 'wpforms-lite' ),
			esc_html__( 'Access powerful marketing and payment integrations, advanced form fields, and more when you purchase our Plus, Pro, or Elite plans.', 'wpforms-lite' ),
			esc_url( wpforms_admin_upgrade_link( 'addons', 'All Addons' ) ),
			esc_html__( 'Upgrade Now', 'wpforms-lite' )
		);

		\WPForms\Admin\Notice::info(
			$notice,
			[ 'autop' => false ]
		);
	}

	/**
	 * Render the Addons page.
	 *
	 * @since 1.6.7
	 */
	public function output() {

		$addons = wpforms()->obj( 'addons' )->get_all();

		if ( empty( $addons ) ) {
			return;
		}

		// WPForms 1.8.7 core includes Custom Captcha.
		// The Custom Captcha addon will only work on WPForms 1.8.6 and earlier versions.
		unset( $addons['wpforms-captcha'] );

		echo wpforms_render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'admin/addons',
			[
				'upgrade_link_base' => wpforms_admin_upgrade_link( 'addons' ),
				'addons'            => $addons,
			],
			true
		);
	}
}
