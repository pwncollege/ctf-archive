<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:ignore Generic.Commenting.DocComment.MissingShort
/** @noinspection AutoloadingIssuesInspection */

/**
 * Register menu elements and do other global tasks.
 *
 * @since 1.0.0
 */
class WPForms_Admin_Menu {

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->hooks();
	}

	/**
	 * Hooks.
	 *
	 * @since 1.9.7.3
	 *
	 * @return void
	 */
	private function hooks(): void {

		// Let's make some menus.
		add_action( 'admin_menu', [ $this, 'register_menus' ], 9 );
		add_action( 'admin_head', [ $this, 'hide_wpforms_submenu_items' ] );
		add_action( 'admin_head', [ $this, 'adjust_pro_menu_item' ] );
		add_action( 'admin_head', [ $this, 'admin_menu_styles' ], 11 );

		// Plugins page settings link.
		add_filter( 'plugin_action_links_' . plugin_basename( WPFORMS_PLUGIN_DIR . 'wpforms.php' ), [ $this, 'settings_link' ], 10, 4 );

		add_action( 'activated_plugin', [ $this, 'activated_rotation_plugin' ], 10, 2 );
	}

	/**
	 * Register our menus.
	 *
	 * @since 1.0.0
	 */
	public function register_menus(): void {

		$manage_cap = wpforms_get_capability_manage_options();
		$access     = wpforms()->obj( 'access' );

		if ( ! $access || ! method_exists( $access, 'get_menu_cap' ) ) {
			return;
		}

		// Default Forms top level menu item.
		add_menu_page(
			esc_html__( 'WPForms', 'wpforms-lite' ),
			esc_html__( 'WPForms', 'wpforms-lite' ),
			$access->get_menu_cap( 'view_forms' ),
			'wpforms-overview',
			[ $this, 'admin_page' ],
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			'data:image/svg+xml;base64,' . base64_encode( '<svg width="1792" height="1792" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path fill="#9ea3a8" d="M643 911v128h-252v-128h252zm0-255v127h-252v-127h252zm758 511v128h-341v-128h341zm0-256v128h-672v-128h672zm0-255v127h-672v-127h672zm135 860v-1240q0-8-6-14t-14-6h-32l-378 256-210-171-210 171-378-256h-32q-8 0-14 6t-6 14v1240q0 8 6 14t14 6h1240q8 0 14-6t6-14zm-855-1110l185-150h-406zm430 0l221-150h-406zm553-130v1240q0 62-43 105t-105 43h-1240q-62 0-105-43t-43-105v-1240q0-62 43-105t105-43h1240q62 0 105 43t43 105z"/></svg>' ),
			/**
			 * Filters WPForms menu position.
			 *
			 * @since 1.6.0.2
			 *
			 * @param string|int|float $position Menu position.
			 */
			apply_filters( 'wpforms_menu_position', '58.9' ) // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
		);

		// All Forms sub menu item.
		add_submenu_page(
			'wpforms-overview',
			esc_html__( 'WPForms', 'wpforms-lite' ),
			esc_html__( 'All Forms', 'wpforms-lite' ),
			$access->get_menu_cap( 'view_forms' ),
			'wpforms-overview',
			[ $this, 'admin_page' ]
		);

		// Add New submenu item.
		add_submenu_page(
			'wpforms-overview',
			esc_html__( 'WPForms Builder', 'wpforms-lite' ),
			esc_html__( 'Add New Form', 'wpforms-lite' ),
			$access->get_menu_cap( [ 'create_forms', 'edit_forms' ] ),
			'wpforms-builder',
			[ $this, 'admin_page' ]
		);

		// Entries sub menu item.
		add_submenu_page(
			'wpforms-overview',
			esc_html__( 'Form Entries', 'wpforms-lite' ),
			esc_html__( 'Entries', 'wpforms-lite' ),
			$access->get_menu_cap( 'view_entries' ),
			'wpforms-entries',
			[ $this, 'admin_page' ]
		);

		// Payments sub menu item.
		add_submenu_page(
			'wpforms-overview',
			esc_html__( 'Payments', 'wpforms-lite' ),
			esc_html__( 'Payments', 'wpforms-lite' ) . $this->get_new_badge_html(),
			$manage_cap,
			WPForms\Admin\Payments\Payments::SLUG,
			[ $this, 'admin_page' ]
		);

		do_action_deprecated( // phpcs:ignore WPForms.Comments.PHPDocHooks.RequiredHookDocumentation
			'wpform_admin_menu',
			[ $this ],
			'1.5.5 of the WPForms plugin',
			'wpforms_admin_menu'
		);

		/**
		 * Fires after constructing the WPForms admin menu.
		 *
		 * @since 1.5.4.2
		 *
		 * @param WPForms_Admin_Menu $instance WPForms Admin Menu instance.
		 */
		do_action( 'wpforms_admin_menu', $this );

		// Templates sub menu item.
		add_submenu_page(
			'wpforms-overview',
			esc_html__( 'WPForms Templates', 'wpforms-lite' ),
			esc_html__( 'Form Templates', 'wpforms-lite' ),
			$access->get_menu_cap( 'edit_forms' ),
			'wpforms-templates',
			[ $this, 'admin_page' ]
		);

		// Settings submenu item.
		add_submenu_page(
			'wpforms-overview',
			esc_html__( 'WPForms Settings', 'wpforms-lite' ),
			esc_html__( 'Settings', 'wpforms-lite' ),
			$manage_cap,
			'wpforms-settings',
			[ $this, 'admin_page' ]
		);

		// Tools sub menu item.
		add_submenu_page(
			'wpforms-overview',
			esc_html__( 'WPForms Tools', 'wpforms-lite' ),
			esc_html__( 'Tools', 'wpforms-lite' ),
			$access->get_menu_cap( [ 'create_forms', 'view_forms', 'view_entries' ] ),
			'wpforms-tools',
			[ $this, 'admin_page' ]
		);

		// Hidden placeholder paged used for misc content.
		add_submenu_page(
			'wpforms-settings',
			esc_html__( 'WPForms', 'wpforms-lite' ),
			esc_html__( 'Info', 'wpforms-lite' ),
			$access->get_menu_cap( 'any' ),
			'wpforms-page',
			[ $this, 'admin_page' ]
		);

		// Addons submenu page.
		add_submenu_page(
			'wpforms-overview',
			esc_html__( 'WPForms Addons', 'wpforms-lite' ),
			'<span style="color:#f18500">' . esc_html__( 'Addons', 'wpforms-lite' ) . '</span>',
			$access->get_menu_cap( 'edit_forms' ),
			'wpforms-addons',
			[ $this, 'admin_page' ]
		);

		// Rotating submenu.
		$rotation = $this->get_rotating_submenu();

		if ( $rotation ) {
			add_submenu_page(
				'wpforms-overview',
				$rotation['page_title'],
				$rotation['menu_title'],
				$manage_cap,
				$rotation['menu_slug'],
				[ $this, 'admin_page' ]
			);
		}

		// SMTP submenu page.
		add_submenu_page(
			'wpforms-overview',
			esc_html__( 'SMTP', 'wpforms-lite' ),
			esc_html__( 'SMTP', 'wpforms-lite' ),
			$manage_cap,
			WPForms\Admin\Pages\SMTP::SLUG,
			[ $this, 'admin_page' ]
		);

		// About submenu page.
		add_submenu_page(
			'wpforms-overview',
			esc_html__( 'About WPForms', 'wpforms-lite' ),
			esc_html__( 'About Us', 'wpforms-lite' ),
			$access->get_menu_cap( 'any' ),
			WPForms_About::SLUG,
			[ $this, 'admin_page' ]
		);

		// Community submenu page.
		add_submenu_page(
			'wpforms-overview',
			esc_html__( 'Community', 'wpforms-lite' ),
			esc_html__( 'Community', 'wpforms-lite' ),
			$manage_cap,
			WPForms\Admin\Pages\Community::SLUG,
			[ $this, 'admin_page' ]
		);

		if ( ! wpforms()->is_pro() ) {
			add_submenu_page(
				'wpforms-overview',
				esc_html__( 'Upgrade to Pro', 'wpforms-lite' ),
				esc_html__( 'Upgrade to Pro', 'wpforms-lite' ),
				$manage_cap,
				wpforms_admin_upgrade_link( 'admin-menu' )
			);
		}
	}

	/**
	 * Hide the "Add New" admin menu item if a user can't create forms.
	 *
	 * @since 1.5.8
	 */
	public function hide_wpforms_submenu_items(): void {

		if ( wpforms_current_user_can( 'create_forms' ) ) {
			return;
		}

		global $submenu;

		if ( ! isset( $submenu['wpforms-overview'] ) ) {
			return;
		}

		foreach ( $submenu['wpforms-overview'] as $key => $item ) {
			if ( isset( $item[2] ) && $item[2] === 'wpforms-builder' ) {
				unset( $submenu['wpforms-overview'][ $key ] );
				break;
			}
		}

		$this->hide_wpforms_menu_item();
	}

	/**
	 * Hide the "WPForms" admin menu if it has no submenu items.
	 *
	 * @since 1.5.8
	 */
	public function hide_wpforms_menu_item(): void {

		global $submenu, $menu;

		if ( ! empty( $submenu['wpforms-overview'] ) ) {
			return;
		}

		unset( $submenu['wpforms-overview'] );

		foreach ( $menu as $key => $item ) {
			if ( isset( $item[2] ) && $item[2] === 'wpforms-overview' ) {
				unset( $menu[ $key ] );
				break;
			}
		}
	}

	/**
	 * Make changes to the PRO menu item.
	 *
	 * @since 1.8.1
	 */
	public function adjust_pro_menu_item(): void {

		global $submenu;

		// Bail if a plugin menu is not registered.
		if ( ! isset( $submenu['wpforms-overview'] ) ) {
			return;
		}

		$upgrade_link_position = key(
			array_filter(
				$submenu['wpforms-overview'],
				static function ( $item ) {

					return strpos( urldecode( $item[2] ), 'wpforms.com/lite-upgrade' ) !== false;
				}
			)
		);

		// Bail if "Upgrade to Pro" menu item is not registered.
		if ( $upgrade_link_position === null ) {
			return;
		}

		// Add the PRO badge to the menu item.
		// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
		if ( isset( $submenu['wpforms-overview'][ $upgrade_link_position ][4] ) ) {
			$submenu['wpforms-overview'][ $upgrade_link_position ][4] .= ' wpforms-sidebar-upgrade-pro';
		} else {
			$submenu['wpforms-overview'][ $upgrade_link_position ][] = 'wpforms-sidebar-upgrade-pro';
		}

		$current_screen      = get_current_screen();
		$upgrade_utm_content = $current_screen === null ? 'Upgrade to Pro' : 'Upgrade to Pro - ' . $current_screen->base;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$upgrade_utm_content = empty( $_GET['view'] ) ? $upgrade_utm_content : $upgrade_utm_content . ': ' . sanitize_key( $_GET['view'] );

		// Add utm_content to the menu item.
		$submenu['wpforms-overview'][ $upgrade_link_position ][2] = esc_url(
			add_query_arg(
				'utm_content',
				$upgrade_utm_content,
				$submenu['wpforms-overview'][ $upgrade_link_position ][2]
			)
		);
		// phpcs:enable WordPress.WP.GlobalVariablesOverride.Prohibited
	}

	/**
	 * Wrapper for the hook to render our custom settings pages.
	 *
	 * @since 1.0.0
	 */
	public function admin_page(): void {

		/**
		 * Fires to show the WPForms admin page.
		 *
		 * @since 1.0.0
		 */
		do_action( 'wpforms_admin_page' ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
	}

	/**
	 * Add a settings link to the Plugins page.
	 *
	 * @since        1.3.9
	 *
	 * @param array|mixed $links       Plugin row links.
	 * @param string      $plugin_file Path to the plugin file relative to the plugins' directory.
	 * @param array       $plugin_data An array of plugin data. See `get_plugin_data()`.
	 * @param string      $context     The plugin context.
	 *
	 * @return array $links
	 * @noinspection PhpUnusedParameterInspection
	 * @noinspection HtmlUnknownTarget
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function settings_link( $links, $plugin_file, $plugin_data, $context ): array { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

		$custom['wpforms-pro'] = sprintf(
			'<a href="%1$s" aria-label="%2$s" target="_blank" rel="noopener noreferrer"
				style="color: #00a32a; font-weight: 700;"
				onmouseover="this.style.color=\'#008a20\';"
				onmouseout="this.style.color=\'#00a32a\';"
				>%3$s</a>',
			esc_url(
				wpforms_admin_upgrade_link(
					'all-plugins',
					'Get WPForms Pro'
				)
			),
			esc_attr__( 'Upgrade to WPForms Pro', 'wpforms-lite' ),
			esc_html__( 'Get WPForms Pro', 'wpforms-lite' )
		);

		$custom['wpforms-settings'] = sprintf(
			'<a href="%s" aria-label="%s">%s</a>',
			esc_url(
				add_query_arg(
					[ 'page' => 'wpforms-settings' ],
					admin_url( 'admin.php' )
				)
			),
			esc_attr__( 'Go to WPForms Settings page', 'wpforms-lite' ),
			esc_html__( 'Settings', 'wpforms-lite' )
		);

		$custom['wpforms-docs'] = sprintf(
			'<a href="%1$s" aria-label="%2$s" target="_blank" rel="noopener noreferrer">%3$s</a>',
			esc_url(
				add_query_arg(
					[
						'utm_content'  => 'Documentation',
						'utm_campaign' => 'liteplugin',
						'utm_medium'   => 'all-plugins',
						'utm_source'   => 'WordPress',
					],
					'https://wpforms.com/docs/'
				)
			),
			esc_attr__( 'Read the documentation', 'wpforms-lite' ),
			esc_html__( 'Docs', 'wpforms-lite' )
		);

		return array_merge( $custom, (array) $links );
	}

	/**
	 * Determine which submenu item to show (rotation).
	 *
	 * Current behavior:
	 * - Show item until the plugin has been activated for 7 or more days.
	 * - Once 7+ days have passed since activation - show next item.
	 * - Once the last item has been active for more than 7 days, always display the first item (WP Consent page).
	 *
	 * @since 1.9.8.6
	 *
	 * @return array|null { menu_title, page_title, menu_slug } or null to show none.
	 */
	private function get_rotating_submenu(): ?array {

		$items    = $this->get_rotation_items();
		$now      = time();
		$defaults = [
			'label'       => '',
			'menu_slug'   => '',
			'slug'        => '',
			'plugin_file' => '',
		];

		// Find the first item that should be displayed.
		foreach ( $items as $item ) {
			$item = wp_parse_args( $item, $defaults );

			$label       = (string) $item['label'];
			$menu_slug   = (string) $item['menu_slug'];
			$plugin_slug = (string) $item['slug'];

			if ( empty( $label ) || empty( $menu_slug ) ) {
				continue; // Skip misconfigured items.
			}

			$timestamp = $this->get_promo_plugin_activation_timestamp( $plugin_slug );

			// Show if a plugin has never activated or within 7 days of activation.
			$within = $timestamp === 0 || ( $now - $timestamp ) < 7 * DAY_IN_SECONDS;

			if ( $within ) {
				return [
					'menu_title' => $label,
					'page_title' => $label,
					'menu_slug'  => $menu_slug,
				];
			}
		}

		// If all items are considered "complete", return the first one (cycle back).
		$first     = $items[0];
		$label     = $first['label'];
		$menu_slug = $first['menu_slug'];

		if ( ! empty( $label ) && ! empty( $menu_slug ) ) {
			return [
				'menu_title' => $label,
				'page_title' => $label,
				'menu_slug'  => $menu_slug,
			];
		}

		return null;
	}

	/**
	 * List of rotating plugins files.
	 *
	 * @since 1.9.8.6
	 *
	 * @return array
	 */
	private function get_rotation_plugins(): array {

		return [
			'wpconsent-cookies-banner-privacy-suite/wpconsent.php' => 'wpconsent',
			'wpconsent-premium/wpconsent-premium.php'     => 'wpconsent',
			'sugar-calendar-lite/sugar-calendar-lite.php' => 'sugar-calendar',
			'sugar-calendar/sugar-calendar.php'           => 'sugar-calendar',
			'duplicator/duplicator.php'                   => 'duplicator',
			'duplicator-pro/duplicator-pro.php'           => 'duplicator',
			'uncanny-automator/uncanny-automator.php'     => 'uncanny-automator',
			'uncanny-automator-pro/uncanny-automator-pro.php' => 'uncanny-automator',
		];
	}

	/**
	 * Record the activation time of a rotation plugin.
	 *
	 * @since 1.9.8.6
	 *
	 * @param string $plugin       Path to the plugin file relative to the plugins' directory.
	 * @param bool   $network_wide Whether the plugin is being activated network wide.
	 */
	public function activated_rotation_plugin( string $plugin, bool $network_wide ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

		$rotation_plugins = $this->get_rotation_plugins();
		$plugin_key       = $rotation_plugins[ $plugin ] ?? '';

		if ( empty( $plugin_key ) ) {
			return;
		}

		$activated_plugins = (array) get_option( 'wpforms_rotation_activated_plugins', [] );

		// Skip if already recorded.
		if ( isset( $activated_plugins[ $plugin_key ] ) ) {
			return;
		}

		$activated_plugins[ $plugin_key ] = time();

		update_option( 'wpforms_rotation_activated_plugins', $activated_plugins );
	}

	/**
	 * Editable list of rotating submenu items.
	 *
	 * @since 1.9.8.6
	 *
	 * @return array
	 */
	private function get_rotation_items(): array {

		return [
			[
				'label'       => esc_html__( 'Privacy Compliance', 'wpforms-lite' ),
				'menu_slug'   => WPForms\Admin\Pages\PrivacyCompliance::SLUG,
				'slug'        => 'wpconsent',
				'plugin_file' => 'wpconsent-cookies-banner-privacy-suite/wpconsent.php',
			],
			[
				'label'       => esc_html__( 'Events', 'wpforms-lite' ),
				'menu_slug'   => WPForms\Admin\Pages\SugarCalendar::SLUG,
				'slug'        => 'sugar-calendar',
				'plugin_file' => 'sugar-calendar-lite/sugar-calendar-lite.php',
			],
			[
				'label'       => esc_html__( 'Backups', 'wpforms-lite' ),
				'menu_slug'   => WPForms\Admin\Pages\Duplicator::SLUG,
				'slug'        => 'duplicator',
				'plugin_file' => 'duplicator/duplicator.php',
			],
			[
				'label'       => esc_html__( 'Automation', 'wpforms-lite' ),
				'menu_slug'   => WPForms\Admin\Pages\UncannyAutomator::SLUG,
				'slug'        => 'uncanny-automator',
				'plugin_file' => 'uncanny-automator/uncanny-automator.php',
			],
		];
	}

	/**
	 * Get the HTML for the "NEW!" badge.
	 *
	 * @since 1.7.8
	 *
	 * @return string
	 */
	private function get_new_badge_html(): string {

		return '<span class="wpforms-menu-new">&nbsp;NEW!</span>';
	}

	/**
	 * Output inline styles for the admin menu.
	 *
	 * @since 1.7.8
	 */
	public function admin_menu_styles(): void {

		$styles = '#adminmenu .wpforms-menu-new { display: inline-block; color: #f18500; vertical-align: super; font-size: 9px; font-weight: 600; padding-inline-start: 2px; }';

		if ( ! wpforms()->is_pro() ) {
			$styles .= 'a.wpforms-sidebar-upgrade-pro { background-color: #00a32a !important; color: #fff !important; font-weight: 600 !important; }';
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		printf( '<style>%s</style>', $styles );
	}

	/**
	 * Get a timestamp.
	 *
	 * @since 1.9.8.6
	 *
	 * @param string $slug Slug of the plugin.
	 *
	 * @return int
	 */
	private function get_promo_plugin_activation_timestamp( string $slug ): int {

		$activated_plugins = (array) get_option( 'wpforms_rotation_activated_plugins', [] );

		return isset( $activated_plugins[ $slug ] ) ? (int) $activated_plugins[ $slug ] : 0;
	}
}

new WPForms_Admin_Menu();
