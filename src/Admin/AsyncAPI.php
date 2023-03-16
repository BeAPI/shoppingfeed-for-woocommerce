<?php

namespace ShoppingFeed\ShoppingFeedWC\Admin;

// Exit on direct access
use ShoppingFeed\Sdk\Api\Catalog\InventoryUpdate;
use ShoppingFeed\Sdk\Api\Catalog\PricingUpdate;
use ShoppingFeed\ShoppingFeedWC\ShoppingFeedHelper;

defined( 'ABSPATH' ) || exit;

class AsyncAPI {

	public function __construct() {
		add_action( 'sf_update_product_pricing', [ $this, 'update_product_pricing' ], 10, 2 );
		add_action( 'sf_update_product_inventory', [ $this, 'update_product_inventory' ], 10, 2 );
	}

	/**
	 * Update products pricings in async mode
	 *
	 * @param $pricing_api
	 * @param $pricing_update
	 *
	 * @return void
	 */
	public function update_product_pricing( $pricing_api, $pricing_update ) {
		if ( ! $pricing_update instanceof PricingUpdate ) {
			ShoppingFeedHelper::get_logger()->error(
				sprintf(
					__( 'pricing_update variable not instance of PricingUpdate class', 'shopping-feed' )
				),
				[
					'source' => 'shopping-feed',
				]
			);

			return;
		}

		$pricing_api->execute( $pricing_update );
	}

	/**
	 * Update products inventory in async mode
	 *
	 * @param $inventory_api
	 * @param $inventory_update
	 *
	 * @return void
	 */
	public function update_product_inventory( $inventory_api, $inventory_update ) {
		if ( ! $inventory_update instanceof InventoryUpdate ) {
			ShoppingFeedHelper::get_logger()->error(
				sprintf(
					__( 'inventory_update variable not instance of InventoryUpdate class', 'shopping-feed' )
				),
				[
					'source' => 'shopping-feed',
				]
			);

			return;
		}

		$inventory_api->execute( $inventory_update );
	}

}
