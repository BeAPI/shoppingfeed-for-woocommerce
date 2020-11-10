<?php

namespace ShoppingFeed\ShoppingFeedWC\Admin;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

use ShoppingFeed\ShoppingFeedWC\ShoppingFeedHelper;
use const OPENSSL_VERSION_TEXT;
use const PHP_VERSION;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @psalm-consistent-constructor
 */
class Requirements {

	const PHP_MIN = '5.6';
	const OPENSSL_MIN = 268439567;
	const OPENSSL_MIN_TEXT = 'OpenSSL 1.0.1 14 Mar 2012';
	/**
	 * @var Requirements
	 */
	private static $instance;

	/**
	 * Get the singleton instance.
	 *
	 * @return Requirements
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new static();
		}

		return self::$instance;
	}

	/**
	 * Singleton instance can't be cloned.
	 */
	private function __clone() {
	}

	/**
	 * Singleton instance can't be serialized.
	 */
	private function __wakeup() {
	}

	/**
	 * @return string
	 */
	public function curl_requirement() {
		return ( $this->valid_curl() )
			? '<p class="success">' . __( 'The PHP cURL extension is installed and activated on your server.', 'shopping-feed' ) . '</p>'
			: '<p class="failed">' . __( 'The PHP cURL extension must be installed and activated on your server.', 'shopping-feed' ) . '</p>';
	}

	/**
	 * @return string
	 */
	public function php_requirement() {
		return ( $this->valid_php() )
			? '<p class="success">' . __( 'The PHP version on your server is valid.', 'shopping-feed' ) . '</p>'
			/* translators: %s: minimum required PHP version */
			: '<p class="failed">' . sprintf( __( 'The PHP version on your server is not supported. Your server must run PHP %s or greater.', 'shopping-feed' ), self::PHP_MIN ) . '</p>';
	}

	/**
	 * @return string
	 */
	public function openssl_requirement() {
		return ( $this->valid_openssl() )
			? '<p class="success">' . __( 'OpenSSL is up to date.', 'shopping-feed' ) . '</p>'
			/* translators: %s: minimum required OpenSSL version */
			: '<p class="failed">' . sprintf( __( 'OpenSSL is not up to date. Please update to OpenSSL %s or later.', 'shopping-feed' ), self::OPENSSL_MIN . ' ( ' . self::OPENSSL_MIN_TEXT . ' )' ) . '</p>';
	}

	/**
	 * @return string
	 */
	public function account_requirement() {
		return ( $this->valid_account() )
			? '<p class="success">' . __( 'You are logged in your ShoppingFeed account.', 'shopping-feed' ) . '</p>'
			: '<p class="failed">' . __( 'You must be logged in your ShoppingFeed account.', 'shopping-feed' ) . '</p>';
	}

	/**
	 * Check if CURL is available and support SSL
	 *
	 * @return bool
	 */
	public function valid_curl() {
		if ( ! function_exists( 'curl_init' ) || ! function_exists( 'curl_exec' ) ) {
			return false;
		}

		// Also Check for SSL support
		$curl_version = curl_version();
		if ( ! ( CURL_VERSION_SSL & $curl_version['features'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if PHP version is equal or above the minimum supported.
	 *
	 * @return bool
	 */
	public function valid_php() {
		return version_compare( PHP_VERSION, self::PHP_MIN, '>=' );
	}

	/**
	 * Check if OPENSSL version is equal or above the minimum supported.
	 *
	 * @return bool
	 */
	public function valid_openssl() {
		return OPENSSL_VERSION_NUMBER >= self::OPENSSL_MIN;
	}

	/**
	 * Check if the user is logged in.
	 *
	 * @return bool
	 */
	public function valid_account() {
		return ShoppingFeedHelper::is_authenticated();
	}
}
