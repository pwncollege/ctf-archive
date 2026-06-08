<?php
/**
 * Helper functions to work with filesystem, uploads and media files.
 *
 * @since 1.8.0
 */

use WPForms\Helpers\File;

/**
 * Get WPForms upload root path (e.g. /wp-content/uploads/wpforms).
 *
 * As of 1.7.0, you can pass in your own value that matches the output of wp_upload_dir()
 * in order to use this function inside of a filter without infinite looping.
 *
 * @since 1.6.1
 *
 * @return array WPForms upload root path (no trailing slash).
 */
function wpforms_upload_dir() {

	$upload_dir = wp_upload_dir();

	if ( ! empty( $upload_dir['error'] ) ) {
		return [ 'error' => $upload_dir['error'] ];
	}

	$basedir             = wp_is_stream( $upload_dir['basedir'] ) ? $upload_dir['basedir'] : realpath( $upload_dir['basedir'] );
	$wpforms_upload_root = trailingslashit( $basedir ) . 'wpforms';

	/**
	 * Allow developers to change a directory where cache and uploaded files will be stored.
	 *
	 * @since 1.5.2
	 *
	 * @param string $wpforms_upload_root WPForms upload root directory.
	 */
	$custom_uploads_root = apply_filters( 'wpforms_upload_root', $wpforms_upload_root );

	if ( is_dir( $custom_uploads_root ) && wp_is_writable( $custom_uploads_root ) ) {
		$wpforms_upload_root = wp_is_stream( $custom_uploads_root )
			? $custom_uploads_root
			: realpath( $custom_uploads_root );
	}

	return [
		'path'  => $wpforms_upload_root,
		'url'   => trailingslashit( $upload_dir['baseurl'] ) . 'wpforms',
		'error' => false,
	];
}

/**
 * Create index.html file in the specified directory if it doesn't exist.
 *
 * @since 1.6.1
 *
 * @param string $path Path to the directory.
 *
 * @return int|false Number of bytes that were written to the file, or false on failure.
 */
