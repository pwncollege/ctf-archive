<?php
// phpcs:ignoreFile WPForms.PHP.BackSlash.RemoveBackslash
namespace WPForms\Integrations\Stripe\Api;

use WPForms\Vendor\Stripe\Customer;
use WPForms\Vendor\Stripe\Plan;
use WPForms\Vendor\Stripe\Stripe;
use WPForms\Vendor\Stripe\Subscription;
use WPForms\Integrations\Stripe\Helpers;

/**
 * Common methods for every Stripe API implementation.
 *
 * @since 1.8.2
 */
abstract class Common {

	/**
	 * API configuration.
	 *
	 * @since 1.8.2
	 *
	 * @var array
	 */
	protected $config;

	/**
	 * Stripe customer object.
	 *
	 * @since 1.8.2
	 *
	 * @var Customer
	 */
	protected $customer;

	/**
	 * Stripe subscription object.
	 *
	 * @since 1.8.2
	 *
	 * @var Subscription
	 */
	protected $subscription;

	/**
	 * API error message.
	 *
	 * @since 1.8.2
	 *
	 * @var string
	 */
	protected $error;

	/**
	 * API exception.
	 *
	 * @since 1.8.2
	 *
	 * @var \Exception
	 */
	protected $exception;

	/**
	 * Get class variable value or its key.
	 *
	 * @since 1.8.2
	 *
	 * @param string $field Name of the variable to retrieve.
	 * @param string $key   Name of the key to retrieve.
	 *
	 * @return mixed
	 */
	protected function get_var( $field, $key = '' ) {

		$var = isset( $this->{$field} ) ? $this->{$field} : null;

		if ( ! $key ) {
			return $var;
		}

		if ( is_object( $var ) ) {
			return isset( $var->{$key} ) ? $var->{$key} : null;
		}

		if ( is_array( $var ) ) {
			return isset( $var[ $key ] ) ? $var[ $key ] : null;
		}

		return $var;
	}

	/**
	 * Get API configuration array or its key.
	 *
	 * @since 1.8.2
	 *
	 * @param string $key Name of the key to retrieve.
	 *
	 * @return mixed
	 */
	public function get_config( $key = '' ) {

		return $this->get_var( 'config', $key );
	}

	/**
	 * Get saved Stripe customer object or its key.
	 *
	 * @since 1.8.2
	 *
	 * @param string $key Name of the key to retrieve.
	 *
	 * @return mixed
	 */
	public function get_customer( $key = '' ) {

		return $this->get_var( 'customer', $key );
	}

	/**
	 * Get saved Stripe subscription object or its key.
	 *
	 * @since 1.8.2
	 *
	 * @param string $key Name of the key to retrieve.
	 *
	 * @return mixed
	 */
	public function get_subscription( $key = '' ) {

		return $this->get_var( 'subscription', $key );
	}

	/**
	 * Get API error message.
	 *
	 * @since 1.8.2
	 *
	 * @return string
	 */
	public function get_error() {

		return $this->get_var( 'error' );
	}

	/**
	 * Get API exception.
	 *
	 * @since 1.8.2
	 *
	 * @return \Exception
	 */
	public function get_exception() {

		return $this->get_var( 'exception' );
	}

	/**
	 * Initial Stripe app configuration.
	 *
	 * @since 1.8.2
	 */
	public function setup_stripe() {

		Stripe::setAppInfo(
			'WPForms acct_17Xt6qIdtRxnENqV',
			WPFORMS_VERSION,
			'https://wpforms.com/addons/stripe-addon/',
			'pp_partner_Dw7IkUZbIlCrtq'
		);
	}

