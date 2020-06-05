<?php

namespace ShoppingFeed\ShoppingFeedWC\Orders;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

use ShoppingFeed\Sdk\Api\Store\StoreResource;
use ShoppingFeed\ShoppingFeedWC\Sdk\Sdk;
use ShoppingFeed\ShoppingFeedWC\ShoppingFeedHelper;

/**
 * Class Orders
 * @package ShoppingFeed\Orders
 */
class Orders {

	/**
	 * @var false|StoreResource
	 */
	private $shop;

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
	 */
	private function __wakeup() {
	}

	/**
	 * Orders constructor.
	 */
	private function __construct() {
		if ( empty( $this->shop ) ) {
			$sdk = Sdk::get_instance();
			if ( $sdk->get_default_shop() ) {
				$this->shop = $sdk->get_default_shop();
			}
		}
	}

	/**
	 * Get Orders from SF
	 * @return bool
	 */
	public function get_orders() {
		if ( ! $this->shop ) {
			return false;
		}

		$order_api                 = $this->shop->getOrderApi();
		$filters                   = array();
		$filters['acknowledgment'] = 'unacknowledged';

		$filters['status'] = ShoppingFeedHelper::sf_order_statuses_to_import();

		foreach ( $order_api->getAll( $filters ) as $sf_order ) {
			if ( Order::exists( $sf_order ) ) {
				ShoppingFeedHelper::get_logger()->notice(
					sprintf(
					/* translators: %1$1s: Order reference. %2$2s: Order id. */
						__( 'Order already imported  %1$1s => %2$2s', 'shopping-feed' ),
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
}
