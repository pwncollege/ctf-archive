<?php

namespace WPForms\Helpers;

use WPForms\Tasks\Tasks;

/**
 * Remote data cache handler.
 *
 * Usage example in `WPForms\Admin\Addons\AddonsCache` and `WPForms\Admin\Builder\TemplatesCache`.
 *
 * @since 1.6.8
 */
abstract class CacheBase {

	/**
	 * Encrypt a cached file.
	 *
	 * @since 1.8.7
	 */
	protected const ENCRYPT = false;

	/**
	 * Request lock time, min.
	 *
	 * @since 1.8.7
	 */
	private const REQUEST_LOCK_TIME = 15;

	/**
	 * A class id or array of cache class ids to sync updates with.
	 *
	 * @since 1.8.9
	 */
	protected const SYNC_WITH = [];

	/**
	 * The current class is syncing updates now.
	 *
	 * @since 1.8.9
	 *
	 * @var bool
	 */
	private $syncing_updates = false;

	/**
	 * Indicates whether the cache was updated during the current run.
	 *
	 * @since 1.6.8
	 *
	 * @var bool
	 */
	protected $updated = false;

	/**
	 * Settings.
	 *
	 * @since 1.6.8
	 *
	 * @var array
	 */
	protected $settings;

	/**
	 * Cache key.
	 *
	 * @since 1.8.2
	 *
	 * @var string
	 */
	private $cache_key;

	/**
	 * Cache dir.
	 *
	 * @since 1.8.2
	 *
	 * @var string
	 */
	private $cache_dir;

	/**
	 * Cache file.
	 *
	 * @since 1.8.2
	 *
	 * @var string
	 */
	private $cache_file;

	/**
	 * Determine if the class is allowed to load.
	 *
	 * @since 1.6.8
	 *
	 * @return bool
	 */
	abstract protected function allow_load();

	/**
	 * Initialize.
	 *
	 * @since 1.6.8
	 */
	public function init() {

		// Init settings before allow_load() as settings are used in get().
		$this->update_settings();

		$this->cache_key  = $this->settings['cache_file'];
		$this->cache_dir  = $this->get_cache_dir(); // See comment in the method.
		$this->cache_file = $this->cache_dir . $this->settings['cache_file'];

		// Do not update caches on heartbeat events.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$action = isset( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : '';

		if ( $action === 'heartbeat' ) {
			return;
		}

		if ( ! $this->allow_load() ) {
			return;
		}

		// Quit if settings weren't provided.
		if (
			empty( $this->settings['remote_source'] ) ||
			empty( $this->settings['cache_file'] )
		) {
			return;
		}

		$this->hooks();
	}

	/**
	 * Base hooks.
	 *
	 * @since 1.6.8
	 */
	private function hooks(): void {

		add_action( 'shutdown', [ $this, 'cache_dir_complete' ] );

		if ( empty( $this->settings['update_action'] ) ) {
			return;
		}

		// Schedule recurring updates.
		add_action( 'admin_init', [ $this, 'schedule_update_cache' ] );
		add_action( $this->settings['update_action'], [ $this, 'update' ] );

		// Sync cache updates.
		add_action( 'wpforms_helpers_cache_base_sync_updates', [ $this, 'sync_updates' ] );
	}

	/**
	 * Sync cache updates.
	 *
	 * If one update has been done, run the update for other caches.
	 *
	 * @since 1.8.9
	 *
	 * @noinspection PhpCastIsUnnecessaryInspection
	 * @noinspection UnnecessaryCastingInspection
	 */
	public function sync_updates(): void {

		// Prevent infinite loop.
		if ( $this->syncing_updates ) {
			foreach ( (array) static::SYNC_WITH as $classname ) {
				$cache = wpforms()->obj( $classname );

				if ( ! $cache instanceof self ) {
					continue;
				}

				$cache->update( true );
			}
		}
	}

	/**
	 * Set up settings.
	 *
	 * @since 1.6.8
	 */
	private function update_settings(): void {

		$default_settings = [

			// Remote source URL.
			// For instance: 'https://wpformsapi.com/feeds/v1/addons/'.
			'remote_source' => '',

			// Request timeout in seconds.
			'timeout'       => 10,

			// Cache file.
			// Just file name. For instance: 'addons.json'.
			'cache_file'    => '',

			// Cache time to live in seconds.
			'cache_ttl'     => WEEK_IN_SECONDS,

			// Scheduled update action.
			// For instance: 'wpforms_admin_addons_cache_update'.
			'update_action' => '',
			// Additional query args for the remote source URL.
			'query_args'    => [],
		];

		$this->settings = wp_parse_args( $this->setup(), $default_settings );
	}