	/**
	 * Set a customer object.
	 * Check if a customer exists in Stripe, if not creates one.
	 *
	 * @since 1.8.2
	 * @since 1.8.6 Added customer name argument and allow empty email.
	 * @since 1.8.8 Added customer billing address argument.
	 * @since 1.9.6 Added customer phone and metadata arguments.
	 *
	 * @param string $email    Email to fetch an existing customer.
	 * @param string $name     Customer name.
	 * @param array  $address  Customer billing address.
	 * @param string $phone    Customer phone number.
	 * @param array  $metadata Customer metadata.
	 */
	protected function set_customer( string $email = '', string $name = '', array $address = [], string $phone = '', array $metadata = [] ) {

		if ( ! $email && ! $name && ! $phone ) {
			return;
		}

		$args = [];

		if ( $name ) {
			$args['name'] = $name;
		}

		if ( $address ) {
			$args['address'] = $address;
		}

		if ( $phone ) {
			$args['phone'] = $phone;
		}

		if ( $metadata ) {
			$args['metadata'] = $metadata;
		}

		// Create a customer with name only if email is empty.
		if ( ! $email ) {

			try {
				$customer = Customer::create( $args, Helpers::get_auth_opts() );
			} catch ( \Exception $e ) {
				$customer = null;
			}

			if ( ! isset( $customer->id ) ) {
				return;
			}

			$this->customer = $customer;
			return;
		}

		// Retrieve a customer by email.
		try {
			$customers = Customer::all(
				[ 'email' => $email ],
				Helpers::get_auth_opts()
			);
		} catch ( \Exception $e ) {
			$customers = null;
		}

		// Determine whether the customer name/address needs to be updated.
		if ( isset( $customers->data[0]->id ) ) {
			$this->customer = $customers->data[0];

			$needUpdateName    = ! empty( $name ) && $name !== $this->customer->name;
			$needUpdatePhone   = ! empty( $phone ) && $phone !== $this->customer->phone;
			$needUpdateAddress = false;

			if ( ! $needUpdateName ) {
				$existingAddress   = isset( $this->customer->address ) && method_exists( $this->customer->address, 'toArray' ) ? $this->customer->address->toArray() : [];
				$needUpdateAddress = ! empty( array_diff_assoc( $address, $existingAddress ) );
			}

			// Update customer name/address/phone.
			if ( $needUpdateName || $needUpdateAddress || $needUpdatePhone ) {
				try {
					$this->customer = Customer::update(
						$this->customer->id,
						$args,
						Helpers::get_auth_opts()
					);
				} catch ( \Exception $e ) {
					wpforms_log(
						'Stripe: Unable to update customer information.',
						$e->getMessage(),
						[
							'type' => [ 'payment', 'error' ],
						]
					);
				}
			}

			return;
		}

		// Create a customer with email.
		try {
			$args['email'] = $email;

			$customer = Customer::create( $args, Helpers::get_auth_opts() );
		} catch ( \Exception $e ) {
			$customer = null;
		}

		if ( ! isset( $customer->id ) ) {
			return;
		}

		$this->customer = $customer;
	}

