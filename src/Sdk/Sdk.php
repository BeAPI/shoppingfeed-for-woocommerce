<?php

namespace ShoppingFeed\ShoppingFeedWC\Sdk;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

use ShoppingFeed\Sdk\Api\Session\SessionResource;
use ShoppingFeed\Sdk\Client;
use ShoppingFeed\Sdk\Credential;
use ShoppingFeed\ShoppingFeedWC\ShoppingFeedHelper;

/**
 * ShoppingFeed SDK
 * Class Sdk
 * @package ShoppingFeed\Sdk
 */
class Sdk {

	/** @var SessionResource */
	private $session;

	/** @var string|null */
	private $username;

	/** @var string|null */
	private $password;

	/** @var string|null */
	private $token;

	/**
	 * @var Sdk
	 */
	private static $instance;

	/**
	 * Get the singleton instance.
	 *
	 * @return Sdk
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
	 * Sdk constructor.
	 */
	private function __construct() {
		if ( empty( $this->session ) ) {
			$options = ShoppingFeedHelper::get_sf_account_options();
			if ( empty( $options ) ) {
				ShoppingFeedHelper::get_logger()->error(
					__( 'No settings founds', 'shopping-feed' ),
					array(
						'source' => 'shopping-feed',
					)
				);

				return;
			}

			$this->token    = ! empty( $options['token'] ) ? $options['token'] : null;
			$this->username = ! empty( $options['username'] ) ? $options['username'] : null;
			$this->password = ! empty( $options['password'] ) ? $options['password'] : null;

			$this->authenticate();
		}
	}

	/**
	 * @return Credential\Password|Credential\Token|\WP_Error
	 */
	private function get_credential() {

		//Check if we have Token to connect directly
		if ( ! empty( $this->token ) ) {
			return new Credential\Token( $this->token );
		}

		//If no credentials found go back
		if ( empty( $this->username ) || empty( $this->password ) ) {
			ShoppingFeedHelper::get_logger()->error(
				__( 'Need username/password to connect', 'shopping-feed' ),
				array(
					'source' => 'shopping-feed',
				)
			);
			return new \WP_Error( 'shopping_feed_auth_required', __( 'Need username/password to connect', 'shopping-feed' ) );
		}

		//Return Credentials username/password
		return new Credential\Password( $this->username, $this->password );
	}

	/**
	 * Try to connect if we have credentials
	 * Set Token if the connection is set
	 * @return bool
	 */
	public function authenticate() {
		if ( is_wp_error( $this->get_credential() ) ) {
			return false;
		}

		/** @var Credential\Password|Credential\Token $credentials */
		$credentials = $this->get_credential();

		try {
			$this->session = Client\Client::createSession( $credentials );

			if ( empty( $this->token ) ) {
				ShoppingFeedHelper::set_sf_token( $this->session->getToken() );
			}

			return true;
		} catch ( \Exception $exception ) {

			ShoppingFeedHelper::get_logger()->error(
				sprintf(
				/* translators: %s: Error message. */
					__( 'Cant login with actual credentials => %s', 'shopping-feed' ),
					$exception->getMessage()
				),
				array(
					'source' => 'shopping-feed',
				)
			);

			ShoppingFeedHelper::clean_password();
			return false;
		}
	}

	/**
	 * Return the main store if the connection is set
	 * @return false|\ShoppingFeed\Sdk\Api\Store\StoreResource
	 */
	public function get_default_shop() {

		if ( ! $this->authenticate() ) {
			return false;
		}

		$main_shop = $this->session->getMainStore();

		if ( ! $main_shop ) {
			ShoppingFeedHelper::get_logger()->error(
				__( 'No store found', 'shopping-feed' ),
				array(
					'source' => 'shopping-feed',
				)
			);
			return false;
		}

		return $main_shop;
	}

	/**
	 * Return Available statuses
	 * @return array
	 */
	public static function get_statuses() {
		return array(
			'created'                  => 'Created',
			'waiting_store_acceptance' => 'Waiting Store Acceptance',
			'refused'                  => 'Refused',
			'waiting_shipment'         => 'Waiting Shipment',
			'shipped'                  => 'Shipped',
			'cancelled'                => 'Canceled',
			'refunded'                 => 'Refunded',
			'partially_refunded'       => 'Partially Refunded',
			'partially_shipped'        => 'Partially Shipped',
		);
	}
}
