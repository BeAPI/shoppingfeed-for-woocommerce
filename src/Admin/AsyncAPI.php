<?php

namespace ShoppingFeed\ShoppingFeedWC\Admin;

// Exit on direct access
use ShoppingFeed\Sdk\Api\Catalog\InventoryUpdate;
use ShoppingFeed\Sdk\Api\Catalog\PricingUpdate;
use ShoppingFeed\Sdk\Api\Store\StoreResource;
use ShoppingFeed\ShoppingFeedWC\Products\Product;
use ShoppingFeed\ShoppingFeedWC\Sdk\Sdk;
use ShoppingFeed\ShoppingFeedWC\ShoppingFeedHelper;
use ShoppingFeed\ShoppingFeedWC\Admin\WoocommerceActions;

defined( 'ABSPATH' ) || exit;

class AsyncAPI {

	public function __construct() {
		add_action( 'sf_async_update_product', [ $this, 'update_product' ], 10, 3 );
	}

	/**
	 * Update products in async mode
	 *
	 * @param $pricing_api
	 * @param $pricing_update
	 *
	 * @return void
	 */
	public function update_product( $woocommerce_actions, $product_id, $only_stock ) {

		$sf_accounts = ShoppingFeedHelper::get_sf_account_options();
		if ( empty( $sf_accounts ) ) {
			ShoppingFeedHelper::get_logger()->error(
				__( 'No Accounts founds', 'shopping-feed' ),
				array(
					'source' => 'shopping-feed',
				)
			);

			return;
		}

		foreach ( $sf_accounts as $sf_account ) {
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
				continue;
			}

			$pricing_api   = $shop->getPricingApi();
			$inventory_api = $shop->getInventoryApi();

			$pricing_update   = new PricingUpdate();
			$inventory_update = new InventoryUpdate();

			$product = new Product( $product_id );
			if ( $product->has_variations() ) {
				if ( ! $woocommerce_actions->update_variation_product( $product ) ) {
					continue;
				}
			} else {
				if ( ! $woocommerce_actions->update_normal_product( $product ) ) {
					continue;
				}
			}
			/**
			 * Check if we need to update the price or only the stock
			 */
			if ( ! $only_stock ) {
				/**
				 *  Send api request to update the price
				 */
				$pricing_api->execute( $pricing_update );
			}

			/**
			 * Send api request to update the inventory
			 */
			$inventory_api->execute( $inventory_update );
		}
	}
}
