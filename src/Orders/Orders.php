<?php

namespace ShoppingFeed\ShoppingFeedWC\Orders;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

use ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Api\Order\OrderResource;
use ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Api\Store\StoreResource;
use ShoppingFeed\ShoppingFeedWC\Addons\Marketplace;
use ShoppingFeed\ShoppingFeedWC\Sdk\Sdk;
use ShoppingFeed\ShoppingFeedWC\ShoppingFeedHelper;

/**
 * @psalm-consistent-constructor
 */
class Orders {

	use Marketplace;

	/**
	 * @var Orders
	 */
	private static $instance;

	/**
	 * Get the singleton instance.
	 *
	 * @return Orders
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
	 * @throws \Exception
	 */
	public function __wakeup() {
		throw new \Exception( 'Cannot serialize singleton' );
	}

	/**
	 * Get Orders from SF
	 */
	public function get_orders( $sf_account, $since = '' ) {
		// Check if import is enable
		if ( ShoppingFeedHelper::is_disable_order_import() ) {
			return false;
		}
		$shop = Sdk::get_sf_shop( $sf_account );

		if ( ! $shop instanceof StoreResource ) {
			ShoppingFeedHelper::get_logger()->error(
				sprintf(
					/* translators: %s: Error message. */
					__( 'Cannot retrieve shop from SDK for account : %s', 'shopping-feed' ),
					$sf_account['username']
				),
				array(
					'source' => 'shopping-feed',
				)
			);
			return false;
		}

		$order_api = $shop->getOrderApi();
		$since = ! empty( $since ) ? $since : gmdate( 'c', strtotime( '14 days ago' ) );
		$filters   = array(
			'acknowledgment' => 'unacknowledged',
			'status'         => ShoppingFeedHelper::sf_order_statuses_to_import(),
			'since'          => $since,
		);

		$orders_options               = ShoppingFeedHelper::get_sf_orders_options();
		$include_fulfilled_by_channel = (bool) ( $orders_options['import_order_fulfilled_by_marketplace'] ?? false );

		foreach ( $order_api->getAll( $filters ) as $sf_order ) {
			if ( Order::exists( $sf_order ) ) {
				ShoppingFeedHelper::get_logger()->notice(
					sprintf(
						/* translators: 1: Order reference. 2: Order id. */
						__( 'Order already imported %1$s (%2$s)', 'shopping-feed' ),
						$sf_order->getReference(),
						$sf_order->getId()
					),
					array(
						'source' => 'shopping-feed',
					)
				);
				continue;
			}

			$import_order_check = $this->can_import_order( $sf_order, $include_fulfilled_by_channel );
			if ( is_wp_error( $import_order_check ) ) {
				/** @psalm-suppress PossiblyInvalidMethodCall */
				ShoppingFeedHelper::get_logger()->notice(
					sprintf(
						/* translators: 1: Order reference, 2: Order id, 3: error message. */
						__( 'Order %1$s (%2$s) can\'t be imported : %3$s', 'shopping-feed' ),
						$sf_order->getReference(),
						$sf_order->getId(),
						$import_order_check->get_error_message()
					),
					array(
						'source' => 'shopping-feed',
					)
				);
				continue;
			}

			//Init Order
			$order = new Order( $sf_order );
			//Add Order
			$order->add();
		}

		return true;
	}

	/**
	 * Check if the order can be imported.
	 *
	 * @param OrderResource $sf_order
	 * @param bool $import_orders_fufilled_by_channel
	 *
	 * @return bool|\WP_Error true if the order can be imported or WP_Error with
	 *                        the reason the order can't be imported otherwise.
	 */
	public function can_import_order( $sf_order, $import_orders_fufilled_by_channel = false ) {
		$sf_order_data = $sf_order->toArray();
		$status = $sf_order_data['status'] ?? '';
		$anonymized = (bool) ( $sf_order_data['anonymized'] ?? false );

		// Always skip anonymized orders.
		if ( $anonymized ) {
			return new \WP_Error(
				'shoppingfeed_order_import_anonymized_order',
				__( 'Order data has been anonymized.', 'shopping-feed' )
			);
		}

		$fulfilled_by_marketplace = $this->is_fulfilled_by_marketplace( $sf_order );

		// If the order is fulfilled by the merchant
		if ( ! $fulfilled_by_marketplace ) {
			// Only import orders with the `waiting_shipment` status.
			if ( 'waiting_shipment' !== $status ) {
				return new \WP_Error(
					'shoppingfeed_order_import_fulfilledby_store_incompatible_status',
					sprintf(
						/* translators: the order status */
						__( 'Order fulfilled by store with status "%s" will not be imported.', 'shopping-feed' ),
						$status
					)
				);
			}

			return true;
		}

		// Only imports orders fulfilled by channel if the flag is true.
		if ( ! $import_orders_fufilled_by_channel ) {
			return new \WP_Error(
				'shoppingfeed_order_import_skip_fulfilledby_marketplace_orders',
				__( 'Order if fulfilled by channel and will not be imported.', 'shopping-feed' )
			);
		}

		return true;
	}
}
