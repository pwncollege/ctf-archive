<?php

namespace WPMailSMTP\Admin;

use WPMailSMTP\WP;

/**
 * Class ParentPageAbstract.
 *
 * @since 2.8.0
 */
abstract class ParentPageAbstract implements PageInterface {

	/**
	 * Slug of a page.
	 *
	 * @since 2.8.0
	 *
	 * @var string
	 */
	protected $slug;

	/**
	 * Page tabs.
	 *
	 * @since 2.8.0
	 *
	 * @var PageAbstract[]
	 */
	protected $tabs = [];

	/**
	 * Page default tab slug.
	 *
	 * @since 2.8.0
	 *
	 * @var string
	 */
	protected $default_tab = '';

	/**
	 * Constructor.
	 *
	 * @since 2.8.0
	 *
	 * @param array $tabs Page tabs.
	 */
	public function __construct( $tabs = [] ) {

		/**
		 * Filters parent page tabs.
		 *
		 * @since 2.8.0
		 *
		 * @param string[] $tabs Parent page tabs.
		 */
		$tabs = apply_filters( 'wp_mail_smtp_admin_page_' . $this->slug . '_tabs', $tabs );

		if ( wp_mail_smtp()->get_admin()->is_admin_page( $this->slug ) ) {
			$this->init_tabs( $tabs );
			$this->hooks();
		}

		if ( WP::is_doing_self_ajax() ) {
			$this->init_ajax( $tabs );
		}
	}

	/**
	 * Hooks.
	 *
	 * @since 2.8.0
	 */
	protected function hooks() {

		add_action( 'admin_init', [ $this, 'process_actions' ] );

		// Register tab related hooks.
		if ( isset( $this->tabs[ $this->get_current_tab() ] ) ) {
			$this->tabs[ $this->get_current_tab() ]->hooks();
		}
	}

	/**
	 * Initialize ajax actions.
	 *
	 * @since 3.0.0
	 *
	 * @param array $tabs Page tabs.
	 */
	private function init_ajax( $tabs ) {

		foreach ( $tabs as $tab ) {
			if ( $this->is_valid_tab( $tab ) ) {
				( new $tab( $this ) )->ajax();
			}
		}
	}

	/**
	 * Get the page slug.
	 *
	 * @since 2.8.0
	 *
	 * @return string
	 */
	public function get_slug() {

		return $this->slug;
	}

	/**
	 * Get the page tabs.
	 *
	 * @since 2.8.0
	 *
	 * @return PageAbstract[]
	 */
	public function get_tabs() {

		return $this->tabs;
	}

	/**
	 * Get the page tabs slugs.
	 *
	 * @since 2.8.0
	 *
	 * @return string[]
	 */
	public function get_tabs_slugs() {

		return array_map(
			function ( $tab ) {
				return $tab->get_slug();
			},
			$this->tabs
		);
	}

	/**
	 * Get the page/tab link.
	 *
	 * @since 2.8.0
	 *
	 * @param string $tab Tab to generate a link to.
	 *
	 * @return string
	 */
	public function get_link( $tab = '' ) {

		return add_query_arg(
			'tab',
			$this->get_defined_tab( $tab ),
			WP::admin_url( 'admin.php?page=' . Area::SLUG . '-' . $this->slug )
		);
	}

	/**
	 * Get the current tab.
	 *
	 * @since 2.8.0
	 *
	 * @return string
	 */
	public function get_current_tab() {

		$tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		return $this->get_defined_tab( $tab );
	}

	/**
	 * Get the tab label.
	 *
	 * @since 2.9.0
	 *
	 * @param string $tab Tab key.
	 *
	 * @return string
	 */
	public function get_tab_label( $tab ) {

		$tabs = $this->get_tabs();

		return isset( $tabs[ $tab ] ) ? $tabs[ $tab ]->get_label() : '';
	}

	/**
	 * Get the tab title.
	 *
	 * @since 2.9.0
	 *
	 * @param string $tab Tab key.
	 *
	 * @return string
	 */
	public function get_tab_title( $tab ) {

		$tabs = $this->get_tabs();

		return isset( $tabs[ $tab ] ) ? $tabs[ $tab ]->get_title() : '';
	}

	/**
	 * Get the defined or default tab.
	 *
	 * @since 2.8.0
	 *
	 * @param string $tab Tab to check.
	 *
	 * @return string Defined tab. Fallback to default one if it doesn't exist.
	 */
	protected function get_defined_tab( $tab ) {

		$tab = sanitize_key( $tab );

		return in_array( $tab, $this->get_tabs_slugs(), true ) ? $tab : $this->default_tab;
	}

