<?php

namespace WPForms\Integrations\Stripe\Api;

use Exception;
use WPForms\Helpers\File;
use WPForms\Vendor\Stripe\PaymentMethodDomain;
use WPForms\Integrations\Stripe\DomainHealthCheck;
use WPForms\Integrations\Stripe\Helpers;

/**
 * Domain Manager.
 *
 * @since 1.8.6
 */
class DomainManager {

	/**
	 * Domain status option name.
	 *
	 * @since 1.8.6
	 */
	const STATUS_OPTION = 'wpforms_stripe_domain_status';

	/**
	 * Active status.
	 *
	 * @since 1.8.6
	 */
	const STATUS_ACTIVE = 'active';

	/**
	 * Inactive status.
	 *
	 * @since 1.8.6
	 */
	const STATUS_INACTIVE = 'inactive';

	/**
	 * Validate domain.
	 *
	 * @since 1.8.6
	 *
	 * @return bool
	 */
	public function validate() {

		if ( $this->is_exists_and_valid() ) {
			$this->set_status( self::STATUS_ACTIVE );

			return true;
		}

		( new DomainHealthCheck() )->maybe_schedule_task();

		if (
			! $this->maybe_create_domain_association_file() ||
			! $this->register()
		) {
			$this->set_status( self::STATUS_INACTIVE );

			return false;
		}

		$this->set_status( self::STATUS_ACTIVE );

		return true;
	}

	/**
	 * Set status.
	 *
	 * @since 1.8.6
	 *
	 * @param string $status Status.
	 */
	private function set_status( $status ) {

		update_option( self::STATUS_OPTION, $status );
	}

	/**
	 * Determine whether domain is active.
	 *
	 * @since 1.8.6
	 *
	 * @return bool
	 */
	public function is_domain_active() {

		return get_option( self::STATUS_OPTION, self::STATUS_ACTIVE ) === self::STATUS_ACTIVE;
	}

	/**
	 * Register domain.
	 *
	 * @since 1.8.6
	 *
	 * @return bool
	 */
	private function register() {

		try {
			$domain = PaymentMethodDomain::create(
				[
					'domain_name' => $this->get_site_domain(),
				],
				Helpers::get_auth_opts()
			);
		} catch ( Exception $e ) {
			wpforms_log(
				'Stripe: Unable to create a domain.',
				$e->getMessage(),
				[
					'type' => [ 'payment', 'error' ],
				]
			);

			return false;
		}

		return $this->is_apple_pay_valid( $domain );
	}

	/**
	 * Check if domain already exists and valid.
	 *
	 * @since 1.8.6
	 *
	 * @return bool
	 */
	private function is_exists_and_valid() {

		try {
			$all_domains = PaymentMethodDomain::all(
				[
					'limit' => 100,
				],
				Helpers::get_auth_opts()
			);
		} catch ( Exception $e ) {
			wpforms_log(
				'Stripe: Unable to get list of domains.',
				$e->getMessage(),
				[
					'type' => [ 'payment', 'error' ],
				]
			);

			return false;
		}

		if ( empty( $all_domains ) || ! isset( $all_domains->data ) ) {
			return false;
		}

		$site_domain = $this->get_site_domain();

		foreach ( $all_domains->data as $domain ) {

			if ( $domain->domain_name !== $site_domain ) {
				continue;
			}

			if ( ! $this->is_apple_pay_valid( $domain ) ) {
				continue;
			}

			return true;
		}

		return false;
	}

	/**
	 * Verify if Apple Pay active and valid.
	 *
	 * @since 1.8.6
	 *
	 * @param object $domain Stripe domain object.
	 *
	 * @return bool
	 */
	private function is_apple_pay_valid( $domain ) {

		return isset( $domain->apple_pay ) && $domain->apple_pay->status === 'active';
	}

	/**
	 * Get site domain.
	 *
	 * @since 1.8.6
	 *
	 * @return string
	 */
	private function get_site_domain() {

		$site_url_parts = wp_parse_url( site_url() );

		return $site_url_parts['host'];
	}

	/**
	 * Maybe create domain association file.
	 *
	 * @since 1.8.6
	 *
	 * @return bool
	 */
	private function maybe_create_domain_association_file() {

		$wp_filesystem = File::get_filesystem();

		if ( is_null( $wp_filesystem ) ) {
			return false;
		}

		$association_dir  = $wp_filesystem->abspath() . '.well-known';
		$file_name        = 'apple-developer-merchantid-domain-association';
		$association_file = $association_dir . '/' . $file_name;

		// Return early if file already exists.
		if ( $wp_filesystem->exists( $association_file ) ) {
			return true;
		}

		if ( ! $wp_filesystem->mkdir( $association_dir, 0755 ) ) {
			$this->log_error( 'Stripe: Unable to create domain association folder in site root.' );

			return false;
		}

		if ( ! $wp_filesystem->copy( WPFORMS_PLUGIN_DIR . 'src/Integrations/Stripe/' . $file_name, $association_file, true ) ) {
			$this->log_error( 'Stripe: Unable to copy domain association file to domain .well-known directory.' );

			return false;
		}

		return true;
	}

	/**
	 * Log error message.
	 *
	 * @since 1.8.6
	 *
	 * @param string $error Error message.
	 */
	private function log_error( $error ) {

		wpforms_log(
			$error,
			'',
			[
				'type' => [ 'payment', 'error' ],
			]
		);
	}
}
