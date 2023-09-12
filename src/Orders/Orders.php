<?php

namespace ShoppingFeed\ShoppingFeedWC\Orders;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

use ShoppingFeed\Sdk\Api\Order\OrderResource;
use ShoppingFeed\Sdk\Api\Store\StoreResource;
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
	public function get_orders( $sf_account ) {
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

		$order_api                 = $shop->getOrderApi();
		$filters                   = array();
		$filters['acknowledgment'] = 'unacknowledged';

		$filters['status'] = ShoppingFeedHelper::sf_order_statuses_to_import();

		foreach ( $order_api->getAll( $filters ) as $sf_order ) {
			if ( Order::exists( $sf_order ) ) {
				ShoppingFeedHelper::get_logger()->notice(
					sprintf(
						/* translators: 1: Order reference. 2: Order id. */
						__( 'Order already imported %1$1s (%2$2s)', 'shopping-feed' ),
						$sf_order->getReference(),
						$sf_order->getId()
					),
					array(
						'source' => 'shopping-feed',
					)
				);
				continue;
			}

			if ( ! $this->can_import_order( $sf_order ) ) {
				ShoppingFeedHelper::get_logger()->notice(
					sprintf(
						/* translators: 1: Order reference. 2: Order id. */
						__( 'Order fulfilled by channel %1$1s (%2$2s)', 'shopping-feed' ),
						$sf_order->getReference(),
						$sf_order->getId()
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
	 *
	 * @return bool
	 */
	public function can_import_order( $sf_order ) {
		// Allow user for force imports for orders fulfilled by the marketplaces.
		$orders_options = ShoppingFeedHelper::get_sf_orders_options();
		if ( isset( $orders_options['import_order_fulfilled_by_marketplace'] ) && true === (bool) $orders_options['import_order_fulfilled_by_marketplace'] ) {
			return true;
		}

		return false === $this->is_fulfilled_by_marketplace( $sf_order );
	}
}
