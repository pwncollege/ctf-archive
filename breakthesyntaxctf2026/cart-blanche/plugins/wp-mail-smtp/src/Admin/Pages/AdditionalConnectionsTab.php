<?php

namespace WPMailSMTP\Admin\Pages;

use WPMailSMTP\Admin\PageAbstract;

/**
 * Class AdditionalConnectionsTab is a placeholder for Pro additional connections feature.
 * Displays product education.
 *
 * @since 3.7.0
 */
class AdditionalConnectionsTab extends PageAbstract {

	/**
	 * Part of the slug of a tab.
	 *
	 * @since 3.7.0
	 *
	 * @var string
	 */
	protected $slug = 'connections';

	/**
	 * Constructor.
	 *
	 * @since 3.7.0
	 *
	 * @param PageAbstract $parent_page Parent page object.
	 */
	public function __construct( $parent_page = null ) {

		parent::__construct( $parent_page );

		if ( wp_mail_smtp()->get_admin()->get_current_tab() === $this->slug && ! wp_mail_smtp()->is_pro() ) {
			$this->hooks();
		}
	}

	/**
	 * Link label of a tab.
	 *
	 * @since 3.7.0
	 *
	 * @return string
	 */
	public function get_label() {

		return esc_html__( 'Additional Connections', 'wp-mail-smtp' );
	}

	/**
	 * Register hooks.
	 *
	 * @since 3.7.0
	 */
	public function hooks() {

		add_action( 'wp_mail_smtp_admin_area_enqueue_assets', [ $this, 'enqueue_assets' ] );
	}

	/**
	 * Enqueue required JS and CSS.
	 *
	 * @since 3.7.0
	 */
	public function enqueue_assets() {

		wp_enqueue_style(
			'wp-mail-smtp-admin-lity',
			wp_mail_smtp()->assets_url . '/css/vendor/lity.min.css',
			[],
			'2.4.1'
		);
		wp_enqueue_script(
			'wp-mail-smtp-admin-lity',
			wp_mail_smtp()->assets_url . '/js/vendor/lity.min.js',
			[],
			'2.4.1'
		);
	}

	/**
	 * Output HTML of additional connections' education.
	 *
	 * @since 3.7.0
	 */
	public function display() {

		$top_upgrade_button_url    = wp_mail_smtp()->get_upgrade_link(
			[
				'medium'  => 'Additional Connections Settings',
				'content' => 'Upgrade to WP Mail SMTP Pro Button Top',
			]
		);
		$bottom_upgrade_button_url = wp_mail_smtp()->get_upgrade_link(
			[
				'medium'  => 'Additional Connections Settings',
				'content' => 'Upgrade to WP Mail SMTP Pro Button',
			]
		);
		?>
		<div id="wp-mail-additional-connections-product-education" class="wp-mail-smtp-product-education">
			<div class="wp-mail-smtp-product-education__row">
				<h4 class="wp-mail-smtp-product-education__heading">
					<?php esc_html_e( 'Additional Connections', 'wp-mail-smtp' ); ?>
				</h4>
				<p class="wp-mail-smtp-product-education__description">
					<?php
					esc_html_e( 'Create additional connections to set a backup for your Primary Connection or to configure Smart Routing.', 'wp-mail-smtp' );
					?>
				</p>

				<a href="<?php echo esc_url( $top_upgrade_button_url ); ?>" target="_blank" rel="noopener noreferrer" class="wp-mail-smtp-product-education__upgrade-btn wp-mail-smtp-product-education__upgrade-btn--top wp-mail-smtp-btn wp-mail-smtp-btn-upgrade wp-mail-smtp-btn-orange">
					<?php esc_html_e( 'Upgrade to WP Mail SMTP Pro', 'wp-mail-smtp' ); ?>
				</a>
			</div>

			<?php
			$this->display_education_screenshots();
			$this->display_education_features_list();
			?>

			<a href="<?php echo esc_url( $bottom_upgrade_button_url ); ?>" target="_blank" rel="noopener noreferrer" class="wp-mail-smtp-product-education__upgrade-btn wp-mail-smtp-product-education__upgrade-btn--bottom wp-mail-smtp-btn wp-mail-smtp-btn-upgrade wp-mail-smtp-btn-orange">
				<?php esc_html_e( 'Upgrade to WP Mail SMTP Pro', 'wp-mail-smtp' ); ?>
			</a>
		</div>
		<?php
	}

	/**
	 * Output HTML of additional connections' education screenshots.
	 *
	 * @since 3.7.0
	 */
	protected function display_education_screenshots() {

		$assets_url  = wp_mail_smtp()->assets_url . '/images/additional-connections/';
		$screenshots = [
			[
				'url'           => $assets_url . 'screenshot-01.png',
				'url_thumbnail' => $assets_url . 'thumbnail-01.png',
				'title'         => __( 'Backup Connection', 'wp-mail-smtp' ),
			],
			[
				'url'           => $assets_url . 'screenshot-02.png',
				'url_thumbnail' => $assets_url . 'thumbnail-02.png',
				'title'         => __( 'Smart Routing', 'wp-mail-smtp' ),
			],
		];
		?>
		<div class="wp-mail-smtp-product-education__row wp-mail-smtp-product-education__row--full-width">
			<div class="wp-mail-smtp-product-education__screenshots wp-mail-smtp-product-education__screenshots--two">
				<?php foreach ( $screenshots as $screenshot ) : ?>
					<div>
						<a href="<?php echo esc_url( $screenshot['url'] ); ?>" data-lity data-lity-desc="<?php echo esc_attr( $screenshot['title'] ); ?>">
							<img src="<?php echo esc_url( $screenshot['url_thumbnail'] ); ?>" alt="<?php esc_attr( $screenshot['title'] ); ?>">
						</a>
						<span><?php echo esc_html( $screenshot['title'] ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Output HTML of additional connections' education features list.
	 *
	 * @since 3.7.0
	 */
	protected function display_education_features_list() {

		?>
		<div class="wp-mail-smtp-product-education__row wp-mail-smtp-product-education__row--full-width">
			<div class="wp-mail-smtp-product-education__list">
				<h4><?php esc_html_e( 'With additional connections you can...', 'wp-mail-smtp' ); ?></h4>
				<div>
					<ul>
						<li><?php esc_html_e( 'Set a Backup Connection', 'wp-mail-smtp' ); ?></li>
					</ul>
					<ul>
						<li><?php esc_html_e( 'Use mailers for different purposes', 'wp-mail-smtp' ); ?></li>
					</ul>
					<ul>
						<li><?php esc_html_e( 'Create advanced routing rules', 'wp-mail-smtp' ); ?></li>
					</ul>
				</div>
			</div>
		</div>
		<?php
	}
}
