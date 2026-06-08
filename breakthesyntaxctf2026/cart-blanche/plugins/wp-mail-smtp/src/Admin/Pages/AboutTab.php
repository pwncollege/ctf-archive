<?php

namespace WPMailSMTP\Admin\Pages;

use Plugin_Upgrader;
use WPMailSMTP\Admin\PageAbstract;
use WPMailSMTP\Admin\PluginsInstallSkin;
use WPMailSMTP\Helpers\Helpers;

/**
 * About tab.
 *
 * @since 2.9.0
 */
class AboutTab extends PageAbstract {

	/**
	 * Part of the slug of a tab.
	 *
	 * @since 2.9.0
	 *
	 * @var string
	 */
	protected $slug = 'about';

	/**
	 * Tab priority.
	 *
	 * @since 2.9.0
	 *
	 * @var int
	 */
	protected $priority = 20;

	/**
	 * Link label of a tab.
	 *
	 * @since 2.9.0
	 *
	 * @return string
	 */
	public function get_label() {

		return esc_html__( 'About Us', 'wp-mail-smtp' );
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
	 * Tab content.
	 *
	 * @since 2.9.0
	 */
	public function display() {

		?>
		<div class="wp-mail-smtp-admin-about-section wp-mail-smtp-admin-columns">

			<div class="wp-mail-smtp-admin-column-60">
				<h3>
					<?php esc_html_e( 'Hello and welcome to WP Mail SMTP, the easiest and most popular WordPress SMTP plugin. We build software that helps your site reliably deliver emails every time.', 'wp-mail-smtp' ); ?>
				</h3>

				<p>
					<?php esc_html_e( 'Email deliverability has been a well-documented problem for all WordPress websites. However as WPForms grew, we became more aware of this painful issue that affects our users and the larger WordPress community. So we decided to solve this problem and make a solution that\'s beginner friendly.', 'wp-mail-smtp' ); ?>
				</p>
				<p>
					<?php esc_html_e( 'Our goal is to make reliable email deliverability easy for WordPress.', 'wp-mail-smtp' ); ?>
				</p>
				<p>
					<?php
					printf(
						wp_kses(
						/* translators: %1$s - WPForms URL, %2$s - WPBeginner URL, %3$s - OptinMonster URL, %4$s - MonsterInsights URL, %5$s - Awesome Motive URL */
							__( 'WP Mail SMTP is brought to you by the same team that\'s behind the most user friendly WordPress forms, <a href="%1$s" target="_blank" rel="noopener noreferrer">WPForms</a>, the largest WordPress resource site, <a href="%2$s" target="_blank" rel="noopener noreferrer">WPBeginner</a>, the most popular lead-generation software, <a href="%3$s" target="_blank" rel="noopener noreferrer">OptinMonster</a>, the best WordPress analytics plugin, <a href="%4$s" target="_blank" rel="noopener noreferrer">MonsterInsights</a>, and <a href="%5$s" target="_blank" rel="noopener noreferrer">more</a>.', 'wp-mail-smtp' ),
							[
								'a' => [
									'href'   => [],
									'rel'    => [],
									'target' => [],
								],
							]
						),
						'https://wpforms.com/?utm_source=wpmailsmtpplugin&utm_medium=pluginaboutpage&utm_campaign=aboutwpmailsmtp',
						'https://www.wpbeginner.com/?utm_source=wpmailsmtpplugin&utm_medium=pluginaboutpage&utm_campaign=aboutwpmailsmtp',
						'https://optinmonster.com/?utm_source=wpmailsmtpplugin&utm_medium=pluginaboutpage&utm_campaign=aboutwpmailsmtp',
						'https://www.monsterinsights.com/?utm_source=wpmailsmtpplugin&utm_medium=pluginaboutpage&utm_campaign=aboutwpmailsmtp',
						// phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
						esc_url( wp_mail_smtp()->get_utm_url( 'https://awesomemotive.com/', [ 'medium' => 'pluginaboutpage', 'content' => 'aboutwpmailsmtp' ] ) )
					);
					?>
				</p>
				<p>
					<?php esc_html_e( 'Yup, we know a thing or two about building awesome products that customers love.', 'wp-mail-smtp' ); ?>
				</p>
			</div>

			<div class="wp-mail-smtp-admin-column-40 wp-mail-smtp-admin-column-last">
				<figure>
					<img src="<?php echo esc_url( wp_mail_smtp()->assets_url . '/images/about/team.jpg' ); ?>" alt="<?php esc_attr_e( 'The WPForms Team photo', 'wp-mail-smtp' ); ?>">
					<figcaption>
						<?php esc_html_e( 'The WPForms Team', 'wp-mail-smtp' ); ?>
					</figcaption>
				</figure>
			</div>

		</div>

		<?php

		// Do not display the plugin section if the user can't install or activate them.
		if ( ! current_user_can( 'install_plugins' ) && ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		$this->display_plugins();
	}

	/**
	 * Display the plugins section.
	 *
	 * @since 2.9.0
	 */
	protected function display_plugins() {

		?>
		<div class="wp-mail-smtp-admin-about-plugins">
			<div class="plugins-container">
				<?php
				foreach ( self::get_am_plugins() as $key => $plugin ) :
					$is_url_external = false;

					$data = $this->get_about_plugins_data( $plugin );

					if ( isset( $plugin['pro'] ) && array_key_exists( $plugin['pro']['path'], get_plugins() ) ) {
						$is_url_external = true;
						$plugin          = $plugin['pro'];

						$data = array_merge( $data, $this->get_about_plugins_data( $plugin, true ) );
					}

					// Do not display a plugin which has to be installed and the user can't install it.
					if ( ! current_user_can( 'install_plugins' ) && $data['status_class'] === 'status-download' ) {
						continue;
					}

					?>
					<div class="plugin-container">
						<div class="plugin-item">
							<div class="details wp-mail-smtp-clear">
								<img src="<?php echo esc_url( $plugin['icon'] ); ?>" alt="<?php esc_attr_e( 'Plugin icon', 'wp-mail-smtp' ); ?>">
								<h5 class="plugin-name">
									<?php echo esc_html( $plugin['name'] ); ?>
								</h5>
								<p class="plugin-desc">
									<?php echo esc_html( $plugin['desc'] ); ?>
								</p>
							</div>
							<div class="actions wp-mail-smtp-clear">
								<div class="status">
									<strong>
										<?php
										printf(
										/* translators: %s - status HTML text. */
											esc_html__( 'Status: %s', 'wp-mail-smtp' ),
											'<span class="status-label ' . esc_attr( $data['status_class'] ) . '">' . esc_html( $data['status_text'] ) . '</span>'
										);
										?>
									</strong>
								</div>
								<div class="action-button">
									<?php
									$go_to_class = '';
									if ( $is_url_external && $data['status_class'] === 'status-download' ) {
										$go_to_class = ' go_to';
									}
									?>
									<a href="<?php echo esc_url( $plugin['url'] ); ?>"
										 class="<?php echo esc_attr( $data['action_class'] . $go_to_class ); ?>"
										 data-plugin="<?php echo esc_attr( $data['plugin_src'] ); ?>">
										<?php echo esc_html( $data['action_text'] ); ?>
									</a>
								</div>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<?php
	}

	/**
	 * Generate all the required CSS classed and labels to be used in rendering.
	 *
	 * @since 2.9.0
	 *
	 * @param array $plugin Plugin slug.
	 * @param bool  $is_pro License type.
	 *
	 * @return mixed
	 */
	protected function get_about_plugins_data( $plugin, $is_pro = false ) {

		$data = [];

		if ( array_key_exists( $plugin['path'], get_plugins() ) ) {
			if ( is_plugin_active( $plugin['path'] ) ) {
				// Status text/status.
				$data['status_class'] = 'status-active';
				$data['status_text']  = esc_html__( 'Active', 'wp-mail-smtp' );
				// Button text/status.
				$data['action_class'] = $data['status_class'] . ' button button-secondary disabled';
				$data['action_text']  = esc_html__( 'Activated', 'wp-mail-smtp' );
				$data['plugin_src']   = esc_attr( $plugin['path'] );
			} else {
				// Status text/status.
				$data['status_class'] = 'status-inactive';
				$data['status_text']  = esc_html__( 'Inactive', 'wp-mail-smtp' );
				// Button text/status.
				$data['action_class'] = $data['status_class'] . ' button button-secondary';
				$data['action_text']  = esc_html__( 'Activate', 'wp-mail-smtp' );
				$data['plugin_src']   = esc_attr( $plugin['path'] );
			}
		} else {
			if ( ! $is_pro ) {
				// Doesn't exist, install.
				// Status text/status.
				$data['status_class'] = 'status-download';
				$data['status_text']  = esc_html__( 'Not Installed', 'wp-mail-smtp' );
				// Button text/status.
				$data['action_class'] = $data['status_class'] . ' button button-primary';
				$data['action_text']  = esc_html__( 'Install Plugin', 'wp-mail-smtp' );
				$data['plugin_src']   = esc_url( $plugin['url'] );

				// If plugin URL is not a zip file, open a new tab with site URL.
				if ( preg_match( '/.*\.zip$/', $plugin['url'] ) === 0 ) {
					$data['status_class'] = 'status-open';
					$data['action_class'] = $data['status_class'] . ' button button-primary';
					$data['action_text']  = esc_html__( 'Visit Site', 'wp-mail-smtp' );
				}
			}
		}

		return $data;
	}

	/**
	 * List of AM plugins that we propose to install.
	 *
	 * @since 2.9.0
	 *
	 * @return array
	 */
	private static function get_am_plugins() {

		$data = [
			'om'                            => [
				'path' => 'optinmonster/optin-monster-wp-api.php',
				'icon' => wp_mail_smtp()->assets_url . '/images/about/plugin-om.png',
				'name' => esc_html__( 'OptinMonster', 'wp-mail-smtp' ),
				'desc' => esc_html__( 'Instantly get more subscribers, leads, and sales with the #1 conversion optimization toolkit. Create high converting popups, announcement bars, spin a wheel, and more with smart targeting and personalization.', 'wp-mail-smtp' ),
				'url'  => 'https://downloads.wordpress.org/plugin/optinmonster.zip',
			],
			'wpforms'                       => [
				'path' => 'wpforms-lite/wpforms.php',
				'icon' => wp_mail_smtp()->assets_url . '/images/about/plugin-wpf.png',
				'name' => esc_html__( 'WPForms', 'wp-mail-smtp' ),
				'desc' => esc_html__( 'The best drag & drop WordPress form builder. Easily create beautiful contact forms, surveys, payment forms, and more with our 600+ form templates. Trusted by over 5 million websites as the best forms plugin.', 'wp-mail-smtp' ),
				'url'  => 'https://downloads.wordpress.org/plugin/wpforms-lite.zip',
				'pro'  => [
					'path' => 'wpforms/wpforms.php',
					'icon' => wp_mail_smtp()->assets_url . '/images/about/plugin-wpf.png',
					'name' => esc_html__( 'WPForms Pro', 'wp-mail-smtp' ),
					'desc' => esc_html__( 'The best drag & drop WordPress form builder. Easily create beautiful contact forms, surveys, payment forms, and more with our 600+ form templates. Trusted by over 5 million websites as the best forms plugin.', 'wp-mail-smtp' ),
					'url'  => 'https://wpforms.com/?utm_source=WordPress&utm_medium=about&utm_campaign=smtp',
				],
			],
			'mi'                            => [
				'path' => 'google-analytics-for-wordpress/googleanalytics.php',
				'icon' => wp_mail_smtp()->assets_url . '/images/about/plugin-mi.png',
				'name' => esc_html__( 'MonsterInsights', 'wp-mail-smtp' ),
				'desc' => esc_html__( 'The leading WordPress analytics plugin that shows you how people find and use your website, so you can make data driven decisions to grow your business. Properly set up Google Analytics without writing code.', 'wp-mail-smtp' ),
				'url'  => 'https://downloads.wordpress.org/plugin/google-analytics-for-wordpress.zip',
				'pro'  => [
					'path' => 'google-analytics-premium/googleanalytics-premium.php',
					'icon' => wp_mail_smtp()->assets_url . '/images/about/plugin-mi.png',
					'name' => esc_html__( 'MonsterInsights Pro', 'wp-mail-smtp' ),
					'desc' => esc_html__( 'The leading WordPress analytics plugin that shows you how people find and use your website, so you can make data driven decisions to grow your business. Properly set up Google Analytics without writing code.', 'wp-mail-smtp' ),
					'url'  => 'https://www.monsterinsights.com/?utm_source=WordPress&utm_medium=about&utm_campaign=smtp',
				],
			],
			'aioseo'                        => [
				'path' => 'all-in-one-seo-pack/all_in_one_seo_pack.php',
				'icon' => wp_mail_smtp()->assets_url . '/images/about/plugin-aioseo.png',
				'name' => esc_html__( 'AIOSEO', 'wp-mail-smtp' ),
				'desc' => esc_html__( 'The original WordPress SEO plugin and toolkit that improves your website’s search rankings. Comes with all the SEO features like Local SEO, WooCommerce SEO, sitemaps, SEO optimizer, schema, and more.', 'wp-mail-smtp' ),
				'url'  => 'https://downloads.wordpress.org/plugin/all-in-one-seo-pack.zip',
				'pro'  => [
					'path' => 'all-in-one-seo-pack-pro/all_in_one_seo_pack.php',
					'icon' => wp_mail_smtp()->assets_url . '/images/about/plugin-aioseo.png',
					'name' => esc_html__( 'AIOSEO', 'wp-mail-smtp' ),
					'desc' => esc_html__( 'The original WordPress SEO plugin and toolkit that improves your website’s search rankings. Comes with all the SEO features like Local SEO, WooCommerce SEO, sitemaps, SEO optimizer, schema, and more.', 'wp-mail-smtp' ),
					'url'  => 'https://aioseo.com/?utm_source=WordPress&utm_medium=about&utm_campaign=smtp',
				],
			],
			'seedprod'                      => [
				'path' => 'coming-soon/coming-soon.php',
				'icon' => wp_mail_smtp()->assets_url . '/images/about/plugin-seedprod.png',
				'name' => esc_html__( 'SeedProd', 'wp-mail-smtp' ),
				'desc' => esc_html__( 'The fastest drag & drop landing page builder for WordPress. Create custom landing pages without writing code, connect them with your CRM, collect subscribers, and grow your audience. Trusted by 1 million sites.', 'wp-mail-smtp' ),
				'url'  => 'https://downloads.wordpress.org/plugin/coming-soon.zip',
				'pro'  => [
					'path' => 'seedprod-coming-soon-pro-5/seedprod-coming-soon-pro-5.php',
					'icon' => wp_mail_smtp()->assets_url . '/images/about/plugin-seedprod.png',
					'name' => esc_html__( 'SeedProd', 'wp-mail-smtp' ),
					'desc' => esc_html__( 'The fastest drag & drop landing page builder for WordPress. Create custom landing pages without writing code, connect them with your CRM, collect subscribers, and grow your audience. Trusted by 1 million sites.', 'wp-mail-smtp' ),
					'url'  => 'https://www.seedprod.com/?utm_source=WordPress&utm_medium=about&utm_campaign=smtp',
				],
			],
			'rafflepress'                   => [
				'path' => 'rafflepress/rafflepress.php',
				'icon' => wp_mail_smtp()->assets_url . '/images/about/plugin-rp.png',
				'name' => esc_html__( 'RafflePress', 'wp-mail-smtp' ),
				'desc' => esc_html__( 'Turn your website visitors into brand ambassadors! Easily grow your email list, website traffic, and social media followers with the most powerful giveaways & contests plugin for WordPress.', 'wp-mail-smtp' ),
				'url'  => 'https://downloads.wordpress.org/plugin/rafflepress.zip',
				'pro'  => [
					'path' => 'rafflepress-pro/rafflepress-pro.php',
					'icon' => wp_mail_smtp()->assets_url . '/images/about/plugin-rp.png',
					'name' => esc_html__( 'RafflePress Pro', 'wp-mail-smtp' ),
					'desc' => esc_html__( 'Turn your website visitors into brand ambassadors! Easily grow your email list, website traffic, and social media followers with the most powerful giveaways & contests plugin for WordPress.', 'wp-mail-smtp' ),
					'url'  => 'https://rafflepress.com/pricing/?utm_source=WordPress&utm_medium=about&utm_campaign=smtp',
				],
			],
			'pushengage'                    => [
				'path' => 'pushengage/main.php',
				'icon' => wp_mail_smtp()->assets_url . '/images/about/plugin-pushengage.png',
				'name' => esc_html__( 'PushEngage', 'wp-mail-smtp' ),
				'desc' => esc_html__( 'Connect with your visitors after they leave your website with the leading web push notification software. Over 10,000+ businesses worldwide use PushEngage to send 15 billion notifications each month.', 'wp-mail-smtp' ),
				'url'  => 'https://downloads.wordpress.org/plugin/pushengage.zip',
			],
			'smash-balloon-instagram-feeds' => [
				'path' => 'instagram-feed/instagram-feed.php',
				'icon' => wp_mail_smtp()->assets_url . '/images/about/plugin-smash-balloon-instagram-feeds.png',
				'name' => esc_html__( 'Smash Balloon Instagram Feeds', 'wp-mail-smtp' ),
				'desc' => esc_html__( 'Easily display Instagram content on your WordPress site without writing any code. Comes with multiple templates, ability to show content from multiple accounts, hashtags, and more. Trusted by 1 million websites.', 'wp-mail-smtp' ),
				'url'  => 'https://downloads.wordpress.org/plugin/instagram-feed.zip',
				'pro'  => [
					'path' => 'instagram-feed-pro/instagram-feed.php',
					'icon' => wp_mail_smtp()->assets_url . '/images/about/plugin-smash-balloon-instagram-feeds.png',
					'name' => esc_html__( 'Smash Balloon Instagram Feeds', 'wp-mail-smtp' ),
					'desc' => esc_html__( 'Easily display Instagram content on your WordPress site without writing any code. Comes with multiple templates, ability to show content from multiple accounts, hashtags, and more. Trusted by 1 million websites.', 'wp-mail-smtp' ),
					'url'  => 'https://smashballoon.com/instagram-feed/?utm_source=WordPress&utm_medium=about&utm_campaign=smtp',
				],
			],
			'smash-balloon-facebook-feeds'  => [
				'path' => 'custom-facebook-feed/custom-facebook-feed.php',
				'icon' => wp_mail_smtp()->assets_url . '/images/about/plugin-smash-balloon-facebook-feeds.png',
				'name' => esc_html__( 'Smash Balloon Facebook Feeds', 'wp-mail-smtp' ),
				'desc' => esc_html__( 'Easily display Facebook content on your WordPress site without writing any code. Comes with multiple templates, ability to embed albums, group content, reviews, live videos, comments, and reactions.', 'wp-mail-smtp' ),
				'url'  => 'https://downloads.wordpress.org/plugin/custom-facebook-feed.zip',
				'pro'  => [
					'path' => 'custom-facebook-feed-pro/custom-facebook-feed.php',
					'icon' => wp_mail_smtp()->assets_url . '/images/about/plugin-smash-balloon-facebook-feeds.png',
					'name' => esc_html__( 'Smash Balloon Facebook Feeds', 'wp-mail-smtp' ),
					'desc' => esc_html__( 'Easily display Facebook content on your WordPress site without writing any code. Comes with multiple templates, ability to embed albums, group content, reviews, live videos, comments, and reactions.', 'wp-mail-smtp' ),
					'url'  => 'https://smashballoon.com/custom-facebook-feed/?utm_source=WordPress&utm_medium=about&utm_campaign=smtp',
				],
			],
			'smash-balloon-youtube-feeds'   => [
				'path' => 'feeds-for-youtube/youtube-feed.php',
				'icon' => wp_mail_smtp()->assets_url . '/images/about/plugin-smash-balloon-youtube-feeds.png',
				'name' => esc_html__( 'Smash Balloon YouTube Feeds', 'wp-mail-smtp' ),
				'desc' => esc_html__( 'Easily display YouTube videos on your WordPress site without writing any code. Comes with multiple layouts, ability to embed live streams, video filtering, ability to combine multiple channel videos, and more.', 'wp-mail-smtp' ),
				'url'  => 'https://downloads.wordpress.org/plugin/feeds-for-youtube.zip',
				'pro'  => [
					'path' => 'youtube-feed-pro/youtube-feed.php',
					'icon' => wp_mail_smtp()->assets_url . '/images/about/plugin-smash-balloon-youtube-feeds.png',
					'name' => esc_html__( 'Smash Balloon YouTube Feeds', 'wp-mail-smtp' ),
					'desc' => esc_html__( 'Easily display YouTube videos on your WordPress site without writing any code. Comes with multiple layouts, ability to embed live streams, video filtering, ability to combine multiple channel videos, and more.', 'wp-mail-smtp' ),
					'url'  => 'https://smashballoon.com/youtube-feed/?utm_source=WordPress&utm_medium=about&utm_campaign=smtp',
				],
			],
			'smash-balloon-twitter-feeds'   => [
				'path' => 'custom-twitter-feeds/custom-twitter-feed.php',
				'icon' => wp_mail_smtp()->assets_url . '/images/about/plugin-smash-balloon-twitter-feeds.png',
				'name' => esc_html__( 'Smash Balloon Twitter Feeds', 'wp-mail-smtp' ),
				'desc' => esc_html__( 'Easily display Twitter content in WordPress without writing any code. Comes with multiple layouts, ability to combine multiple Twitter feeds, Twitter card support, tweet moderation, and more.', 'wp-mail-smtp' ),
				'url'  => 'https://downloads.wordpress.org/plugin/custom-twitter-feeds.zip',
				'pro'  => [
					'path' => 'custom-twitter-feeds-pro/custom-twitter-feed.php',
					'icon' => wp_mail_smtp()->assets_url . '/images/about/plugin-smash-balloon-twitter-feeds.png',
					'name' => esc_html__( 'Smash Balloon Twitter Feeds', 'wp-mail-smtp' ),
					'desc' => esc_html__( 'Easily display Twitter content in WordPress without writing any code. Comes with multiple layouts, ability to combine multiple Twitter feeds, Twitter card support, tweet moderation, and more.', 'wp-mail-smtp' ),
					'url'  => 'https://smashballoon.com/custom-twitter-feeds/?utm_source=WordPress&utm_medium=about&utm_campaign=smtp',
				],
			],
			'trustpulse'                    => [
				'path' => 'trustpulse-api/trustpulse.php',
				'icon' => wp_mail_smtp()->assets_url . '/images/about/plugin-trustpulse.png',
				'name' => esc_html__( 'TrustPulse', 'wp-mail-smtp' ),
				'desc' => esc_html__( 'Boost your sales and conversions by up to 15% with real-time social proof notifications. TrustPulse helps you show live user activity and purchases to help convince other users to purchase.', 'wp-mail-smtp' ),
				'url'  => 'https://downloads.wordpress.org/plugin/trustpulse-api.zip',
			],
			'searchwp'                      => [
				'path' => '',
				'icon' => wp_mail_smtp()->assets_url . '/images/about/searchwp.png',
				'name' => esc_html__( 'SearchWP', 'wp-mail-smtp' ),
				'desc' => esc_html__( 'The most advanced WordPress search plugin. Customize your WordPress search algorithm, reorder search results, track search metrics, and everything you need to leverage search to grow your business.', 'wp-mail-smtp' ),
				'url'  => 'https://searchwp.com/?utm_source=WordPress&utm_medium=about&utm_campaign=smtp',
				'pro'  => [
					'path' => 'searchwp/index.php',
					'icon' => wp_mail_smtp()->assets_url . '/images/about/searchwp.png',
					'name' => esc_html__( 'SearchWP', 'wp-mail-smtp' ),
					'desc' => esc_html__( 'The most advanced WordPress search plugin. Customize your WordPress search algorithm, reorder search results, track search metrics, and everything you need to leverage search to grow your business.', 'wp-mail-smtp' ),
					'url'  => 'https://searchwp.com/?utm_source=WordPress&utm_medium=about&utm_campaign=smtp',
				],
			],
			'affiliatewp'                   => [
				'path' => '',
				'icon' => wp_mail_smtp()->assets_url . '/images/about/affiliatewp.png',
				'name' => esc_html__( 'AffiliateWP', 'wp-mail-smtp' ),
				'desc' => esc_html__( 'The #1 affiliate management plugin for WordPress. Easily create an affiliate program for your eCommerce store or membership site within minutes and start growing your sales with the power of referral marketing.', 'wp-mail-smtp' ),
				'url'  => 'https://affiliatewp.com/?utm_source=WordPress&utm_medium=about&utm_campaign=smtp',
				'pro'  => [
					'path' => 'affiliate-wp/affiliate-wp.php',
					'icon' => wp_mail_smtp()->assets_url . '/images/about/affiliatewp.png',
					'name' => esc_html__( 'AffiliateWP', 'wp-mail-smtp' ),
					'desc' => esc_html__( 'The #1 affiliate management plugin for WordPress. Easily create an affiliate program for your eCommerce store or membership site within minutes and start growing your sales with the power of referral marketing.', 'wp-mail-smtp' ),
					'url'  => 'https://affiliatewp.com/?utm_source=WordPress&utm_medium=about&utm_campaign=smtp',
				],
			],
			'wp-simple-pay'                 => [
				'path' => 'stripe/stripe-checkout.php',
				'icon' => wp_mail_smtp()->assets_url . '/images/about/wp-simple-pay.png',
				'name' => esc_html__( 'WP Simple Pay', 'wp-mail-smtp' ),
				'desc' => esc_html__( 'The #1 Stripe payments plugin for WordPress. Start accepting one-time and recurring payments on your WordPress site without setting up a shopping cart. No code required.', 'wp-mail-smtp' ),
				'url'  => 'https://downloads.wordpress.org/plugin/stripe.zip',
				'pro'  => [
					'path' => 'wp-simple-pay-pro-3/simple-pay.php',
					'icon' => wp_mail_smtp()->assets_url . '/images/about/wp-simple-pay.png',
					'name' => esc_html__( 'WP Simple Pay Pro', 'wp-mail-smtp' ),
					'desc' => esc_html__( 'The #1 Stripe payments plugin for WordPress. Start accepting one-time and recurring payments on your WordPress site without setting up a shopping cart. No code required.', 'wp-mail-smtp' ),
					'url'  => 'https://wpsimplepay.com/?utm_source=WordPress&utm_medium=about&utm_campaign=smtp',
				],
			],
			'easy-digital-downloads'        => [
				'path' => 'easy-digital-downloads/easy-digital-downloads.php',
				'icon' => wp_mail_smtp()->assets_url . '/images/about/edd.png',
				'name' => esc_html__( 'Easy Digital Downloads', 'wp-mail-smtp' ),
				'desc' => esc_html__( 'The best WordPress eCommerce plugin for selling digital downloads. Start selling eBooks, software, music, digital art, and more within minutes. Accept payments, manage subscriptions, advanced access control, and more.', 'wp-mail-smtp' ),
				'url'  => 'https://downloads.wordpress.org/plugin/easy-digital-downloads.zip',
			],
			'sugar-calendar'                => [
				'path' => 'sugar-calendar-lite/sugar-calendar-lite.php',
				'icon' => wp_mail_smtp()->assets_url . '/images/about/sugar-calendar.png',
				'name' => esc_html__( 'Sugar Calendar Lite', 'wp-mail-smtp' ),
				'desc' => esc_html__( 'A simple & powerful event calendar plugin for WordPress that comes with all the event management features including payments, scheduling, timezones, ticketing, recurring events, and more.', 'wp-mail-smtp' ),
				'url'  => 'https://downloads.wordpress.org/plugin/sugar-calendar-lite.zip',
				'pro'  => [
					'path' => 'sugar-calendar/sugar-calendar.php',
					'icon' => wp_mail_smtp()->assets_url . '/images/about/sugar-calendar.png',
					'name' => esc_html__( 'Sugar Calendar', 'wp-mail-smtp' ),
					'desc' => esc_html__( 'A simple & powerful event calendar plugin for WordPress that comes with all the event management features including payments, scheduling, timezones, ticketing, recurring events, and more.', 'wp-mail-smtp' ),
					'url'  => 'https://sugarcalendar.com/?utm_source=WordPress&utm_medium=about&utm_campaign=smtp',
				],
			],
			'wp-charitable'                 => [
				'path' => 'charitable/charitable.php',
				'icon' => wp_mail_smtp()->assets_url . '/images/about/plugin-charitable.png',
				'name' => esc_html__( 'Charitable', 'wp-mail-smtp' ),
				'desc' => esc_html__( 'Top-rated WordPress donation and fundraising plugin. Over 10,000+ non-profit organizations and website owners use Charitable to create fundraising campaigns and raise more money online.', 'wp-mail-smtp' ),
				'url'  => 'https://downloads.wordpress.org/plugin/charitable.zip',
			],
			'wpcode'                        => [
				'path' => 'insert-headers-and-footers/ihaf.php',
				'icon' => wp_mail_smtp()->assets_url . '/images/about/plugin-wpcode.png',
				'name' => esc_html__( 'WPCode Lite', 'wp-mail-smtp' ),
				'desc' => esc_html__( 'Future proof your WordPress customizations with the most popular code snippet management plugin for WordPress. Trusted by over 1,500,000+ websites for easily adding code to WordPress right from the admin area.', 'wp-mail-smtp' ),
				'url'  => 'https://downloads.wordpress.org/plugin/insert-headers-and-footers.zip',
				'pro'  => [
					'path' => 'wpcode-premium/wpcode.php',
					'icon' => wp_mail_smtp()->assets_url . '/images/about/plugin-wpcode.png',
					'name' => esc_html__( 'WPCode Pro', 'wp-mail-smtp' ),
					'desc' => esc_html__( 'Future proof your WordPress customizations with the most popular code snippet management plugin for WordPress. Trusted by over 1,500,000+ websites for easily adding code to WordPress right from the admin area.', 'wp-mail-smtp' ),
					'url'  => 'https://wpcode.com/?utm_source=WordPress&utm_medium=about&utm_campaign=smtp',
				],
			],
			'duplicator'                    => [
				'path' => 'duplicator/duplicator.php',
				'icon' => wp_mail_smtp()->assets_url . '/images/about/duplicator-icon-large.png',
				'name' => esc_html__( 'Duplicator', 'wp-mail-smtp' ),
				'desc' => esc_html__( 'Leading WordPress backup & site migration plugin. Over 1,500,000+ smart website owners use Duplicator to make reliable and secure WordPress backups to protect their websites. It also makes website migration really easy.', 'wp-mail-smtp' ),
				'url'  => 'https://downloads.wordpress.org/plugin/duplicator.zip',
				'pro'  => [
					'path' => 'duplicator-pro/duplicator-pro.php',
					'icon' => wp_mail_smtp()->assets_url . '/images/about/duplicator-icon-large.png',
					'name' => esc_html__( 'Duplicator Pro', 'wp-mail-smtp' ),
					'desc' => esc_html__( 'Leading WordPress backup & site migration plugin. Over 1,500,000+ smart website owners use Duplicator to make reliable and secure WordPress backups to protect their websites. It also makes website migration really easy.', 'wp-mail-smtp' ),
					'url'  => 'https://duplicator.com/?utm_source=WordPress&utm_medium=about&utm_campaign=smtp',
				],
			],
		];

		return $data;
	}

	/**
	 * Active the given plugin.
	 *
	 * @since 2.9.0
	 */
	public static function ajax_plugin_activate() {

		$error = esc_html__( 'Could not activate the plugin. Please activate it from the Plugins page.', 'wp-mail-smtp' );

		// Check for permissions.
		if ( ! current_user_can( 'activate_plugins' ) ) {
			wp_send_json_error( $error );
		}

		if ( empty( $_POST['plugin'] ) ) {
			wp_send_json_error( $error );
		}

		$plugin_slug = sanitize_text_field( wp_unslash( $_POST['plugin'] ) );

		$whitelisted_plugins = [];

		foreach ( self::get_am_plugins() as $item ) {
			if ( ! empty( $item['path'] ) ) {
				$whitelisted_plugins[] = $item['path'];
			}

			if ( ! empty( $item['pro']['path'] ) ) {
				$whitelisted_plugins[] = $item['pro']['path'];
			}
		}

		if ( ! in_array( $plugin_slug, $whitelisted_plugins, true ) ) {
			wp_send_json_error( esc_html__( 'Could not activate the plugin. Plugin is not whitelisted.', 'wp-mail-smtp' ) );
		}

		$activate = activate_plugins( $plugin_slug );

		if ( ! is_wp_error( $activate ) ) {
			wp_send_json_success( esc_html__( 'Plugin activated.', 'wp-mail-smtp' ) );
		}

		wp_send_json_error( $error );
	}

	/**
	 * Install & activate the given plugin.
	 *
	 * @since 2.9.0
	 */
	public static function ajax_plugin_install() { // phpcs:ignore:Generic.Metrics.CyclomaticComplexity.TooHigh

		$error = esc_html__( 'Could not install the plugin.', 'wp-mail-smtp' );

		// Check for permissions.
		if ( ! current_user_can( 'install_plugins' ) ) {
			wp_send_json_error( $error );
		}

		if ( empty( $_POST['plugin'] ) ) {
			wp_send_json_error();
		}

		$plugin_url = esc_url_raw( wp_unslash( $_POST['plugin'] ) );

		if ( ! in_array( $plugin_url, wp_list_pluck( array_values( self::get_am_plugins() ), 'url' ) , true ) ) {
			wp_send_json_error( esc_html__( 'Could not install the plugin. Plugin is not whitelisted.', 'wp-mail-smtp' ) );
		}

		// Set the current screen to avoid undefined notices.
		set_current_screen( 'wp-mail-smtp_page_wp-mail-smtp-about' );

		// Prepare variables.
		$url = esc_url_raw(
			add_query_arg(
				[
					'page' => 'wp-mail-smtp-about',
				],
				admin_url( 'admin.php' )
			)
		);

		/*
		 * The `request_filesystem_credentials` function will output a credentials form in case of failure.
		 * We don't want that, since it will break AJAX response. So just hide output with a buffer.
		 */
		ob_start();
		// phpcs:ignore WPForms.Formatting.EmptyLineAfterAssigmentVariables.AddEmptyLine
		$creds = request_filesystem_credentials( $url, '', false, false, null );
		ob_end_clean();

		// Check for file system permissions.
		if ( false === $creds ) {
			wp_send_json_error( $error );
		}

		if ( ! WP_Filesystem( $creds ) ) {
			wp_send_json_error( $error );
		}

		// Do not allow WordPress to search/download translations, as this will break JS output.
		remove_action( 'upgrader_process_complete', [ 'Language_Pack_Upgrader', 'async_upgrade' ], 20 );

		// Import the plugin upgrader.
		Helpers::include_plugin_upgrader();

		// Create the plugin upgrader with our custom skin.
		$installer = new Plugin_Upgrader( new PluginsInstallSkin() );

		// Error check.
		if ( ! method_exists( $installer, 'install' ) ) {
			wp_send_json_error( $error );
		}

		$installer->install( $plugin_url );

		// Flush the cache and return the newly installed plugin basename.
		wp_cache_flush();

		if ( $installer->plugin_info() ) {

			$plugin_basename = $installer->plugin_info();

			if ( $plugin_basename === 'wpforms-lite/wpforms.php' ) {
				add_option( 'wpforms_installation_source', 'wp-mail-smtp-about-us' );
			}

			// Activate the plugin silently.
			$activated = activate_plugin( $plugin_basename );

			if ( ! is_wp_error( $activated ) ) {
				wp_send_json_success(
					[
						'msg'          => esc_html__( 'Plugin installed & activated.', 'wp-mail-smtp' ),
						'is_activated' => true,
						'basename'     => $plugin_basename,
					]
				);
			} else {
				wp_send_json_success(
					[
						'msg'          => esc_html__( 'Plugin installed.', 'wp-mail-smtp' ),
						'is_activated' => false,
						'basename'     => $plugin_basename,
					]
				);
			}
		}

		wp_send_json_error( $error );
	}
}
