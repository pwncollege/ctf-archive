<?php

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpIllegalPsrClassPathInspection */
/** @noinspection AutoloadingIssuesInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

use WPForms\Admin\Notice;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ask for some love.
 *
 * @since 1.3.2
 */
class WPForms_Review {

	/**
	 * List of page slugs.
	 *
	 * Some 3rd-party addons may use page slugs that start with `wpforms-` (e.g., WPForms Views),
	 * so we should define exact pages we want the footer to be displayed on instead
	 * of targeting any page that looks like a WPForms page.
	 *
	 * @since 1.9.8.6
	 *
	 * @var array
	 */
	private const PAGES = [
		'wpforms-about',
		'wpforms-addons',
		'wpforms-community',
		'wpforms-entries',
		'wpforms-overview',
		'wpforms-payments',
		'wpforms-settings',
		'wpforms-smtp',
		'wpforms-templates',
		'wpforms-tools',
		'wpforms-wpconsent',
		'wpforms-sugar-calendar',
		'wpforms-duplicator',
		'wpforms-uncanny-automator',
	];

	/**
	 * Primary class constructor.
	 *
	 * @since 1.3.2
	 */
	public function __construct() {

		$this->hooks();
	}

	/**
	 * Hooks.
	 *
	 * @since 1.9.4
	 *
	 * @return void
	 */
	private function hooks(): void {

		// Admin notice requesting review.
		add_action( 'admin_init', [ $this, 'review_request' ] );

		// Admin footer text.
		add_filter( 'admin_footer_text', [ $this, 'admin_footer' ], 1, 2 );
		add_action( 'in_admin_footer', [ $this, 'promote_wpforms' ] );
	}

	/**
	 * Add admin notices as needed for reviews.
	 *
	 * @since 1.3.2
	 */
	public function review_request() {

		if (
			// Only consider showing the review request to admin users.
			! is_super_admin() ||
			// If the user has opted out of product announcement notifications, don't display the review request.
			wpforms_setting( 'hide-announcements' ) ||
			// Do not show the review request on Addons page.
			wpforms_is_admin_page( 'addons' )
		) {
			return;
		}

		// Verify that we can do a check for reviews.
		$notices = (array) get_option( 'wpforms_admin_notices', [] );
		$time    = time();
		$load    = false;

		if ( empty( $notices['review_request'] ) ) {
			$notices['review_request'] = [
				'time'      => $time,
				'dismissed' => false,
			];

			update_option( 'wpforms_admin_notices', $notices );

			return;
		}

		// Check if it has been dismissed or not.
		if (
			isset( $notices['review_request']['dismissed'], $notices['review_request']['time'] ) &&
			! $notices['review_request']['dismissed'] &&
			( ( $notices['review_request']['time'] + DAY_IN_SECONDS ) <= $time )
		) {
			$load = true;
		}

		// If we cannot load, return early.
		if ( ! $load ) {
			return;
		}

		// The Logic is slightly different depending on what's at our disposal.
		if ( class_exists( 'WPForms_Entry_Handler', false ) && wpforms()->is_pro() ) {
			$this->review();
		} else {
			$this->review_lite();
		}
	}

	/**
	 * Maybe show review request.
	 *
	 * @since 1.3.9
	 */
	public function review() {

		// Fetch total entries.
		$entry_handler = wpforms()->obj( 'entry' );
		$entries       = $entry_handler ? $entry_handler->get_entries( [ 'number' => 50 ], true ) : 0;

		// Only show review request if the site has collected at least 50 entries.
		if ( empty( $entries ) || $entries < 50 ) {
			return;
		}

		ob_start();

		// We have a candidate! Output a review message.
		$this->review_content();

		Notice::info(
			ob_get_clean(),
			[
				'dismiss' => Notice::DISMISS_GLOBAL,
				'slug'    => 'review_request',
				'autop'   => false,
				'class'   => 'wpforms-review-notice',
			]
		);
	}

	/**
	 * Maybe show Lite review request.
	 *
	 * @since 1.3.9
	 */
	public function review_lite() {

		// Do not show the review request on Entries pages.
		if ( wpforms_is_admin_page( 'entries' ) ) {
			return;
		}

		// Fetch when plugin was initially installed.
		$activated = (array) get_option( 'wpforms_activated', [] );

		if ( ! empty( $activated['lite'] ) ) {
			// Only continue if the plugin has been installed for at least 14 days.
			if ( ( $activated['lite'] + ( DAY_IN_SECONDS * 14 ) ) > time() ) {
				return;
			}
		} else {
			$activated['lite'] = time();

			update_option( 'wpforms_activated', $activated );

			return;
		}

		// Only proceed with displaying if the user created at least one form.
		$form_count = wp_count_posts( 'wpforms' );

		if ( empty( $form_count->publish ) ) {
			return;
		}

		// Check if the Constant Contact notice is displaying.
		$cc = get_option( 'wpforms_constant_contact', false );

		// If it's displaying don't ask for review until they configure CC or
		// dismiss the notice.
		if ( $cc ) {
			return;
		}

		ob_start();

		// We have a candidate! Output a review message.
		$this->review_content();

		Notice::info(
			ob_get_clean(),
			[
				'dismiss' => Notice::DISMISS_GLOBAL,
				'slug'    => 'review_lite_request',
				'autop'   => false,
				'class'   => 'wpforms-review-notice',
			]
		);
	}

