<?php

namespace ShoppingFeed\ShoppingFeedWC\Addons\Shipping\Marketplaces;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

use ShoppingFeed\ShoppingFeedWC\Addons\Marketplace;
use ShoppingFeed\ShoppingFeedWC\Orders\Order\Shipping;

class Cdiscount {

	use Marketplace;

	public function __construct() {
		add_action( 'sf_set_shipping_method_and_colis_number', array( $this, 'set_shipping_method_and_colis_number' ) );
	}

	/**
	 * @param $shipping Shipping
	 */
	public function set_shipping_method_and_colis_number( $shipping ) {
		if ( false === $this->is_cdiscount( $shipping->sf_order ) ) {
			return;
		}

		$shipping->colis_number = ! empty( $shipping->sf_order->toArray()['shippingAddress']['other'] ) ? $shipping->sf_order->toArray()['shippingAddress']['other'] : '';
	}
}
