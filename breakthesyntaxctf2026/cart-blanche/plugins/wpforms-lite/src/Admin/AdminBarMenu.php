<?php

namespace WPForms\Admin;

use WP_Admin_Bar;

/**
 * WPForms admin bar menu.
 *
 * @since 1.6.0
 */
class AdminBarMenu {

	/**
	 * Initialize class.
	 *
	 * @since 1.6.0
	 */
	public function init() {

		if ( ! $this->has_access() ) {
			return;
		}

		$this->hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.6.0
	 */
	public function hooks() {

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_css' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_css' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_js' ] );

		add_action( 'admin_bar_menu', [ $this, 'register' ], 999 );
		add_action( 'wpforms_wp_footer_end', [ $this, 'menu_forms_data_html' ] );
	}

	/**
	 * Determine whether the current user has access to see the admin bar menu.
	 *
	 * @since 1.6.0
	 *
	 * @return bool
	 */
	public function has_access(): bool {

		$access = false;

		if (
			is_admin_bar_showing() &&
			wpforms_current_user_can() &&
			! wpforms_setting( 'hide-admin-bar', false )
		) {
			$access = true;
		}

		/**
		 * Filters whether the current user has access to see the admin bar menu.
		 *
		 * @since 1.6.0
		 *
		 * @param bool $access Whether the current user has access to see the admin bar menu.
		 */
		return (bool) apply_filters( 'wpforms_admin_adminbarmenu_has_access', $access ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
	}

	/**
	 * Determine whether new notifications are available.
	 *
	 * @since 1.6.0
	 *
	 * @return bool
	 */
	public function has_notifications() {

		return wpforms()->obj( 'notifications' )->get_count();
	}

	/**
	 * Enqueue CSS styles.
	 *
	 * @since 1.6.0
	 */
	public function enqueue_css() {

		$min = wpforms_get_min_suffix();

		wp_enqueue_style(
			'wpforms-admin-bar',
			WPFORMS_PLUGIN_URL . "assets/css/admin-bar{$min}.css",
			[],
			WPFORMS_VERSION
		);

		// Apply WordPress pre/post 5.7 accent color, only when admin bar is displayed on the frontend or we're
		// inside the Form Builder - it does not load some WP core admin styles, including themes.
		if ( wpforms_is_admin_page( 'builder' ) || ! is_admin() ) {
			wp_add_inline_style(
				'wpforms-admin-bar',
				sprintf(
					'#wpadminbar .wpforms-menu-notification-counter, #wpadminbar .wpforms-menu-notification-indicator {
						background-color: %s !important;
						color: #ffffff !important;
					}',
					version_compare( get_bloginfo( 'version' ), '5.7', '<' ) ? '#ca4a1f' : '#d63638'
				)
			);
		}
	}

	/**
	 * Enqueue JavaScript files.
	 *
	 * @since 1.6.5
	 */
	public function enqueue_js() {

		wp_add_inline_script(
			'admin-bar',
			"( function() {
				function wpforms_admin_bar_menu_init() {
					var template = document.getElementById( 'tmpl-wpforms-admin-menubar-data' ),
						notifications = document.getElementById( 'wp-admin-bar-wpforms-notifications' );

					if ( ! template ) {
						return;
					}

					if ( ! notifications ) {
						var menu = document.getElementById( 'wp-admin-bar-wpforms-menu-default' );

						if ( ! menu ) {
							return;
						}

						menu.insertAdjacentHTML( 'afterBegin', template.innerHTML );
					} else {
						notifications.insertAdjacentHTML( 'afterend', template.innerHTML );
					}
				};
				document.addEventListener( 'DOMContentLoaded', wpforms_admin_bar_menu_init );
			}() );",
			'before'
		);
	}

	/**
	 * Register and render admin bar menu items.
	 *
	 * @since 1.6.0
	 *
	 * @param WP_Admin_Bar $wp_admin_bar WordPress Admin Bar object.
	 */
	public function register( WP_Admin_Bar $wp_admin_bar ) {

		$items = (array) apply_filters(
			'wpforms_admin_adminbarmenu_register',
			[
				'main_menu',
				'notification_menu',
				'all_forms_menu',
				'add_new_menu',
				'all_payments_menu',
				'settings_menu',
				'tools_menu',
				'community_menu',
				'support_menu',
			],
			$wp_admin_bar
		);

		foreach ( $items as $item ) {

			$this->{ $item }( $wp_admin_bar );

			do_action( "wpforms_admin_adminbarmenu_register_{$item}_after", $wp_admin_bar );
		}

		$this->register_settings_submenu( $wp_admin_bar );
		$this->register_tools_submenu( $wp_admin_bar );
	}

	/**
	 * Register Settings submenu.
	 *
	 * @since 1.9.2
	 *
	 * @param WP_Admin_Bar $wp_admin_bar WordPress Admin Bar object.
	 */
	private function register_settings_submenu( WP_Admin_Bar $wp_admin_bar ) {

		/**
		 * Filters the Settings submenu items.
		 *
		 * @since 1.9.2
		 *
		 * @param array        $items        Array of submenu items.
		 * @param WP_Admin_Bar $wp_admin_bar WordPress Admin Bar object.
		 */
		$items = (array) apply_filters(
			'wpforms_admin_bar_menu_register_settings_submenu',
			[
				'wpforms-general-settings'      => [
					'title' => __( 'General', 'wpforms-lite' ),
					'path'  => 'admin.php?page=wpforms-settings&view=general',
				],
				'wpforms-email-settings'        => [
					'title' => __( 'Email', 'wpforms-lite' ),
					'path'  => 'admin.php?page=wpforms-settings&view=email',
				],
				'wpforms-captcha-settings'      => [
					'title' => __( 'CAPTCHA', 'wpforms-lite' ),
					'path'  => 'admin.php?page=wpforms-settings&view=captcha',
				],
				'wpforms-validation-settings'   => [
					'title' => __( 'Validation', 'wpforms-lite' ),
					'path'  => 'admin.php?page=wpforms-settings&view=validation',
				],
				'wpforms-payments-settings'     => [
					'title' => __( 'Payments', 'wpforms-lite' ),
					'path'  => 'admin.php?page=wpforms-settings&view=payments',
				],
				'wpforms-integrations-settings' => [
					'title' => __( 'Integrations', 'wpforms-lite' ),
					'path'  => 'admin.php?page=wpforms-settings&view=integrations',
				],
				'wpforms-geolocation-settings'  => [
					'title' => __( 'Geolocation', 'wpforms-lite' ),
					'path'  => 'admin.php?page=wpforms-settings&view=geolocation',
				],
				'wpforms-access-settings'       => [
					'title' => __( 'Access Control', 'wpforms-lite' ),
					'path'  => 'admin.php?page=wpforms-settings&view=access',
				],
				'wpforms-misc-settings'         => [
					'title' => __( 'Misc', 'wpforms-lite' ),
					'path'  => 'admin.php?page=wpforms-settings&view=misc',
				],
			],
			$wp_admin_bar
		);

		foreach ( $items as $item_id => $args ) {

			$wp_admin_bar->add_menu(
				[
					'parent' => 'wpforms-settings',
					'id'     => sanitize_key( $item_id ),
					'title'  => esc_html( $args['title'] ),
					'href'   => admin_url( $args['path'] ),
				]
			);

			/**
			 * Fires after the Settings submenu item is registered.
			 *
			 * @since 1.9.2
			 *
			 * @param WP_Admin_Bar $wp_admin_bar WordPress Admin Bar object.
			 */
			do_action( "wpforms_admin_bar_menu_register_settings_submenu_{$item_id}_after", $wp_admin_bar );
		}
	}

	/**
	 * Register Tools submenu.
	 *
	 * @since 1.9.3
	 *
	 * @param WP_Admin_Bar $wp_admin_bar WordPress Admin Bar object.
	 */
	private function register_tools_submenu( WP_Admin_Bar $wp_admin_bar ) {

		/**
		 * Filters the Tools submenu items.
		 *
		 * @since 1.9.3
		 *
		 * @param array        $items        Array of submenu items.
		 * @param WP_Admin_Bar $wp_admin_bar WordPress Admin Bar object.
		 *
		 * @return array
		 */
		$items = (array) apply_filters(
			'wpforms_admin_bar_menu_register_tools_submenu',
			[
				'wpforms-tools-import'           => [
					'title' => esc_html__( 'Import', 'wpforms-lite' ),
					'path'  => 'admin.php?page=wpforms-tools&view=import',
				],
				'wpforms-tools-export'           => [
					'title' => esc_html__( 'Export', 'wpforms-lite' ),
					'path'  => 'admin.php?page=wpforms-tools&view=export',
				],
				'wpforms-tools-entry-automation' => [
					'title' => esc_html__( 'Entry Automation', 'wpforms-lite' ),
					'path'  => 'admin.php?page=wpforms-tools&view=entry-automation',
				],
				'wpforms-tools-system'           => [
					'title' => esc_html__( 'System Info', 'wpforms-lite' ),
					'path'  => 'admin.php?page=wpforms-tools&view=system',
				],
				'wpforms-tools-action-scheduler' => [
					'title' => esc_html__( 'Scheduled Actions', 'wpforms-lite' ),
					'path'  => 'admin.php?page=wpforms-tools&view=action-scheduler&s=wpforms',
				],
				'wpforms-tools-logs'             => [
					'title' => esc_html__( 'Logs', 'wpforms-lite' ),
					'path'  => 'admin.php?page=wpforms-tools&view=logs',
				],
				'wpforms-tools-wpcode'           => [
					'title' => esc_html__( 'Code Snippets', 'wpforms-lite' ),
					'path'  => 'admin.php?page=wpforms-tools&view=wpcode',
				],
			],
			$wp_admin_bar
		);

		foreach ( $items as $item_id => $args ) {
			$wp_admin_bar->add_menu(
				[
					'parent' => 'wpforms-tools',
					'id'     => sanitize_key( $item_id ),
					'title'  => esc_html( $args['title'] ),
					'href'   => admin_url( $args['path'] ),
				]
			);

			/**
			 * Fires after the Tools submenu item is registered.
			 *
			 * @since 1.9.2
			 *
			 * @param WP_Admin_Bar $wp_admin_bar WordPress Admin Bar object.
			 */
			do_action( "wpforms_admin_bar_menu_register_tools_submenu_{$item_id}_after", $wp_admin_bar );
		}

		$this->register_action_scheduler_submenu( $wp_admin_bar );
	}

	/**
	 * Register Action Scheduler submenu.
	 *
	 * @since 1.9.3
	 *
	 * @param WP_Admin_Bar $wp_admin_bar WordPress Admin Bar object.
	 */
	private function register_action_scheduler_submenu( WP_Admin_Bar $wp_admin_bar ) {

		/**
		 * Filters the Action Scheduler submenu items.
		 *
		 * @since 1.9.3
		 *
		 * @param array        $items        Array of submenu items.
		 * @param WP_Admin_Bar $wp_admin_bar WordPress Admin Bar object.
		 *
		 * @return array
		 */
		$items = apply_filters(
			'wpforms_admin_bar_menu_register_action_scheduler_submenu',
			[
				'wpforms-tools-action-scheduler-all'      => [
					'title' => esc_html__( 'View All', 'wpforms-lite' ),
					'path'  => 'admin.php?page=wpforms-tools&view=action-scheduler&s=wpforms&orderby=hook&order=desc',
				],
				'wpforms-tools-action-scheduler-complete' => [
					'title' => esc_html__( 'Completed Actions', 'wpforms-lite' ),
					'path'  => 'admin.php?page=wpforms-tools&view=action-scheduler&s=wpforms&status=complete&orderby=hook&order=desc',
				],
				'wpforms-tools-action-scheduler-failed'   => [
					'title' => esc_html__( 'Failed Actions', 'wpforms-lite' ),
					'path'  => 'admin.php?page=wpforms-tools&view=action-scheduler&s=wpforms&status=failed&orderby=hook&order=desc',
				],
				'wpforms-tools-action-scheduler-pending'  => [
					'title' => esc_html__( 'Pending Actions', 'wpforms-lite' ),
					'path'  => 'admin.php?page=wpforms-tools&view=action-scheduler&s=wpforms&status=pending&orderby=hook&order=desc',
				],
				'wpforms-tools-action-scheduler-past-due' => [
					'title' => esc_html__( 'Past Due Actions', 'wpforms-lite' ),
					'path'  => 'admin.php?page=wpforms-tools&view=action-scheduler&s=wpforms&status=past-due&orderby=hook&order=desc',
				],
			],
			$wp_admin_bar
		);

		foreach ( $items as $item_id => $args ) {
			$wp_admin_bar->add_menu(
				[
					'parent' => 'wpforms-tools-action-scheduler',
					'id'     => sanitize_key( $item_id ),
					'title'  => esc_html( $args['title'] ),
					'href'   => admin_url( $args['path'] ),
				]
			);

			/**
			 * Fires after the Action Scheduler submenu item is registered.
			 *
			 * @since 1.9.3
			 *
			 * @param WP_Admin_Bar $wp_admin_bar WordPress Admin Bar object.
			 */
			do_action( "wpforms_admin_bar_menu_register_action_scheduler_submenu_{$item_id}_after", $wp_admin_bar );
		}
	}

	/**
	 * Render primary top-level admin bar menu item.
	 *
	 * @since 1.6.0
	 *
	 * @param WP_Admin_Bar $wp_admin_bar WordPress Admin Bar object.
	 */
	public function main_menu( WP_Admin_Bar $wp_admin_bar ) {

		$indicator     = '';
		$notifications = $this->has_notifications();

		if ( $notifications ) {
			$count     = $notifications < 10 ? $notifications : '!';
			$indicator = ' <div class="wp-core-ui wp-ui-notification wpforms-menu-notification-counter">' . $count . '</div>';
		}

		$wp_admin_bar->add_menu(
			[
				'id'    => 'wpforms-menu',
				'title' => 'WPForms' . $indicator,
				'href'  => admin_url( 'admin.php?page=wpforms-overview' ),
			]
		);
	}

	/**
	 * Render Notifications admin bar menu item.
	 *
	 * @since 1.6.0
	 *
	 * @param WP_Admin_Bar $wp_admin_bar WordPress Admin Bar object.
	 */
	public function notification_menu( WP_Admin_Bar $wp_admin_bar ) {

		if ( ! $this->has_notifications() ) {
			return;
		}

		$wp_admin_bar->add_menu(
			[
				'parent' => 'wpforms-menu',
				'id'     => 'wpforms-notifications',
				'title'  => esc_html__( 'Notifications', 'wpforms-lite' ) . ' <div class="wp-core-ui wp-ui-notification wpforms-menu-notification-indicator"></div>',
				'href'   => admin_url( 'admin.php?page=wpforms-overview' ),
			]
		);
	}

	/**
	 * Render All Forms admin bar menu item.
	 *
	 * @since 1.6.0
	 *
	 * @param WP_Admin_Bar $wp_admin_bar WordPress Admin Bar object.
	 */
	public function all_forms_menu( WP_Admin_Bar $wp_admin_bar ) {

		$wp_admin_bar->add_menu(
			[
				'parent' => 'wpforms-menu',
				'id'     => 'wpforms-forms',
				'title'  => esc_html__( 'All Forms', 'wpforms-lite' ),
				'href'   => admin_url( 'admin.php?page=wpforms-overview' ),
			]
		);
	}

	/**
	 * Render All Payments admin bar menu item.
	 *
	 * @since 1.8.4
	 *
	 * @param WP_Admin_Bar $wp_admin_bar WordPress Admin Bar object.
	 */
	public function all_payments_menu( WP_Admin_Bar $wp_admin_bar ) {

		$wp_admin_bar->add_menu(
			[
				'parent' => 'wpforms-menu',
				'id'     => 'wpforms-payments',
				'title'  => esc_html__( 'Payments', 'wpforms-lite' ),
				'href'   => add_query_arg(
					[
						'page' => 'wpforms-payments',
					],
					admin_url( 'admin.php' )
				),
			]
		);
	}

	/**
	 * Render Add New admin bar menu item.
	 *
	 * @since 1.6.0
	 *
	 * @param WP_Admin_Bar $wp_admin_bar WordPress Admin Bar object.
	 */
	public function add_new_menu( WP_Admin_Bar $wp_admin_bar ) {

		$wp_admin_bar->add_menu(
			[
				'parent' => 'wpforms-menu',
				'id'     => 'wpforms-add-new',
				'title'  => esc_html__( 'Add New Form', 'wpforms-lite' ),
				'href'   => admin_url( 'admin.php?page=wpforms-builder' ),
			]
		);
	}

	/**
	 * Render Settings admin bar menu item.
	 *
	 * @since 1.9.2
	 *
	 * @param WP_Admin_Bar $wp_admin_bar WordPress Admin Bar object.
	 */
	public function settings_menu( WP_Admin_Bar $wp_admin_bar ) {

		$wp_admin_bar->add_menu(
			[
				'parent' => 'wpforms-menu',
				'id'     => 'wpforms-settings',
				'title'  => esc_html__( 'Settings', 'wpforms-lite' ),
				'href'   => admin_url( 'admin.php?page=wpforms-settings' ),
			]
		);
	}

	/**
	 * Add Tools menu to the admin bar.
	 *
	 * @since 1.9.3
	 *
	 * @param WP_Admin_Bar $wp_admin_bar The admin bar object.
	 */
	public function tools_menu( WP_Admin_Bar $wp_admin_bar ) {

		$wp_admin_bar->add_menu(
			[
				'parent' => 'wpforms-menu',
				'id'     => 'wpforms-tools',
				'title'  => esc_html__( 'Tools', 'wpforms-lite' ),
				'href'   => admin_url( 'admin.php?page=wpforms-tools' ),
			]
		);
	}

	/**
	 * Render Community admin bar menu item.
	 *
	 * @since 1.6.0
	 *
	 * @param WP_Admin_Bar $wp_admin_bar WordPress Admin Bar object.
	 */
	public function community_menu( WP_Admin_Bar $wp_admin_bar ) {

		$wp_admin_bar->add_menu(
			[
				'parent' => 'wpforms-menu',
				'id'     => 'wpforms-community',
				'title'  => esc_html__( 'Community', 'wpforms-lite' ),
				'href'   => 'https://www.facebook.com/groups/wpformsvip/',
				'meta'   => [
					'target' => '_blank',
					'rel'    => 'noopener noreferrer',
				],
			]
		);
	}

	/**
	 * Render Support admin bar menu item.
	 *
	 * @since 1.6.0
	 * @since 1.7.4 Update the `Support` item title to `Help Docs`.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar WordPress Admin Bar object.
	 */
	public function support_menu( WP_Admin_Bar $wp_admin_bar ) {

		$href = add_query_arg(
			[
				'utm_campaign' => wpforms()->is_pro() ? 'plugin' : 'liteplugin',
				'utm_medium'   => 'admin-bar',
				'utm_source'   => 'WordPress',
				'utm_content'  => 'Documentation',
			],
			'https://wpforms.com/docs/'
		);

		$wp_admin_bar->add_menu(
			[
				'parent' => 'wpforms-menu',
				'id'     => 'wpforms-help-docs',
				'title'  => esc_html__( 'Help Docs', 'wpforms-lite' ),
				'href'   => $href,
				'meta'   => [
					'target' => '_blank',
					'rel'    => 'noopener noreferrer',
				],
			]
		);
	}

	/**
	 * Get form data for JS to modify the admin bar menu.
	 *
	 * @since 1.6.5
	 * @since 1.8.4 Added the View Payments link.
	 *
	 * @param array $forms Forms array.
	 *
	 * @return array
	 */
	protected function get_forms_data( $forms ) {

		$data = [
			'has_notifications' => $this->has_notifications(),
			'edit_text'         => esc_html__( 'Edit Form', 'wpforms-lite' ),
			'entry_text'        => esc_html__( 'View Entries', 'wpforms-lite' ),
			'payment_text'      => esc_html__( 'View Payments', 'wpforms-lite' ),
			'survey_text'       => esc_html__( 'Survey Results', 'wpforms-lite' ),
			'forms'             => [],
		];

		$admin_url = admin_url( 'admin.php' );

		foreach ( $forms as $form ) {
			$form_id = absint( $form['id'] );

			if ( empty( $form_id ) ) {
				continue;
			}

			/* translators: %d - form ID. */
			$form_title = sprintf( esc_html__( 'Form ID: %d', 'wpforms-lite' ), $form_id );

			if ( ! empty( $form['settings']['form_title'] ) ) {
				$form_title = wp_html_excerpt(
					sanitize_text_field( $form['settings']['form_title'] ),
					99,
					'&hellip;'
				);
			}

			$has_payments = wpforms()->obj( 'payment' )->get_by( 'form_id', $form_id );

			$data['forms'][] = apply_filters(
				'wpforms_admin_adminbarmenu_get_form_data',
				[
					'form_id'      => $form_id,
					'title'        => $form_title,
					'edit_url'     => add_query_arg(
						[
							'page'    => 'wpforms-builder',
							'view'    => 'fields',
							'form_id' => $form_id,
						],
						$admin_url
					),
					'payments_url' => $has_payments ? add_query_arg(
						[
							'page'    => 'wpforms-payments',
							'form_id' => $form_id,
						],
						$admin_url
					) : '',
				]
			);
		}

		return $data;
	}

	/**
	 * Add form(s) data to the page.
	 *
	 * @since 1.6.5
	 *
	 * @param array $forms Forms array.
	 */
	public function menu_forms_data_html( $forms ) {

		if ( empty( $forms ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wpforms_render(
			'admin-bar-menu',
			[
				'forms_data' => $this->get_forms_data( $forms ),
			],
			true
		);
	}
}
