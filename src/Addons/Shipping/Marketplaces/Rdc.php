<?php

namespace ShoppingFeed\ShoppingFeedWC\Addons\Shipping\Marketplaces;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

use ShoppingFeed\ShoppingFeedWC\Orders\Order\Shipping;

class Rdc {
	public function __construct() {
		add_action( 'sf_set_shipping_method_and_colis_number', array( $this, 'set_shipping_method_and_colis_number' ) );
	}

	/**
	 * @param $shipping Shipping
	 */
	public function set_shipping_method_and_colis_number( $shipping ) {
		if (
			'RDC' !== $shipping->sf_order->getChannel()->getName() &&
			$shipping->sf_order->getChannel()->getId() !== 51
		) {
			return;
		}

		$sf_carrier = $shipping->sf_order->getShipment()['carrier'];

		$text                   = explode( ' ', str_replace( 'Livraison en point de proximité avec ', '', $sf_carrier ) );
		$shipping->colis_number = end( $text );
		unset( $text[ count( $text ) - 1 ] );
		$shipping->method = implode( ' ', $text );
	}
}
