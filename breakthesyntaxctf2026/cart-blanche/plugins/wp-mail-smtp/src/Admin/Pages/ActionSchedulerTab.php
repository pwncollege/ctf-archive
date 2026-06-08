<?php

namespace WPMailSMTP\Admin\Pages;

use WPMailSMTP\Admin\PageAbstract;

/**
 * Class ActionScheduler.
 *
 * @since 2.9.0
 */
class ActionSchedulerTab extends PageAbstract {

	/**
	 * Part of the slug of a tab.
	 *
	 * @since 2.9.0
	 *
	 * @var string
	 */
	protected $slug = 'action-scheduler';

	/**
	 * Tab priority.
	 *
	 * @since 2.9.0
	 *
	 * @var int
	 */
	protected $priority = 30;

	/**
	 * Link label of a tab.
	 *
	 * @since 2.9.0
	 *
	 * @return string
	 */
	public function get_label() {

		return esc_html__( 'Scheduled Actions', 'wp-mail-smtp' );
	}

	/**
	 * Title of a tab.
	 *
	 * @since 2.9.0
	 *
	 * @return string
	 */
	public function get_title() {

		return $this->get_label();
	}

	/**
	 * URL to a tab.
	 *
	 * @since 2.9.0
	 *
	 * @return string
	 */
	public function get_link() {

		return add_query_arg( [ 's' => 'wp_mail_smtp' ], parent::get_link() );
	}

	/**
	 * Register hooks.
	 *
	 * @since 2.9.0
	 */
	public function hooks() {

		add_action( 'current_screen', [ $this, 'init' ], 20 );
	}

	/**
	 * Init.
	 *
	 * @since 2.9.0
	 */
	public function init() {

		if ( $this->is_applicable() ) {
			\ActionScheduler_AdminView::instance()->process_admin_ui();
		}
	}

	/**
	 * Display scheduled actions table.
	 *
	 * @since 2.9.0
	 */
	public function display() {

		if ( ! $this->is_applicable() ) {
			return;
		}
		?>
		<h1><?php echo esc_html__( 'Scheduled Actions', 'wp-mail-smtp' ); ?></h1>

		<p>
			<?php
			echo sprintf(
				wp_kses( /* translators: %s - Action Scheduler website URL. */
					__( 'WP Mail SMTP is using the <a href="%s" target="_blank" rel="noopener noreferrer">Action Scheduler</a> library, which allows it to queue and process bigger tasks in the background without making your site slower for your visitors. Below you can see the list of all tasks and their status. This table can be very useful when debugging certain issues.', 'wp-mail-smtp' ),
					[
						'a' => [
							'href'   => [],
							'rel'    => [],
							'target' => [],
						],
					]
				),
				'https://actionscheduler.org/'
			);
			?>
		</p>

		<p>
			<?php echo esc_html__( 'Action Scheduler library is also used by other plugins, like WPForms and WooCommerce, so you might see tasks that are not related to our plugin in the table below.', 'wp-mail-smtp' ); ?>
		</p>

		<?php if ( isset( $_GET['s'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div id="wp-mail-smtp-reset-filter">
				<?php
				echo wp_kses(
					sprintf( /* translators: %s - search term. */
						__( 'Search results for <strong>%s</strong>', 'wp-mail-smtp' ),
						sanitize_text_field( wp_unslash( $_GET['s'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					),
					[ 'strong' => [] ]
				);
				?>
				<a href="<?php echo esc_url( remove_query_arg( 's' ) ); ?>">
					<i class="reset dashicons dashicons-dismiss"></i>
				</a>
			</div>
		<?php endif; ?>

		<?php
		\ActionScheduler_AdminView::instance()->render_admin_ui();
	}

	/**
	 * Check if ActionScheduler_AdminView class exists.
	 *
	 * @since 2.9.0
	 *
	 * @return bool
	 */
	private function is_applicable() {

		return class_exists( 'ActionScheduler_AdminView' );
	}
}
