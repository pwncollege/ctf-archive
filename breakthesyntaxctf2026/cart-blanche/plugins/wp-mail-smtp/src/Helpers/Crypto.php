<?php

namespace WPMailSMTP\Helpers;

/**
 * Class for encryption functionality.
 *
 * @since 2.5.0
 *
 * @link https://www.php.net/manual/en/intro.sodium.php
 */
class Crypto {

	/**
	 * Get a secret key for encrypt/decrypt.
	 *
	 * @since 2.5.0
	 *
	 * @param bool $create Should the key be created, if it does not exist yet.
	 *
	 * @return string|bool
	 */
	public static function get_secret_key( $create = false ) {

		if ( defined( 'WPMS_CRYPTO_KEY' ) ) {
			return WPMS_CRYPTO_KEY;
		}

		$secret_key = apply_filters( 'wp_mail_smtp_helpers_crypto_get_secret_key', get_option( 'wp_mail_smtp_mail_key' ) );

		// If we already have the secret, send it back.
		if ( false !== $secret_key ) {
			return base64_decode( $secret_key ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		}

		if ( $create ) {
			// We don't have a secret, so let's generate one.
			try {
				$secret_key = sodium_crypto_secretbox_keygen(); // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.sodium_crypto_secretbox_keygenFound
			} catch ( \Exception $e ) {
				$secret_key = wp_generate_password( SODIUM_CRYPTO_SECRETBOX_KEYBYTES ); // phpcs:ignore PHPCompatibility.Constants.NewConstants.sodium_crypto_secretbox_keybytesFound
			}

			add_option( 'wp_mail_smtp_mail_key', base64_encode( $secret_key ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode

			return $secret_key;
		}

		return false;
	}

	/**
	 * Encrypt a message.
	 *
	 * @since 2.5.0
	 *
	 * @param string $message Message to encrypt.
	 * @param string $key     Encryption key.
	 *
	 * @return string
	 * @throws \Exception The exception object.
	 */
	public static function encrypt( $message, $key = '' ) {

		if ( apply_filters( 'wp_mail_smtp_helpers_crypto_stop', false ) ) {
			return $message;
		}

		// Create a nonce for this operation. It will be stored and recovered in the message itself.
		// phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.random_bytesFound, PHPCompatibility.Constants.NewConstants.sodium_crypto_secretbox_noncebytesFound
		$nonce = random_bytes( SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );

		if ( empty( $key ) ) {
			$key = self::get_secret_key( true );
		}

		// Encrypt message and combine with nonce.
		$cipher = base64_encode( // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			$nonce .
			sodium_crypto_secretbox( // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.sodium_crypto_secretboxFound
				$message,
				$nonce,
				$key
			)
		);

		try {
			sodium_memzero( $message ); // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.sodium_memzeroFound
			sodium_memzero( $key ); // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.sodium_memzeroFound
		} catch ( \Exception $e ) {
			return $cipher;
		}

		return $cipher;
	}

	/**
	 * Decrypt a message.
	 * Returns encrypted message on any failure and the decrypted message on success.
	 *
	 * @since 2.5.0
	 *
	 * @param string $encrypted Encrypted message.
	 * @param string $key       Encryption key.
	 *
	 * @return string
	 * @throws \Exception The exception object.
	 */
	public static function decrypt( $encrypted, $key = '' ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		if ( apply_filters( 'wp_mail_smtp_helpers_crypto_stop', false ) ) {
			return $encrypted;
		}

		// Unpack base64 message.
		$decoded = base64_decode( $encrypted ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode

		if ( false === $decoded ) {
			return $encrypted;
		}

		// Include polyfill if mbstring PHP extension is not enabled.
		if ( ! function_exists( 'mb_strlen' ) || ! function_exists( 'mb_substr' ) ) {
			Helpers::include_mbstring_polyfill();
		}

		// phpcs:ignore PHPCompatibility.Constants.NewConstants.sodium_crypto_secretbox_noncebytesFound, PHPCompatibility.Constants.NewConstants.sodium_crypto_secretbox_macbytesFound
		if ( mb_strlen( $decoded, '8bit' ) < ( SODIUM_CRYPTO_SECRETBOX_NONCEBYTES + SODIUM_CRYPTO_SECRETBOX_MACBYTES ) ) {
			return $encrypted;
		}

		// Pull nonce and ciphertext out of unpacked message.
		$nonce      = mb_substr( $decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit' ); // phpcs:ignore PHPCompatibility.Constants.NewConstants.sodium_crypto_secretbox_noncebytesFound
		$ciphertext = mb_substr( $decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit' ); // phpcs:ignore PHPCompatibility.Constants.NewConstants.sodium_crypto_secretbox_noncebytesFound

		$key = empty( $key ) ? self::get_secret_key() : $key;

		if ( empty( $key ) ) {
			return $encrypted;
		}

		// Decrypt it.
		$message = sodium_crypto_secretbox_open( // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.sodium_crypto_secretbox_openFound
			$ciphertext,
			$nonce,
			$key
		);

		// Check for decryption failures.
		if ( false === $message ) {
			return $encrypted;
		}

		try {
			sodium_memzero( $ciphertext ); // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.sodium_memzeroFound
			sodium_memzero( $key ); // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.sodium_memzeroFound
		} catch ( \Exception $e ) {
			return $message;
		}

		return $message;
	}
}