	/**
	 * Output the review content.
	 *
	 * @since 1.8.7.2
	 */
	private function review_content(): void {

		?>
		<p><?php esc_html_e( 'Hey, there! It looks like you enjoy creating forms with WPForms. Would you do us a favor and take a few seconds to give us a 5-star review? We’d love to hear from you.', 'wpforms-lite' ); ?></p>
		<p>
			<a
					href="<?php echo wpforms_wp_org_review_link(); ?>" class="wpforms-notice-dismiss wpforms-review-out"
					target="_blank" rel="noopener noreferrer">
				<?php esc_html_e( 'Ok, you deserve it', 'wpforms-lite' ); ?>
			</a>
			<br>
			<a href="#" class="wpforms-notice-dismiss" target="_blank" rel="noopener noreferrer">
				<?php esc_html_e( 'Nope, maybe later', 'wpforms-lite' ); ?>
			</a>
			<br>
			<a href="#" class="wpforms-notice-dismiss" target="_blank" rel="noopener noreferrer">
				<?php esc_html_e( 'I already did', 'wpforms-lite' ); ?>
			</a>
		</p>
		<?php
	}

	/**
	 * When a user is on a WPForms related admin page, display footer text
	 * that graciously asks them to rate us.
	 *
	 * @since 1.3.2
	 *
	 * @param string|mixed $text Footer text.
	 *
	 * @return string
	 * @noinspection HtmlUnknownTarget
	 */
	public function admin_footer( $text ): string {

		global $current_screen;

		$text = (string) $text;

		if ( ! empty( $current_screen->id ) && strpos( $current_screen->id, 'wpforms' ) !== false ) {
			$url  = wpforms_wp_org_review_link();
			$text = sprintf(
				wp_kses( /* translators: $1$s - WPForms plugin name, $2$s - WP.org review link, $3$s - WP.org review link. */
					__( 'Please rate %1$s <a href="%2$s" target="_blank" rel="noopener noreferrer">&#9733;&#9733;&#9733;&#9733;&#9733;</a> on <a href="%3$s" target="_blank" rel="noopener">WordPress.org</a> to help us spread the word.', 'wpforms-lite' ),
					[
						'a' => [
							'href'   => [],
							'target' => [],
							'rel'    => [],
						],
					]
				),
				'<strong>WPForms</strong>',
				$url,
				$url
			);
		}

		return $text;
	}

	/**
	 * Pre-footer promotion block, displayed on all WPForms admin pages except Form Builder.
	 *
	 * @since 1.8.0
	 */
	public function promote_wpforms() {

		// phpcs:ignore WordPress.Security.NonceVerification
		$current_page = isset( $_REQUEST['page'] ) ? sanitize_key( $_REQUEST['page'] ) : '';

		if ( ! in_array( $current_page, self::PAGES, true ) ) {
			return;
		}

		$links = [
			[
				'url'    => wpforms()->is_pro() ?
					wpforms_utm_link(
						'https://wpforms.com/account/support/',
						'Plugin Footer',
						'Contact Support'
					) : 'https://wordpress.org/support/plugin/wpforms-lite/',
				'text'   => __( 'Support', 'wpforms-lite' ),
				'target' => '_blank',
			],
			[
				'url'    => wpforms_utm_link(
					'https://wpforms.com/docs/',
					'Plugin Footer',
					'Plugin Documentation'
				),
				'text'   => __( 'Docs', 'wpforms-lite' ),
				'target' => '_blank',
			],
			[
				'url'    => 'https://www.facebook.com/groups/wpformsvip/',
				'text'   => __( 'VIP Circle', 'wpforms-lite' ),
				'target' => '_blank',
			],
			[
				'url'  => admin_url( 'admin.php?page=wpforms-about' ),
				'text' => __( 'Free Plugins', 'wpforms-lite' ),
			],
		];

		echo wpforms_render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'admin/promotion',
			[
				'title' => __( 'Made with ♥ by the WPForms Team', 'wpforms-lite' ),
				'links' => $links,
			],
			true
		);
	}
}

new WPForms_Review();
