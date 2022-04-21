<?php

namespace ShoppingFeed\ShoppingFeedWC\Sdk;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

use ShoppingFeed\Sdk\Client;
use ShoppingFeed\Sdk\Credential;
use ShoppingFeed\ShoppingFeedWC\ShoppingFeedHelper;

/**
 * @psalm-consistent-constructor
 */
class Sdk {

	/**
	 * @param int $account_id
	 */
	public static function get_sf_account_shop( $account_id ) {
		$sf_account = ShoppingFeedHelper::get_sf_account_credentials( $account_id );

		return self::get_sf_shop( $sf_account );
	}


	/**
	 * Return account deault shop
	 *
	 * @param array $sf_account
	 *
	 * @return false|\ShoppingFeed\Sdk\Api\Store\StoreResource
	 * @psalm-suppress all
	 */
	public static function get_sf_shop( $sf_account ) {
		if (
			empty( $sf_account['token'] ) && (
				empty( $sf_account['username'] ) ||
				empty( $sf_account['password'] )
			)
		) {
			//TODO: add more informations about concerned sf account
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

		$credentials = new Credential\Password( $sf_account['username'], $sf_account['password'] );

		if ( ! empty( $sf_account['token'] ) ) {
			$credentials = new Credential\Token( $sf_account['token'] );
		}

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

		//add storeId to account
		$account_options = ShoppingFeedHelper::get_sf_account_options();
		$index           = array_search( $sf_account['username'], array_column( $account_options, 'username' ), true );
		if ( false === $index || empty( $account_options[ $index ] ) ) {
			return false;
		}
		$account_options[ $index ]['sf_store_id'] = $main_shop->getId();
		$account_options[ $index ]['token']       = $session->getToken();
		ShoppingFeedHelper::set_sf_account_options( $account_options );

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
