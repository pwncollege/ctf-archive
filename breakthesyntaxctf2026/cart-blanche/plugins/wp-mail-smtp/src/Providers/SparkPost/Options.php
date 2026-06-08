<?php

namespace WPMailSMTP\Providers\SparkPost;

use WPMailSMTP\ConnectionInterface;
use WPMailSMTP\Helpers\UI;
use WPMailSMTP\Providers\OptionsAbstract;

/**
 * Class Options.
 *
 * @since 3.2.0
 */
class Options extends OptionsAbstract {

	/**
	 * Mailer slug.
	 *
	 * @since 3.2.0
	 */
	const SLUG = 'sparkpost';

	/**
	 * Options constructor.
	 *
	 * @since 3.2.0
	 *
	 * @param ConnectionInterface $connection The Connection object.
	 */
	public function __construct( $connection = null ) {

		$description = sprintf(
			wp_kses( /* translators: %1$s - URL to SparkPost website. */
				__( '<a href="%1$s" target="_blank" rel="noopener noreferrer">SparkPost</a> is a transactional email provider that\'s trusted by big brands and small businesses. It sends more than 4 trillion emails each year and reports 99.9%% uptime. You can get started with the free test account that lets you send up to 500 emails per month.', 'wp-mail-smtp' ) .
				'<br><br>' .
				/* translators: %2$s - URL to wpmailsmtp.com doc. */
				__( 'To get started, read our <a href="%2$s" target="_blank" rel="noopener noreferrer">SparkPost documentation</a>.', 'wp-mail-smtp' ),
				[
					'br' => true,
					'a'  => [
						'href'   => true,
						'rel'    => true,
						'target' => true,
					],
				]
			),
			'https://www.sparkpost.com/',
			esc_url( wp_mail_smtp()->get_utm_url( 'https://wpmailsmtp.com/docs/how-to-set-up-the-sparkpost-mailer-in-wp-mail-smtp/', 'SparkPost documentation' ) )
		);

		parent::__construct(
			[
				'logo_url'    => wp_mail_smtp()->assets_url . '/images/providers/sparkpost.svg',
				'slug'        => self::SLUG,
				'title'       => esc_html__( 'SparkPost', 'wp-mail-smtp' ),
				'php'         => '5.6',
				'description' => $description,
				'supports'    => [
					'from_email'       => true,
					'from_name'        => true,
					'return_path'      => false,
					'from_email_force' => true,
					'from_name_force'  => true,
				],
				'recommended' => false,
			],
			$connection
		);
	}

	/**
	 * Output the mailer provider options.
	 *
	 * @since 3.2.0
	 */
	public function display_options() {

		// Do not display options if PHP version is not correct.
		if ( ! $this->is_php_correct() ) {
			$this->display_php_warning();
			return;
		}
		?>

		<!-- API Key -->
		<div id="wp-mail-smtp-setting-row-<?php echo esc_attr( $this->get_slug() ); ?>-api_key" class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-text wp-mail-smtp-clear">
			<div class="wp-mail-smtp-setting-label">
				<label for="wp-mail-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-api_key"><?php esc_html_e( 'API Key', 'wp-mail-smtp' ); ?></label>
			</div>
			<div class="wp-mail-smtp-setting-field">
				<?php if ( $this->connection_options->is_const_defined( $this->get_slug(), 'api_key' ) ) : ?>
					<input type="text" disabled value="****************************************"
								 id="wp-mail-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-api_key"
					/>
					<?php $this->display_const_set_message( 'WPMS_SPARKPOST_API_KEY' ); ?>
				<?php else : ?>
					<?php
					$slug  = $this->get_slug();
					$value = $this->connection_options->get( $this->get_slug(), 'api_key' );

					UI::hidden_password_field(
						[
							'name'       => "wp-mail-smtp[{$slug}][api_key]",
							'id'         => "wp-mail-smtp-setting-{$slug}-api_key",
							'value'      => $value,
							'clear_text' => esc_html__( 'Remove API Key', 'wp-mail-smtp' ),
						]
					);
					?>
				<?php endif; ?>
				<p class="desc">
					<?php
					$url = 'sparkpost.com';
					$url = $this->connection_options->get( $this->get_slug(), 'region' ) === 'EU' ? 'eu.' . $url : $url;
					$url = 'https://app.' . $url . '/account/api-keys';

					printf( /* translators: %s - API Key link. */
						esc_html__( 'Follow this link to get an API Key from SparkPost: %s.', 'wp-mail-smtp' ),
						'<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer">' .
							esc_html__( 'Get API Key', 'wp-mail-smtp' ) .
						'</a>'
					);
					?>
				</p>
			</div>
		</div>

		<!-- Region -->
		<div id="wp-mail-smtp-setting-row-<?php echo esc_attr( $this->get_slug() ); ?>-region" class="wp-mail-smtp-setting-row wp-mail-smtp-setting-row-radio wp-mail-smtp-clear">
			<div class="wp-mail-smtp-setting-label">
				<label for="wp-mail-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-region"><?php esc_html_e( 'Region', 'wp-mail-smtp' ); ?></label>
			</div>
			<div class="wp-mail-smtp-setting-field">

				<label for="wp-mail-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-region-us">
					<input type="radio" id="wp-mail-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-region-us"
								 name="wp-mail-smtp[<?php echo esc_attr( $this->get_slug() ); ?>][region]" value="US"
						<?php echo $this->connection_options->is_const_defined( $this->get_slug(), 'region' ) ? 'disabled' : ''; ?>
						<?php checked( 'US', $this->connection_options->get( $this->get_slug(), 'region' ) ); ?>
					/>
					<?php esc_html_e( 'US', 'wp-mail-smtp' ); ?>
				</label>

				<label for="wp-mail-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-region-eu">
					<input type="radio" id="wp-mail-smtp-setting-<?php echo esc_attr( $this->get_slug() ); ?>-region-eu"
								 name="wp-mail-smtp[<?php echo esc_attr( $this->get_slug() ); ?>][region]" value="EU"
						<?php echo $this->connection_options->is_const_defined( $this->get_slug(), 'region' ) ? 'disabled' : ''; ?>
						<?php checked( 'EU', $this->connection_options->get( $this->get_slug(), 'region' ) ); ?>
					/>
					<?php esc_html_e( 'EU', 'wp-mail-smtp' ); ?>
				</label>

				<?php
				if ( $this->connection_options->is_const_defined( $this->get_slug(), 'region' ) ) {
					$this->display_const_set_message( 'WPMS_SPARKPOST_REGION' );
				}
				?>
				<p class="desc">
					<?php esc_html_e( 'Select your SparkPost account region.', 'wp-mail-smtp' ); ?>
					<?php
					printf(
						wp_kses(
						/* translators: %s - URL to Mailgun.com page. */
							__( '<a href="%s" rel="" target="_blank">More information</a> on SparkPost.', 'wp-mail-smtp' ),
							[
								'a' => [
									'href'   => [],
									'rel'    => [],
									'target' => [],
								],
							]
						),
						'https://www.sparkpost.com/docs/getting-started/getting-started-sparkpost'
					);
					?>
				</p>
			</div>
		</div>

		<?php
	}
}