	/**
	 * Set an error message from a Stripe API exception.
	 *
	 * @since 1.8.2
	 *
	 * @param \Exception|\WPForms\Vendor\Stripe\Exception\ApiErrorException $e Stripe API exception to process.
	 */
	protected function set_error_from_exception( $e ) {

		/**
		 * WPForms set Stripe error from exception.
		 *
		 * @since 1.8.2
		 *
		 * @param \Exception|\WPForms\Vendor\Stripe\Exception\ApiErrorException $e Stripe API exception to process.
		 */
		do_action( 'wpformsstripe_api_common_set_error_from_exception', $e ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName

		if ( is_a( $e, '\WPForms\Vendor\Stripe\Exception\CardException' ) ) {
			$body        = $e->getJsonBody();
			$this->error = $body['error']['message'];

			return;
		}

		$errors = [
			'\WPForms\Vendor\Stripe\Exception\RateLimitException'      => esc_html__( 'Too many requests made to the API too quickly.', 'wpforms-lite' ),
			'\WPForms\Vendor\Stripe\Exception\InvalidRequestException' => esc_html__( 'Invalid parameters were supplied to Stripe API.', 'wpforms-lite' ),
			'\WPForms\Vendor\Stripe\Exception\AuthenticationException' => esc_html__( 'Authentication with Stripe API failed.', 'wpforms-lite' ),
			'\WPForms\Vendor\Stripe\Exception\ApiConnectionException'  => esc_html__( 'Network communication with Stripe failed.', 'wpforms-lite' ),
			'\WPForms\Vendor\Stripe\Exception\ApiErrorException'       => esc_html__( 'Unable to process Stripe payment.', 'wpforms-lite' ),
			'\Exception'                                => esc_html__( 'Unable to process payment.', 'wpforms-lite' ),
		];

		foreach ( $errors as $error_type => $error_message ) {

			if ( is_a( $e, $error_type ) ) {
				$this->error = $error_message;

				return;
			}
		}
	}

	/**
	 * Set an exception from a Stripe API exception.
	 *
	 * @since 1.8.2
	 *
	 * @param \Exception $e Stripe API exception to process.
	 */
	protected function set_exception( $e ) {

		$this->exception = $e;
	}

	/**
	 * Handle Stripe API exception.
	 *
	 * @since 1.8.2
	 *
	 * @param \Exception $e Stripe API exception to process.
	 */
	protected function handle_exception( $e ) {

		$this->set_exception( $e );
		$this->set_error_from_exception( $e );
	}

	/**
	 * Get data for every subscription period.
	 *
	 * @since 1.8.2
	 *
	 * @return array
	 */
	protected function get_subscription_period_data() {

		return [
			'daily'      => [
				'name'     => 'daily',
				'interval' => 'day',
				'count'    => 1,
				'desc'     => esc_html__( 'Daily', 'wpforms-lite' ),
			],
			'weekly'     => [
				'name'     => 'weekly',
				'interval' => 'week',
				'count'    => 1,
				'desc'     => esc_html__( 'Weekly', 'wpforms-lite' ),
			],
			'monthly'    => [
				'name'     => 'monthly',
				'interval' => 'month',
				'count'    => 1,
				'desc'     => esc_html__( 'Monthly', 'wpforms-lite' ),
			],
			'quarterly'  => [
				'name'     => 'quarterly',
				'interval' => 'month',
				'count'    => 3,
				'desc'     => esc_html__( 'Quarterly', 'wpforms-lite' ),
			],
			'semiyearly' => [
				'name'     => 'semiyearly',
				'interval' => 'month',
				'count'    => 6,
				'desc'     => esc_html__( 'Semi-Yearly', 'wpforms-lite' ),
			],
			'yearly'     => [
				'name'     => 'yearly',
				'interval' => 'year',
				'count'    => 1,
				'desc'     => esc_html__( 'Yearly', 'wpforms-lite' ),
			],
		];
	}

	/**
	 * Create Stripe plan.
	 *
	 * @since 1.8.2
	 *
	 * @param string $id     ID of a plan to create.
	 * @param array  $period Subscription period data.
	 * @param array  $args   Additional arguments.
	 *
	 * @return Plan|null
	 */
	protected function create_plan( $id, $period, $args ) {

		$name = sprintf(
			'%s (%s %s)',
			! empty( $args['settings']['name'] ) ? $args['settings']['name'] : $args['form_title'],
			$args['amount'],
			$period['desc']
		);

		/**
		 * Allow to filter Stripe subscription plan name.
		 *
		 * @since 1.8.8
		 *
		 * @param string $name   Plan name.
		 * @param array  $period Subscription period data.
		 * @param array  $args   Additional arguments.
		 */
		$name = (string) apply_filters( 'wpforms_integrations_stripe_api_common_create_plan_name', $name, $period, $args );

		$plan_args = [
			'amount'         => $args['amount'],
			'interval'       => $period['interval'],
			'interval_count' => $period['count'],
			'product'        => [
				'name' => sanitize_text_field( $name ),
			],
			'nickname'       => sanitize_text_field( $name ),
			'currency'       => strtolower( wpforms_get_currency() ),
			'id'             => $id,
			'metadata'       => [
				'form_name' => sanitize_text_field( $args['form_title'] ),
				'form_id'   => $args['form_id'],
			],
		];

		try {
			$plan = Plan::create( $plan_args, Helpers::get_auth_opts() );
		} catch ( \Exception $e ) {
			$plan = null;
		}

		return $plan;
	}

	/**
	 * Get Stripe plan ID.
	 * Check if a plan exists in Stripe, if not creates one.
	 *
	 * @since 1.8.2
	 *
	 * @param array $args Arguments needed for getting a valid plan ID.
	 *
	 * @return string
	 */
	protected function get_plan_id( $args ) {

		$period_data = $this->get_subscription_period_data();

		$period = array_key_exists( $args['settings']['period'], $period_data ) ? $period_data[ $args['settings']['period'] ] : $period_data['yearly'];

		if ( ! empty( $args['settings']['name'] ) ) {
			$slug = preg_replace( '/[^a-z0-9\-]/', '', strtolower( str_replace( ' ', '-', $args['settings']['name'] ) ) );
		} else {
			$slug = 'form' . $args['form_id'];
		}

		$plan_id = sprintf(
			'%s_%s_%s',
			$slug,
			$args['amount'],
			$period['name']
		);

		try {
			$plan = Plan::retrieve( $plan_id, Helpers::get_auth_opts() );
		} catch ( \Exception $e ) {
			$plan = $this->create_plan( $plan_id, $period, $args );
		}

		return isset( $plan->id ) ? $plan->id : '';
	}
}