function wpforms_create_index_html_file( $path ) {

	if ( ! is_dir( $path ) || is_link( $path ) ) {
		return false;
	}

	$index_file = wp_normalize_path( trailingslashit( $path ) . 'index.html' );

	// Do nothing if index.html exists in the directory.
	if ( file_exists( $index_file ) ) {
		return false;
	}

	// Create empty index.html.
	return file_put_contents( $index_file, '' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
}

/**
 * Create index.php file in the specified directory if it doesn't exist.
 *
 * @since 1.8.7
 *
 * @param string $path Path to the directory.
 *
 * @return int|false Number of bytes that were written to the file, or false on failure.
 */
function wpforms_create_index_php_file( string $path ) {

	if ( ! is_dir( $path ) || is_link( $path ) ) {
		return false;
	}

	$index_file = wp_normalize_path( trailingslashit( $path ) . 'index.php' );

	// Do nothing if index.php exists in the directory.
	if ( file_exists( $index_file ) ) {
		return false;
	}

	$data = '<?php
header( $_SERVER[\'SERVER_PROTOCOL\'] . \' 404 Not Found\' );
header( \'Status: 404 Not Found\' );
';

	// Create index.php.
	return file_put_contents( $index_file, $data ); // phpcs:ignore WordPress.WP.AlternativeFunctions
}

/**
 * Create .htaccess file in the WPForms upload directory.
 *
 * @since 1.6.1
 *
 * @return bool True when the .htaccess file exists, false on failure.
 */
function wpforms_create_upload_dir_htaccess_file(): bool {

	/**
	 * Whether to create upload dir .htaccess file.
	 *
	 * @since 1.6.1
	 *
	 * @param bool $allow True or false.
	 */
	if ( ! apply_filters( 'wpforms_create_upload_dir_htaccess_file', true ) ) {
		return false;
	}

	$htaccess_file = File::get_upload_dir() . '.htaccess';
	$cache_key     = 'upload_htaccess_file';

	if ( File::is_file_updated( $htaccess_file, $cache_key ) ) {
		return true;
	}

	if ( ! function_exists( 'insert_with_markers' ) ) {
		require_once ABSPATH . 'wp-admin/includes/misc.php';
	}

	/**
	 * Filters upload dir .htaccess file content.
	 *
	 * @since 1.6.1
	 *
	 * @param bool $allow True or false.
	 */
	$contents = apply_filters(
		'wpforms_create_upload_dir_htaccess_file_content',
		'# Disable PHP and Python scripts parsing.
<Files *>
  SetHandler none
  SetHandler default-handler
  RemoveHandler .cgi .php .php3 .php4 .php5 .phtml .pl .py .pyc .pyo
  RemoveType .cgi .php .php3 .php4 .php5 .phtml .pl .py .pyc .pyo
</Files>
<IfModule mod_php5.c>
  php_flag engine off
</IfModule>
<IfModule mod_php7.c>
  php_flag engine off
</IfModule>
<IfModule mod_php8.c>
  php_flag engine off
</IfModule>
<IfModule headers_module>
  Header set X-Robots-Tag "noindex"
</IfModule>'
	);

	$created = insert_with_markers( $htaccess_file, 'WPForms', $contents );

	if ( $created ) {
		File::save_file_updated_stat( $htaccess_file, $cache_key );
	}

	return $created;
}

/**
 * Create .htaccess file in the WPForms cache directory.
 *
 * @since 1.8.7
 *
 * @return bool True when the .htaccess file exists, false on failure.
 */
function wpforms_create_cache_dir_htaccess_file(): bool {

	/**
	 * Whether to create cache dir .htaccess file.
	 *
	 * @since 1.8.7
	 *
	 * @param bool $allow True or false.
	 */
	if ( ! apply_filters( 'wpforms_create_cache_dir_htaccess_file', true ) ) {
		return false;
	}

	$htaccess_file = File::get_cache_dir() . '.htaccess';

	if ( File::is_file_updated( $htaccess_file, 'cache_htaccess_file' ) ) {
		return true;
	}

	if ( ! function_exists( 'insert_with_markers' ) ) {
		require_once ABSPATH . 'wp-admin/includes/misc.php';
	}

	/**
	 * Filters cache dir .htaccess file content.
	 *
	 * @since 1.8.7
	 *
	 * @param bool $allow True or false.
	 */
	$contents = apply_filters(
		'wpforms_create_cache_dir_htaccess_file_content',
		'# Disable access for any file in the cache dir.
# Apache 2.2
<IfModule !authz_core_module>
	Deny from all
</IfModule>

# Apache 2.4+
<IfModule authz_core_module>
	Require all denied
</IfModule>'
	);

	$created = insert_with_markers( $htaccess_file, 'WPForms', $contents );

	if ( $created ) {
		File::save_file_updated_stat( $htaccess_file );
	}

	return $created;
}

/**
 * Convert a file size provided, such as "2M", to bytes.
 *
 * @link http://stackoverflow.com/a/22500394
 *
 * @since 1.0.0
 *
 * @param string $size File size.
 *
 * @return int
 */
function wpforms_size_to_bytes( $size ) {

	if ( is_numeric( $size ) ) {
		return $size;
	}

	$suffix = substr( $size, - 1 );
	$value  = substr( $size, 0, - 1 );

	switch ( strtoupper( $suffix ) ) {
		case 'P':
			$value *= 1024;

		case 'T':
			$value *= 1024;

		case 'G':
			$value *= 1024;

		case 'M':
			$value *= 1024;

		case 'K':
			$value *= 1024;
			break;
	}

	return $value;
}

/**
 * Convert a file size provided, such as "2M", to bytes.
 *
 * @link http://stackoverflow.com/a/22500394
 *
 * @since 1.0.0
 *
 * @param bool $bytes Whether the value should be in bytes or formatted.
 *
 * @return false|string|int
 */
function wpforms_max_upload( $bytes = false ) {

	$max = wp_max_upload_size();

	if ( $bytes ) {
		return $max;
	}

	return size_format( $max );
}