	/**
	 * Provide settings.
	 *
	 * @since 1.6.8
	 *
	 * @return array Settings array.
	 */
	abstract protected function setup();

	/**
	 * Get a cache directory path.
	 *
	 * @since 1.6.8
	 *
	 * @return string
	 */
	protected function get_cache_dir() {

		return File::get_cache_dir();
	}

	/**
	 * Get data from cache or from API call.
	 *
	 * @since 1.8.2
	 *
	 * @return array
	 */
	public function get() {

		$cache = $this->get_from_cache();

		if ( ! empty( $cache ) && ! $this->is_expired_cache() ) {
			return $cache;
		}

		$this->update();

		return $this->get_from_cache();
	}

	/**
	 * Determine if the cache is expired.
	 *
	 * @since 1.8.2
	 *
	 * @return bool
	 */
	private function is_expired_cache(): bool {

		return $this->cache_time() + $this->settings['cache_ttl'] < time();
	}

	/**
	 * Get cache creation time.
	 *
	 * @since 1.8.2
	 *
	 * @return int
	 */
	private function cache_time(): int {

		return (int) Transient::get( $this->cache_key );
	}

	/**
	 * Determine if the cache file exists.
	 *
	 * @since 1.8.2
	 *
	 * @return bool
	 */
	private function exists(): bool {

		return is_file( $this->cache_file ) && is_readable( $this->cache_file );
	}

	/**
	 * Get cache from a cache file.
	 *
	 * @since 1.8.2
	 *
	 * @return array
	 */
	private function get_from_cache(): array {

		if ( ! $this->exists() ) {
			return [];
		}

		$content = File::get_contents( $this->cache_file );

		// Do not decrypt non-encrypted legacy files, they will be encrypted on the scheduled update.
		if ( static::ENCRYPT && ! wpforms_is_json( $content ) ) {
			$content = Crypto::decrypt( $content );
		}

		return (array) json_decode( $content, true );
	}

	/**
	 * Update cache.
	 *
	 * @since 1.8.2
	 *
	 * @param bool $force Force update.
	 *
	 * @return bool
	 */
	public function update( bool $force = false ): bool {

		if (
			! $force &&
			time() < $this->cache_time() + self::REQUEST_LOCK_TIME * MINUTE_IN_SECONDS
		) {
			return false;
		}

		Transient::set( $this->cache_key, time(), $this->settings['cache_ttl'] );

		if ( ! wp_mkdir_p( $this->cache_dir ) ) {
			return false;
		}

		$data    = $this->perform_remote_request();
		$content = wp_json_encode( $data );

		$this->maybe_update_transient( $data );

		if ( static::ENCRYPT ) {
			$content = Crypto::encrypt( $content );
		}

		if ( ! File::put_contents( $this->cache_file, $content ) ) {
			return false;
		}

		if ( ! $this->syncing_updates ) {
			$this->syncing_updates = true;

			/**
			 * Action hook after the cache has been updated.
			 *
			 * @since 1.8.9
			 */
			do_action( 'wpforms_helpers_cache_base_sync_updates' );
		}

		$this->updated = true;

		return true;
	}

