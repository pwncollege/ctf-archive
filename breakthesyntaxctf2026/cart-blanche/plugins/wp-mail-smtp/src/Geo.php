<?php

namespace WPMailSMTP;

/**
 * Class Geo to work with location, domain, IPs etc.
 *
 * @since 1.5.0
 */
class Geo {

	/**
	 * Get the current site hostname.
	 * In case of CLI we don't have SERVER_NAME, so use host name instead, may be not a domain name.
	 * Examples: example.com, localhost.
	 *
	 * @since 1.5.0
	 *
	 * @return string
	 */
	public static function get_site_domain() {

		return ! empty( $_SERVER['SERVER_NAME'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ) : wp_parse_url( get_home_url( get_current_blog_id() ), PHP_URL_HOST );
	}

	/**
	 * Get the domain IP address.
	 * Uses gethostbyname() which is quite slow, but this is done only one time.
	 *
	 * @since 1.5.0
	 *
	 * @param string $domain
	 *
	 * @return string
	 */
	public static function get_ip_by_domain( $domain ) {

		if ( $domain === 'localhost' ) {
			return '127.0.0.1';
		}

		return gethostbyname( $domain );
	}

	/**
	 * Get the location coordinates by IP address.
	 * We make a request to 3rd party services.
	 *
	 * @since 1.5.0
	 * @since 1.6.0 Added new geo API endpoint, provided by WPForms.
	 * @since 2.0.0 Updated the WPForms geo API endpoint to v3.
	 *
	 * @param string $ip The IP address.
	 *
	 * @return array Empty array for localhost.
	 */
	public static function get_location_by_ip( $ip ) {

		// Check for a non-local IP.
		if ( empty( $ip ) || in_array( $ip, [ '127.0.0.1', '::1' ], true ) ) {
			return [];
		}

		$request = wp_remote_get( 'https://geo.wpforms.com/v3/geolocate/json/' . $ip );

		if ( ! is_wp_error( $request ) ) {
			$request = json_decode( wp_remote_retrieve_body( $request ), true );
			if ( ! empty( $request['latitude'] ) && ! empty( $request['longitude'] ) ) {
				$data = [
					'latitude'  => sanitize_text_field( $request['latitude'] ),
					'longitude' => sanitize_text_field( $request['longitude'] ),
					'city'      => isset( $request['city'] ) ? sanitize_text_field( $request['city'] ) : '',
					'region'    => isset( $request['region_name'] ) ? sanitize_text_field( $request['region_name'] ) : '',
					'country'   => isset( $request['country_iso'] ) ? sanitize_text_field( $request['country_iso'] ) : '',
					'postal'    => isset( $request['zip_code'] ) ? sanitize_text_field( $request['zip_code'] ) : '',
				];

				return $data;
			}
		}

		$request = wp_remote_get( 'https://ipapi.co/' . $ip . '/json' );

		if ( ! is_wp_error( $request ) ) {

			$request = json_decode( wp_remote_retrieve_body( $request ), true );

			if ( ! empty( $request['latitude'] ) && ! empty( $request['longitude'] ) ) {

				$data = [
					'latitude'  => sanitize_text_field( $request['latitude'] ),
					'longitude' => sanitize_text_field( $request['longitude'] ),
					'city'      => isset( $request['city'] ) ? sanitize_text_field( $request['city'] ) : '',
					'region'    => isset( $request['region'] ) ? sanitize_text_field( $request['region'] ) : '',
					'country'   => isset( $request['country'] ) ? sanitize_text_field( $request['country'] ) : '',
					'postal'    => isset( $request['postal'] ) ? sanitize_text_field( $request['postal'] ) : '',
				];

				return $data;
			}
		}

		$request = wp_remote_get(
			'https://tools.keycdn.com/geo.json?host=' . $ip,
			[
				'user-agent' => 'keycdn-tools:' . get_home_url(),
			]
		);

		if ( ! is_wp_error( $request ) ) {

			$request = json_decode( wp_remote_retrieve_body( $request ), true );

			if ( ! empty( $request['data']['geo']['latitude'] ) && ! empty( $request['data']['geo']['longitude'] ) ) {

				$data = [
					'latitude'  => sanitize_text_field( $request['data']['geo']['latitude'] ),
					'longitude' => sanitize_text_field( $request['data']['geo']['longitude'] ),
					'city'      => isset( $request['data']['geo']['city'] ) ? sanitize_text_field( $request['data']['geo']['city'] ) : '',
					'region'    => isset( $request['data']['geo']['region_name'] ) ? sanitize_text_field( $request['data']['geo']['region_name'] ) : '',
					'country'   => isset( $request['data']['geo']['country_code'] ) ? sanitize_text_field( $request['data']['geo']['country_code'] ) : '',
					'postal'    => isset( $request['data']['geo']['postal_code'] ) ? sanitize_text_field( $request['data']['geo']['postal_code'] ) : '',
				];

				return $data;
			}
		}

		return [];
	}

	/**
	 * This routine calculates the distance between two points (given the latitude/longitude of those points).
	 * Definitions: South latitudes are negative, east longitudes are positive.
	 *
	 * @see   https://www.geodatasource.com/developers/php
	 *
	 * @since 1.5.0
	 *
	 * @param float  $lat1 Latitude of point 1 (in decimal degrees).
	 * @param float  $lon1 Longitude of point 1 (in decimal degrees).
	 * @param float  $lat2 Latitude of point 2 (in decimal degrees).
	 * @param float  $lon2 Longitude of point 2 (in decimal degrees).
	 * @param string $unit Supported values: M, K, N. Miles by default.
	 *
	 * @return float|int
	 */
	public static function get_distance_between( $lat1, $lon1, $lat2, $lon2, $unit = 'M' ) {

		if ( ( $lat1 === $lat2 ) && ( $lon1 === $lon2 ) ) {
			return 0;
		}

		$theta = $lon1 - $lon2;
		$dist  = sin( deg2rad( $lat1 ) ) * sin( deg2rad( $lat2 ) ) + cos( deg2rad( $lat1 ) ) * cos( deg2rad( $lat2 ) ) * cos( deg2rad( $theta ) );
		$dist  = acos( $dist );
		$dist  = rad2deg( $dist );
		$miles = $dist * 60 * 1.1515;
		$unit  = strtoupper( $unit );

		if ( $unit === 'K' ) {
			return ( $miles * 1.609344 );
		} elseif ( $unit === 'N' ) {
			return ( $miles * 0.8684 );
		}

		return $miles;
	}

	/**
	 * Get the user IP address.
	 *
	 * @since 3.11.0
	 *
	 * Code based on the:
	 *   - WordPress method \WP_Community_Events::get_unsafe_client_ip
	 *   - Cloudflare documentation https://support.cloudflare.com/hc/en-us/articles/206776727
	 *
	 * @return string
	 */
	public static function get_ip() {

		$ip = '127.0.0.1';

		$address_headers = [
			'HTTP_TRUE_CLIENT_IP',
			'HTTP_CF_CONNECTING_IP',
			'HTTP_X_REAL_IP',
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR',
		];

		foreach ( $address_headers as $header ) {
			if ( empty( $_SERVER[ $header ] ) ) {
				continue;
			}

			/*
			 * HTTP_X_FORWARDED_FOR can contain a chain of comma-separated addresses, with or without spaces.
			 * The first address is the original client. It can't be trusted for authenticity,
			 * but we don't need to for this purpose.
			 */

			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$address_chain = explode( ',', wp_unslash( $_SERVER[ $header ] ) );
			$ip            = filter_var( trim( $address_chain[0] ), FILTER_VALIDATE_IP );

			break;
		}

		/**
		 * Filter detected IP address.
		 *
		 * @since 3.11.0
		 *
		 * @param string $ip IP address.
		 */
		return filter_var( apply_filters( 'wp_mail_smtp_geo_get_ip', $ip ), FILTER_VALIDATE_IP );
	}
}
