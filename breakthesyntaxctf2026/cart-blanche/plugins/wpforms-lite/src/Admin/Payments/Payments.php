<?php

namespace WPForms\Admin\Payments;

use WPForms\Admin\Payments\Views\Coupons\Education;
use WPForms\Admin\Payments\Views\Overview\BulkActions;
use WPForms\Admin\Payments\Views\Single;
use WPForms\Admin\Payments\Views\Overview\Page;
use WPForms\Admin\Payments\Views\Overview\Coupon;
use WPForms\Admin\Payments\Views\Overview\Filters;
use WPForms\Admin\Payments\Views\Overview\Search;

/**
 * Payments class.
 *
 * @since 1.8.2
 */
class Payments {

	/**
	 * Payments page slug.
	 *
	 * @since 1.8.2
	 *
	 * @var string
	 */
	const SLUG = 'wpforms-payments';

	/**
	 * Available views (pages).
	 *
	 * @since 1.8.2
	 *
	 * @var array
	 */
	private $views = [];

	/**
	 * The current page slug.
	 *
	 * @since 1.8.2
	 *
	 * @var string
	 */
	private $active_view_slug;

	/**
	 * The current page view.
	 *
	 * @since 1.8.2
	 *
	 * @var null|\WPForms\Admin\Payments\Views\PaymentsViewsInterface
	 */
	private $view;

	/**
	 * Initialize class.
	 *
	 * @since 1.8.2
	 */
	public function init() {

		if ( ! wpforms_is_admin_page( 'payments' ) ) {
			return;
		}

		$this->update_request_uri();

		( new ScreenOptions() )->init();
		( new Coupon() )->init();
		( new Filters() )->init();
		( new Search() )->init();
		( new BulkActions() )->init();

		$this->hooks();
	}

	/**
	 * Initialize the active view.
	 *
	 * @since 1.8.2
	 */
	public function init_view() {

		$view_ids = array_keys( $this->get_views() );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$this->active_view_slug = isset( $_GET['view'] ) ? sanitize_key( $_GET['view'] ) : 'payments';

		// If the user tries to load an invalid view - fallback to the first available.
		if ( ! in_array( $this->active_view_slug, $view_ids, true ) ) {
			$this->active_view_slug = reset( $view_ids );
		}

		if ( ! isset( $this->views[ $this->active_view_slug ] ) ) {
			return;
		}

		$this->view = $this->views[ $this->active_view_slug ];

		$this->view->init();
	}

	/**
	 * Get available views.
	 *
	 * @since 1.8.2
	 *
	 * @return array
	 */
	private function get_views() {

		if ( ! empty( $this->views ) ) {
			return $this->views;
		}

		$views = [
			'coupons' => new Education(),
		];

		/**
		 * Allow to extend payment views.
		 *
		 * @since 1.8.2
		 *
		 * @param array $views Array of views where key is slug.
		 */
		$this->views = (array) apply_filters( 'wpforms_admin_payments_payments_get_views', $views );

		$this->views['payments'] = new Page();
		$this->views['payment']  = new Single();

		// Payments view should be the first one.
		$this->views = array_merge( [ 'payments' => $this->views['payments'] ], $this->views );

		$this->views = array_filter(
			$this->views,
			static function ( $view ) {

				return $view->current_user_can();
			}
		);

		return $this->views;
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.8.2
	 */
	private function hooks() {

		add_action( 'wpforms_admin_page', [ $this, 'output' ] );
		add_action( 'current_screen', [ $this, 'init_view' ] );
		add_filter( 'wpforms_db_payments_payment_add_secondary_where_conditions_args', [ $this, 'modify_secondary_where_conditions_args' ] );
	}

	/**
	 * Output the page.
	 *
	 * @since 1.8.2
	 */
	public function output() {

		if ( empty( $this->view ) ) {
			return;
		}
		?>
		<div id="wpforms-payments" class="wrap wpforms-admin-wrap wpforms-payments-wrap wpforms-payments-wrap-<?php echo esc_attr( $this->active_view_slug ); ?>">

			<h1 class="page-title">
				<?php esc_html_e( 'Payments', 'wpforms-lite' ); ?>
				<?php $this->view->heading(); ?>
			</h1>

			<?php if ( ! empty( $this->view->get_tab_label() ) ) : ?>
				<div class="wpforms-tabs-wrapper">
					<?php $this->display_tabs(); ?>
				</div>
			<?php endif; ?>

			<div class="wpforms-admin-content wpforms-admin-settings">
				<?php $this->view->display(); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Display tabs.
	 *
	 * @since 1.8.2.2
	 */
	private function display_tabs() {

		$views = $this->get_views();

		// Remove views that should not be displayed.
		$views = array_filter(
			$views,
			static function ( $view ) {

				return ! empty( $view->get_tab_label() );
			}
		);

		// If there is only one view - no need to display tabs.
		if ( count( $views ) === 1 ) {
			return;
		}
		?>
		<nav class="nav-tab-wrapper">
			<?php foreach ( $views as $slug => $view ) : ?>
				<a href="<?php echo esc_url( $this->get_tab_url( $slug ) ); ?>" class="nav-tab <?php echo $slug === $this->active_view_slug ? 'nav-tab-active' : ''; ?>">
					<?php echo esc_html( $view->get_tab_label() ); ?>
				</a>
			<?php endforeach; ?>
		</nav>
		<?php
	}

	/**
	 * Get tab URL.
	 *
	 * @since 1.8.2.2
	 *
	 * @param string $tab Tab slug.
	 *
	 * @return string
	 */
	private function get_tab_url( $tab ) {

		return add_query_arg(
			[
				'page' => self::SLUG,
				'view' => $tab,
			],
			admin_url( 'admin.php' )
		);
	}

	/**
	 * Modify arguments of secondary where clauses.
	 *
	 * @since 1.8.2
	 *
	 * @param array $args Query arguments.
	 *
	 * @return array
	 */
	public function modify_secondary_where_conditions_args( $args ) {

		// Set a current mode.
		if ( ! isset( $args['mode'] ) ) {
			$args['mode'] = Page::get_mode();
		}

		return $args;
	}

	/**
	 * Update view param in request URI.
	 *
	 * Backward compatibility for old URLs.
	 *
	 * @since 1.8.4
	 */
	private function update_request_uri() {

		// phpcs:disable WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		if ( ! isset( $_GET['view'], $_SERVER['REQUEST_URI'] ) ) {
			return;
		}

		$old_new = [
			'single'   => 'payment',
			'overview' => 'payments',
		];

		if (
			! array_key_exists( $_GET['view'], $old_new )
			|| in_array( $_GET['view'], $old_new, true )
		) {
			return;
		}

		wp_safe_redirect(
			str_replace(
				'view=' . $_GET['view'],
				'view=' . $old_new[ $_GET['view'] ],
				esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) )
			)
		);
		// phpcs:enable WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash

		exit;
	}
}