	/**
	 * Initialize tabs.
	 *
	 * @since 2.8.0
	 *
	 * @param array $tabs Page tabs.
	 */
	public function init_tabs( $tabs ) {

		foreach ( $tabs as $key => $tab ) {
			if ( ! $this->is_valid_tab( $tab ) ) {
				continue;
			}

			$this->tabs[ $key ] = new $tab( $this );
		}

		// Sort tabs by priority.
		$this->sort_tabs();
	}

	/**
	 * All possible plugin forms manipulation and hooks registration will be done here.
	 *
	 * @since 2.8.0
	 */
	public function process_actions() {

		$tabs = $this->get_tabs_slugs();

		// Allow to process only own tabs.
		if ( ! array_key_exists( $this->get_current_tab(), $tabs ) ) {
			return;
		}

		// Process POST only if it exists.
		// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		if ( ! empty( $_POST ) && isset( $_POST['wp-mail-smtp-post'] ) ) {
			$current_tab = $this->tabs[ $this->get_current_tab() ];

			// Verify nonce.
			$current_tab->check_admin_referer();

			// Verify capability.
			if ( ! current_user_can( wp_mail_smtp()->get_capability_manage_options() ) ) {
				wp_die( esc_html__( 'You don\'t have the capability to perform this action.', 'wp-mail-smtp' ) );
			}

			if ( ! empty( $_POST['wp-mail-smtp'] ) ) {
				$post = $_POST['wp-mail-smtp'];
			} else {
				$post = [];
			}

			$current_tab->process_post( $post );
		}
		// phpcs:enable

		// This won't do anything for most pages.
		// Works for plugin page only, when GET params are allowed.
		$this->tabs[ $this->get_current_tab() ]->process_auth();
	}

	/**
	 * Display page content based on the current tab.
	 *
	 * @since 2.8.0
	 */
	public function display() {

		$current_tab = $this->get_current_tab();
		$page_slug   = $this->slug;
		?>
		<div class="wp-mail-smtp-page-title">
			<?php if ( count( $this->tabs ) > 1 ) : ?>
				<?php foreach ( $this->tabs as $tab ) : ?>
					<a href="<?php echo esc_url( $tab->get_link() ); ?>"
						 class="tab <?php echo $current_tab === $tab->get_slug() ? 'active' : ''; ?>">
						<?php echo esc_html( $tab->get_label() ); ?>
					</a>
				<?php endforeach; ?>
			<?php else : ?>
				<span class="page-title"><?php echo esc_html( array_values( $this->tabs )[0]->get_title() ); ?></span>
			<?php endif; ?>

			<?php
			/**
			 * Fires after page title.
			 *
			 * @since 2.9.0
			 *
			 * @param ParentPageAbstract $page Current page.
			 */
			do_action( "wp_mail_smtp_admin_page_{$page_slug}_{$current_tab}_display_header", $this );
			?>
		</div>

		<div class="wp-mail-smtp-page-content">
			<?php
			foreach ( $this->tabs as $tab ) {
				if ( $tab->get_slug() === $current_tab ) {

					printf( '<h1 class="screen-reader-text">%s</h1>', esc_html( $tab->get_title() ) );

					/**
					 * Fires before tab content.
					 *
					 * @since 2.8.0
					 *
					 * @param PageAbstract $tab Current tab.
					 */
					do_action( 'wp_mail_smtp_admin_pages_before_content', $tab );

					/**
					 * Fires before tab content.
					 *
					 * @since 2.9.0
					 *
					 * @param PageAbstract $tab Current tab.
					 */
					do_action( "wp_mail_smtp_admin_page_{$page_slug}_{$current_tab}_display_before", $tab );

					$tab->display();

					/**
					 * Fires after tab content.
					 *
					 * @since 2.8.0
					 *
					 * @param PageAbstract $tab Current tab.
					 */
					do_action( "wp_mail_smtp_admin_page_{$page_slug}_{$current_tab}_display_after", $tab );

					break;
				}
			}
			?>
		</div>
		<?php
	}

	/**
	 * Sort tabs by priority.
	 *
	 * @since 2.8.0
	 */
	protected function sort_tabs() {

		uasort(
			$this->tabs,
			function ( $a, $b ) {

				return ( $a->get_priority() < $b->get_priority() ) ? - 1 : 1;
			}
		);
	}

	/**
	 * Whether tab is valid.
	 *
	 * @since 3.0.0
	 *
	 * @param array $tab Page tab.
	 *
	 * @return bool
	 */
	private function is_valid_tab( $tab ) {

		return is_subclass_of( $tab, '\WPMailSMTP\Admin\PageAbstract' );
	}
}
