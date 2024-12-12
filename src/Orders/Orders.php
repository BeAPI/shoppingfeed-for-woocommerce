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
		ShoppingFeedHelper::log(
			\WC_Log_Levels::INFO,
			'>>> Starting orders import process >>>',
			'shopping-feed-orders'
		);

		// Check if import is enable
		if ( ShoppingFeedHelper::is_disable_order_import() ) {
			return false;
		}

		$shop = Sdk::get_sf_shop( $sf_account );

		if ( ! $shop instanceof StoreResource ) {
			ShoppingFeedHelper::log(
				\WC_Log_Levels::ERROR,
				sprintf(
					'Cannot retrieve shop from SDK for account : %s',
					$sf_account['username']
				),
				'shopping-feed-orders'
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

		ShoppingFeedHelper::log(
			\WC_Log_Levels::DEBUG,
			'Fetching orders from API',
			'shopping-feed-orders',
			[
				'filters' => $filters,
			]
		);

		foreach ( $order_api->getAll( $filters ) as $sf_order ) {
			ShoppingFeedHelper::log(
				\WC_Log_Levels::INFO,
				sprintf( '[Order %s] Start processing.', $sf_order->getReference() ),
				'shopping-feed-orders',
				[
					'id' => $sf_order->getId(),
					'channel' => $sf_order->getChannel()->getName(),
					'status' => $sf_order->getStatus(),
				]
			);

			if ( Order::exists( $sf_order ) ) {
				$existing_order = Order::find_by_sf_reference( $sf_order->getReference() );
				ShoppingFeedHelper::log(
					\WC_Log_Levels::INFO,
					sprintf( '[Order %s] Order already imported.', $sf_order->getReference() ),
					'shopping-feed-orders',
					[
						'wc_order' => $existing_order->get_id(),
					]
				);

				continue;
			}

			$import_order_check = $this->can_import_order( $sf_order, $include_fulfilled_by_channel );
			if ( is_wp_error( $import_order_check ) ) {
				ShoppingFeedHelper::log(
					\WC_Log_Levels::NOTICE,
					sprintf(
						"[Order %s] Order can't be imported : %s",
						$sf_order->getReference(),
						$import_order_check->get_error_message()
					),
					'shopping-feed-orders'
				);

				continue;
			}

			// Include VAT when importing the order.
			$include_vat = (bool) ( ShoppingFeedHelper::get_sf_orders_options()['import_vat_order'] ?? false );
			ShoppingFeedHelper::log(
				\WC_Log_Levels::DEBUG,
				sprintf(
					'[Order %s] Will include VAT when importing the order : %s',
					$sf_order->getReference(),
					( $include_vat ) ? 'yes' : 'no'
				),
				'shopping-feed-orders'
			);

			//Init Order
			$order = new Order( $sf_order, $include_vat );
			try {
				//Add Order
				$order->add();
			} catch ( \Exception $e ) {
				ShoppingFeedHelper::log(
					\WC_Log_Levels::CRITICAL,
					sprintf(
						'[Order %s] Import process exception : %s',
						$sf_order->getReference(),
						$e->getMessage()
					),
					'shopping-feed-orders'
				);
			}

			ShoppingFeedHelper::log(
				\WC_Log_Levels::INFO,
				sprintf( '[Order %s] Processing completed.', $sf_order->getReference() ),
				'shopping-feed-orders'
			);
		}

		ShoppingFeedHelper::log(
			\WC_Log_Levels::INFO,
			'<<< Orders import process complete <<<',
			'shopping-feed-orders'
		);

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
