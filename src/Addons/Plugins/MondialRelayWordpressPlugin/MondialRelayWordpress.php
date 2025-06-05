<?php

namespace ShoppingFeed\ShoppingFeedWC\Addons\Plugins\MondialRelayWordpressPlugin;

use ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Api\Order\OrderResource;
use ShoppingFeed\ShoppingFeedWC\Orders\Order\Metas;
use ShoppingFeed\ShoppingFeedWC\Orders\Order\Products;
use ShoppingFeed\ShoppingFeedWC\ShoppingFeedHelper;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

/**
 * Class MondialRelayWordpress to manage the plugin mondialrelay-wordpress
 * @link https://mondialrelay-wp.com/en/home/
 * @package ShoppingFeed\ShoppingFeedWC\Addons\Plugins\MondialRelayWordpressPlugin
 */
class MondialRelayWordpress {

	private const MONDIAL_RELAY_SHIPPING_METHOD = 'mondialrelay_official_shipping';

	public function __construct() {
		if ( ! class_exists( \class_MRWP_main::class ) ) {
			return;
		}

		add_action( 'sf_add_metas', [ $this, 'add_meta' ] );
	}

	/**
	 * Add metadata used by MondialRelayWordPress plugin to generate labels.
	 *
	 * @param Metas $metas
	 *
	 * @return void
	 */
	public function add_meta( $metas ): void {
		$carrier = $metas->sf_order->getShipment()['carrier'] ?? false;
		if ( ! $carrier ) {
			return;
		}

		// Find matching shipping method name for the carrier
		$shipping_method_for_carrier = ShoppingFeedHelper::get_wc_shipping_from_sf_carrier( $carrier );
		if (
			! is_array( $shipping_method_for_carrier )
			|| empty( $shipping_method_for_carrier ) ||
			! isset( $shipping_method_for_carrier['method_rate_id'] )
		) {
			ShoppingFeedHelper::get_logger()->warning(
				sprintf(
					'[Mondial Relay] Order %s carrier is not associated with a Woocommerce shipping method.',
					$metas->sf_order->getReference()
				),
				array(
					'source'  => 'shopping-feed',
					'carrier' => esc_html( $carrier ),
				)
			);

			return;
		}

		// Check if shipping method match MondialRelay
		if ( self::MONDIAL_RELAY_SHIPPING_METHOD !== $shipping_method_for_carrier['method_rate_id'] ) {
			ShoppingFeedHelper::get_logger()->warning(
				sprintf(
					'[Mondial Relay] Order %s carrier is not associated with Mondial Relay shipping method.',
					$metas->sf_order->getReference()
				),
				array(
					'source'          => 'shopping-feed',
					'carrier'         => esc_html( $carrier ),
					'shipping_method' => esc_html( $shipping_method_for_carrier['method_rate_id'] ),
				)
			);

			return;
		}

		ShoppingFeedHelper::get_logger()->info(
			sprintf(
				'[Mondial Relay] order %s carrier is associated with Mondial Relay shipping method.',
				$metas->sf_order->getReference()
			),
			array(
				'source' => 'shopping-feed',
			)
		);

		// Get shipping method instance (use later to check the shipping code)
		$shipping_methods = \WC()->shipping()->get_shipping_methods();
		$method_name      = (string) $shipping_method_for_carrier['method_rate_id'];
		$instance_id      = (int) $shipping_method_for_carrier['method_id'];

		if ( ! isset( $shipping_methods[ $method_name ] ) ) {
			ShoppingFeedHelper::get_logger()->error(
				'[Mondial Relay] Failed to retrieve shipping method instance.',
				array(
					'source' => 'shopping-feed',
					'shipping_method' => sprintf( '%s:%s', $method_name, $instance_id ),
				)
			);

			return;
		}

		$classname = get_class( $shipping_methods[ $method_name ] );
		/* @var \WC_Shipping_Method $instance */
		$instance  = new $classname( $instance_id );

		// Get the relay id for the order.
		$relay_id = $metas->sf_order->getShippingAddress()['relayId'] ?? false;
		if ( ! $relay_id ) {
			ShoppingFeedHelper::get_logger()->warning(
				sprintf( '[Mondial Relay] no relay id found for the order %s', $metas->sf_order->getReference() ),
				array(
					'source' => 'shopping-feed',
				)
			);

			return;
		}

		ShoppingFeedHelper::get_logger()->info(
			sprintf(
				'[Mondial Relay] found relay id %s for order %s',
				$relay_id,
				$metas->sf_order->getReference()
			),
			array(
				'source' => 'shopping-feed',
			)
		);

		// Prefix relay id with the country code if available
		$country = $metas->sf_order->getShippingAddress()['country'] ?? false;
		if ( $country ) {
			$relay_id = sprintf( '%s-%s', strtoupper( $country ), $relay_id );
		}

		$metas->add_meta( 'Mondial Relay Parcel Shop ID', $relay_id, true );

		$formatted_address = $this->format_address( $metas->sf_order );
		$metas->add_meta( 'Mondial Relay Parcel Shop Address', $formatted_address, true );

		/**
		 * Code MED and APM are encoded as 24R
		 *
		 * @see \class_MRWP_main::save_mondial_relay_shipping_meta
		 */
		$shipping_code = $instance->get_instance_option( 'method', '' );
		if ( in_array( $shipping_code, [ 'MED', 'APM' ], true ) ) {
			$shipping_code = '24R';
		}

		$metas->add_meta( 'Mondial Relay Shipping Code', $shipping_code, true );

		$weight = $this->calculate_products_weight( $metas->sf_order );
		$metas->add_meta( 'Mondial Relay Parcel Weight', $weight, true );

		ShoppingFeedHelper::get_logger()->info(
			'[Mondial Relay] added metadata for MondialRelayWordpress',
			array(
				'source' => 'shopping-feed',
			)
		);
	}

	/**
	 * Format address to match MondialRelayWP
	 *
	 * The plugin use `-MRWP-` to mark line return.
	 *
	 * @return string
	 */
	private function format_address( OrderResource $sf_order ): string {
		$address_parts   = [];
		$address_parts[] = $sf_order->getShippingAddress()['company'] ?? '';
		$address_parts[] = $sf_order->getShippingAddress()['street'] ?? '';
		$address_parts[] = $sf_order->getShippingAddress()['street2'] ?? '';
		$address_parts[] = $sf_order->getShippingAddress()['postalCode'] ?? '';
		$address_parts[] = $sf_order->getShippingAddress()['city'] ?? '';
		$address_parts[] = $sf_order->getShippingAddress()['country'] ?? '';

		return implode( '-MRWP-', $address_parts );
	}

	/**
	 * Return the weight in grams for the order.
	 *
	 * @param OrderResource $sf_order
	 *
	 * @return int
	 */
	private function calculate_products_weight( OrderResource $sf_order ): int {
		$weight   = 0;
		$products = new Products( $sf_order );
		foreach ( $products->get_products() as $product ) {
			$product_id = 0 !== (int) $product['args']['variation_id'] ? (int) $product['args']['variation_id'] : $product['args']['product_id'];
			$wc_product = wc_get_product( $product_id );
			if ( ! $wc_product ) {
				continue;
			}

			$weight += ( (float) $wc_product->get_weight() ) * 1000; // convert weight to grams
		}

		return (int) $weight;
	}
}
