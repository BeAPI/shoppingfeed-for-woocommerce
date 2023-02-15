<?php

namespace ShoppingFeed\ShoppingFeedWC\Admin;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

class AsyncAPI {

	public function __construct() {
		add_action( 'sf_update_product_pricing', [ $this, 'update_product_pricing' ], 10, 2 );
		add_action( 'sf_update_product_inventory', [ $this, 'update_product_inventory' ], 10, 2 );
	}

	public function update_product_pricing( $pricing_api, $pricing_update ) {
		$pricing_api->execute( $pricing_update );
	}

	public function update_product_inventory( $inventory_api, $inventory_update ) {
		$inventory_api->execute( $inventory_update );
	}

}
