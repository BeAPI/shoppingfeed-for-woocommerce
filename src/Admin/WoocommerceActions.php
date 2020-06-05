<?php

namespace ShoppingFeed\ShoppingFeedWC\Admin;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

use Exception;
use ShoppingFeed\Sdk\Api\Catalog\InventoryUpdate;
use ShoppingFeed\Sdk\Api\Catalog\PricingUpdate;
use ShoppingFeed\Sdk\Api\Store\StoreResource;
use ShoppingFeed\ShoppingFeedWC\Feed\Generator;
use ShoppingFeed\ShoppingFeedWC\Orders\Operations;
use ShoppingFeed\ShoppingFeedWC\Orders\Order;
use ShoppingFeed\ShoppingFeedWC\Orders\Orders;
use ShoppingFeed\ShoppingFeedWC\Products\Product;
use ShoppingFeed\ShoppingFeedWC\Sdk\Sdk;
use ShoppingFeed\ShoppingFeedWC\ShoppingFeedHelper;


/**
 * Custom WooCommerce actions.
 *
 */
class WoocommerceActions {

	/**
	 * @var PricingUpdate
	 */
	private $pricing_update;

	/**
	 * @var InventoryUpdate
	 */
	private $inventory_update;


	public function __construct() {

		//Product Update
		add_action( 'woocommerce_update_product', array( $this, 'update_product' ) );

		//Stock Update
		add_action( 'woocommerce_updated_product_stock', array( $this, 'update_stock' ) );

		//Feed Generation
		add_action(
			'sf_generate_feed_action',
			function () {
				Generator::get_instance()->generate();
			}
		);

		//Get Orders
		add_action(
			'sf_get_orders_action',
			function () {
				Orders::get_instance()->get_orders();
			}
		);

		//Acknowledge Remain Order
		add_action(
			'sf_acknowledge_remain_order',
			function ( $order_id, $message ) {
				Operations::acknowledge_order( $order_id, $message );
			},
			10,
			2
		);

		/**
		 * Orders Statuses Mapping
		 * Each status with action
		 * @see Operations
		 */
		$statuses_actions = ShoppingFeedHelper::get_sf_statuses_actions();
		if ( 0 < $statuses_actions ) {
			foreach ( $statuses_actions as $sf_action => $wc_statuses ) {
				if ( ! is_array( $wc_statuses ) || '' === $sf_action ) {
					continue;
				}

				foreach ( $wc_statuses as $wc_status ) {
					$status = str_replace( 'wc-', '', $wc_status );
					$action = 'woocommerce_order_status_' . $status;
					add_action(
						$action,
						function ( $order_id ) use ( $sf_action ) {
							//if its not a sf order
							if ( ! Order::is_sf_order( $order_id ) ) {
								return;
							}
							try {
								$operations = new Operations( $order_id );
								if ( ! method_exists( $operations, $sf_action ) ) {
									ShoppingFeedHelper::get_logger()->error(
										sprintf(
										/* translators: %s: Action name. */
											__( 'Cant find any matched method for %s', 'shopping-feed' ),
											$sf_action
										),
										array(
											'source' => 'shopping-feed',
										)
									);

									return;
								}
								call_user_func( array( $operations, $sf_action ) );
							} catch ( Exception $exception ) {
								wc_get_order( $order_id )->add_order_note(
									sprintf(
									/* translators: %s: Action */
										__( 'Failed to %s order', 'shopping-feed' ),
										ucfirst( $sf_action )
									)
								);
								ShoppingFeedHelper::get_logger()->error(
									sprintf(
									/* translators: %1$s: Action. %2$s: Error Message */
										__( 'Failed to %1$s order => %2$s', 'shopping-feed' ),
										ucfirst( $sf_action ),
										$exception->getMessage()
									),
									array(
										'source' => 'shopping-feed',
									)
								);
							}
						}
					);
				}
			}
		}
	}


	/**
	 * Update product inventory
	 *
	 * @param $product_id
	 */
	public function update_stock( $product_id ) {
		$this->update_product( $product_id, true );
	}

	/**
	 * Update product's price & inventory
	 *
	 * @param $product_id
	 * @param bool $only_stock
	 *
	 * @return bool
	 */
	public function update_product( $product_id, $only_stock = false ) {
		$sdk = Sdk::get_instance();
		if ( ! $sdk->get_default_shop() instanceof StoreResource ) {
			throw new Exception(
				__( 'No store found', 'shopping-feed' )
			);
		}

		/** @var StoreResource $shop */
		$shop          = $sdk->get_default_shop();
		$pricing_api   = $shop->getPricingApi();
		$inventory_api = $shop->getInventoryApi();

		$this->pricing_update   = new \ShoppingFeed\Sdk\Api\Catalog\PricingUpdate();
		$this->inventory_update = new \ShoppingFeed\Sdk\Api\Catalog\InventoryUpdate();

		$product = new Product( $product_id );
		if ( $product->has_variations() ) {
			foreach ( $product->get_variations() as $variation ) {
				if ( empty( $variation['sku'] ) ) {
					ShoppingFeedHelper::get_logger()->warning(
						sprintf(
						/* translators: %s: Product ID. */
							__( 'Cant update product without SKU => %s', 'shopping-feed' ),
							$product_id
						),
						array(
							'source' => 'shopping-feed',
						)
					);

					continue;
				}

				$this->may_update_pricing( $variation['sku'], $variation['price'] );
				$this->may_update_inventory( $variation['sku'], $variation['quantity'] );
			}
		} else {
			if ( empty( $product->get_sku() ) ) {
				ShoppingFeedHelper::get_logger()->warning(
					sprintf(
					/* translators: %s: Product ID. */
						__( 'Cant update product without SKU => %s', 'shopping-feed' ),
						$product_id
					),
					array(
						'source' => 'shopping-feed',
					)
				);

				return false;
			}

			$this->may_update_pricing( $product->get_sku(), $product->get_price() );
			$this->may_update_inventory( $product->get_sku(), $product->get_quantity() );
		}
		/**
		 * Check if we need to update the price or only the stock
		 */
		if ( ! $only_stock ) {
			/**
			 * Send api request to update the price
			 */
			$pricing_api->execute( $this->pricing_update );
		}

		/**
		 * Send api request to update the inventory
		 */
		$inventory_api->execute( $this->inventory_update );

		return true;
	}

	/**
	 * Update price in SF if we have the price locally
	 *
	 * @param $sku
	 * @param $price
	 */
	private function may_update_pricing( $sku, $price ) {
		if ( empty( $price ) ) {
			ShoppingFeedHelper::get_logger()->warning(
				sprintf(
				/* translators: %s: Product SKU. */
					__( 'Cant update product without price => %s', 'shopping-feed' ),
					$sku
				),
				array(
					'source' => 'shopping-feed',
				)
			);

			return;
		}
		$this->pricing_update->add( $sku, $price );
	}

	/**
	 * Update quantity in SF if we have the quantity locally
	 *
	 * @param $sku
	 * @param $inventory
	 */
	private function may_update_inventory( $sku, $inventory ) {
		if ( ! is_numeric( $inventory ) ) {
			ShoppingFeedHelper::get_logger()->warning(
				sprintf(
				/* translators: %s: Product SKU. */
					__( 'Cant update product without quantity => %s', 'shopping-feed' ),
					$sku
				),
				array(
					'source' => 'shopping-feed',
				)
			);

			return;
		}
		$this->inventory_update->add( $sku, intval( $inventory ) );
	}
}
