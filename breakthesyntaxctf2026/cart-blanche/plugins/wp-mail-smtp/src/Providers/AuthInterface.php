<?php

namespace WPMailSMTP\Providers;

/**
 * Interface AuthInterface.
 *
 * @since 1.0.0
 */
interface AuthInterface {

	/**
	 * Whether user saved Client ID/App ID and Client Secret/App Password or not.
	 * Both options are required.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_clients_saved();

	/**
	 * Whether we have an access and refresh tokens or not.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_auth_required();
}