	/**
	 * Get data from API.
	 *
	 * @since 1.8.2
	 *
	 * @return array
	 */
	protected function perform_remote_request(): array {

		$query_args = $this->settings['query_args'] ?? [];

		$request_url = add_query_arg( $query_args, $this->settings['remote_source'] );
		$user_agent  = wpforms_get_default_user_agent();
		$request     = wp_remote_get(
			$request_url,
			[
				'timeout'    => $this->settings['timeout'],
				'user-agent' => $user_agent,
			]
		);

		$request_url_log = remove_query_arg( [ 'tgm-updater-key' ], $request_url );

		// Log if the request failed.
		if ( is_wp_error( $request ) ) {
			$this->add_log(
				'Cached data: HTTP request error',
				[
					'class'       => static::class,
					'request_url' => $request_url_log,
					'error'       => $request->get_error_message(),
					'error_data'  => $request->get_error_data(),
				],
				'error'
			);

			return [];
		}

		$response_code     = wp_remote_retrieve_response_code( $request );
		$raw_headers       = wp_remote_retrieve_headers( $request );
		$response_headers  = is_object( $raw_headers ) ? $raw_headers->getAll() : (array) $raw_headers;
		$response_body     = wp_remote_retrieve_body( $request );
		$response_body_len = strlen( $response_body );
		$response_body_log = $response_body_len > 1024 ? "(First 1 kB):\n" . substr( trim( $response_body ), 0, 1024 ) . '...' : trim( $response_body );
		$response_body_log = esc_html( $response_body_log );

		$log_data = [
			'class'          => static::class,
			'request_url'    => $request_url_log,
			'code'           => $response_code,
			'headers'        => $response_headers,
			'content_length' => $response_body_len,
			'body'           => $response_body_log,
		];

		// Log the response details in debug mode.
		if ( wpforms_debug() ) {
			$this->add_log( 'Cached data: Response details', $log_data );
		}

		// Log the error if the response code is not 2xx or 3xx.
		if ( $response_code > 399 ) {
			$this->add_log( 'Cached data: HTTP request error', $log_data, 'error' );

			return [];
		}

		$json = trim( $response_body );
		$data = json_decode( $json, true );

		if ( empty( $data ) ) {
			$message = $data === null ? 'Invalid JSON' : 'Empty JSON';

			$log_data = array_merge(
				$log_data,
				[
					'json_result'   => $message,
					'cache_file'    => $this->settings['cache_file'],
					'remote_source' => $this->settings['remote_source'],
				]
			);

			$this->add_log( 'Cached data: ' . $message, $log_data, 'error' );

			return [];
		}

		return $this->prepare_cache_data( $data );
	}

	/**
	 * Add log.
	 *
	 * @since 1.8.9
	 *
	 * @param string $title Log title.
	 * @param array  $data  Log data.
	 * @param string $type  Log type.
	 */
	protected function add_log( string $title, array $data, string $type = 'log' ): void {

		wpforms_log(
			$title,
			$data,
			[
				'type' => [ $type ],
			]
		);
	}

	/**
	 * Schedule updates.
	 *
	 * @since 1.6.8
	 */
	public function schedule_update_cache(): void {

		// Just skip if not need to register a scheduled action.
		if ( empty( $this->settings['update_action'] ) ) {
			return;
		}

		$tasks = wpforms()->obj( 'tasks' );

		if (
			! $tasks instanceof Tasks ||
			$tasks->is_scheduled( $this->settings['update_action'] ) !== false
		) {
			return;
		}

		$tasks->create( $this->settings['update_action'] )
			->recurring( time() + $this->settings['cache_ttl'], $this->settings['cache_ttl'] )
			->params()
			->register();
	}

	/**
	 * Complete the cache directory.
	 *
	 * @since 1.6.8
	 */
	public function cache_dir_complete(): void {

		if ( ! $this->updated ) {
			return;
		}

		wpforms_create_upload_dir_htaccess_file();
		wpforms_create_cache_dir_htaccess_file();
		wpforms_create_index_html_file( $this->cache_dir );
		wpforms_create_index_php_file( $this->cache_dir );
	}

	/**
	 * Invalidate cache.
	 *
	 * @since 1.8.7
	 */
	public function invalidate_cache(): void {

		Transient::delete( $this->cache_key );
	}

	/**
	 * Prepare data to store in a local cache.
	 *
	 * @since 1.6.8
	 *
	 * @param array|mixed $data Raw data received by the remote request.
	 *
	 * @return array Prepared data for caching.
	 */
	protected function prepare_cache_data( $data ): array {

		if ( empty( $data ) || ! is_array( $data ) ) {
			return [];
		}

		return $data;
	}

	/**
	 * Maybe update transient duration time.
	 *
	 * Allows updating transient duration time if it's less than expiration time.
	 * To do this, overwrite this method in child classes.
	 *
	 * @since 1.8.7
	 *
	 * @param array $data Data received by the remote request.
	 *
	 * @return bool|array
	 */
	protected function maybe_update_transient( array $data ) {

		return $data;
	}
}
