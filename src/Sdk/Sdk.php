<?php

namespace ShoppingFeed\ShoppingFeedWC\Sdk;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

use GuzzleHttp\Exception\ClientException;
use ShoppingFeed\Sdk\Api\Session\SessionResource;
use ShoppingFeed\Sdk\Client;
use ShoppingFeed\Sdk\Credential;
use ShoppingFeed\ShoppingFeedWC\ShoppingFeedHelper;

/**
 * @psalm-consistent-constructor
 */
class Sdk {

	/**
	 * Get session from credentials.
	 *
	 * @param string $username
	 * @param string $password
	 *
	 * @return SessionResource|\WP_Error
	 */
	public static function get_session_by_credentials( $username, $password ) {

		try {
			$credentials = new Credential\Password( $username, $password );
			$options     = new Client\ClientOptions();
			$options->addHeaders(
				[
					'Connection' => 'close',
				]
			);
			$session = Client\Client::createSession( $credentials, $options );
		} catch ( ClientException $e ) {
			if ( $e->getResponse() && 401 === $e->getResponse()->getStatusCode() ) {
				$session = new \WP_Error( 'sf_login_invalid_credentials', __( 'Credentials were not recognized by ShoppingFeed.', 'shopping-feed' ) );
			} else {
				$session = new \WP_Error( 'sf_login_error', __( 'A error occurred will trying to log in with the credentials.', 'shopping-feed' ) );
			}
		} catch ( \Exception $e ) {
			$session = new \WP_Error( 'sf_request_error', __( 'A error occurred.', 'shopping-feed' ) );
		}

		return $session;
	}

	/**
	 * @param int $account_id
	 */
	public static function get_sf_account_shop( $account_id ) {
		$sf_account = ShoppingFeedHelper::get_sf_account_credentials( $account_id );

		return self::get_sf_shop( $sf_account );
	}


	/**
	 * Return account default shop
	 *
	 * @param array $sf_account
	 *
	 * @return false|\ShoppingFeed\Sdk\Api\Store\StoreResource
	 * @psalm-suppress all
	 */
	public static function get_sf_shop( $sf_account ) {
		if ( empty( $sf_account['token'] ) ) {
			ShoppingFeedHelper::get_logger()->error(
				sprintf(
					__( 'No Credentials found to connect', 'shopping-feed' )
				),
				array(
					'source' => 'shopping-feed',
				)
			);

			return false;
		}

		$credentials = new Credential\Token( $sf_account['token'] );

		try {
			$options = new Client\ClientOptions();
			$options->addHeaders(
				[
					'Connection' => 'close',
				]
			);
			$session = Client\Client::createSession( $credentials, $options );
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

			return false;
		}

		$main_shop = $session->getMainStore();

		if ( empty( $main_shop ) ) {
			ShoppingFeedHelper::get_logger()->error(
			//TODO: add more informations about concerned sf account
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
