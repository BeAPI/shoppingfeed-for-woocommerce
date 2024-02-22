<?php

namespace ShoppingFeed\ShoppingFeedWC\Addons\Plugins\PhWoocommerceShipmentTrackingProPlugin;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

/**
 * Integrate tracking data from WoocommerceShipmentTrackingPro with ShoppingFeed.
 * @link https://www.pluginhive.com/product/woocommerce-shipment-tracking-pro/
 *
 * @package ShoppingFeed\ShoppingFeedWC\Addons\Plugins\PhWoocommerceShipmentTrackingProPlugin
 */
class PhWoocommerceShipmentTrackingProPlugin {

	public function __construct() {
		if ( ! defined( 'PH_SHIPMENT_TRACKING_PLUGIN_VERSION' ) ) {
			return;
		}

		add_filter( 'shopping_feed_tracking_number', array( $this, 'get_tracking_number' ), 11, 2 );
		add_filter( 'shopping_feed_tracking_link', array( $this, 'get_tracking_link' ), 11, 2 );
	}

	/**
	 * Get Tracking Number.
	 *
	 * @param string $tracking_number
	 * @param \WC_Order|\WP_Error $wc_order
	 *
	 * @return string
	 */
	public function get_tracking_number( $tracking_number, $wc_order ) {
		if ( ! $wc_order instanceof \WC_Order ) {
			return $tracking_number;
		}

		$tracking_info = $this->get_tracking_data( $wc_order );
		if ( empty( $tracking_info ) ) {
			return $tracking_number;
		}

		$custom_tracking_numbers = wp_list_pluck( $tracking_info, 'tracking_id' );
		if ( empty( $custom_tracking_numbers ) ) {
			return $tracking_number;
		}

		return implode( ',', $custom_tracking_numbers );
	}

	/**
	 * Get Tracking Link.
	 *
	 * @param string $tracking_link
	 * @param \WC_Order|\WP_Error $wc_order
	 *
	 * @return string
	 */
	public function get_tracking_link( $tracking_link, $wc_order ) {
		if ( ! $wc_order instanceof \WC_Order ) {
			return $tracking_link;
		}

		$tracking_info = $this->get_tracking_data( $wc_order );
		if ( empty( $tracking_info ) ) {
			return $tracking_link;
		}

		$custom_tracking_links = wp_list_pluck( $tracking_info, 'tracking_link' );
		if ( empty( $custom_tracking_links ) ) {
			return $tracking_link;
		}

		return implode( ',', $custom_tracking_links );
	}

	/**
	 * Get tracking data from Woocommerce Shipment Tracking Pro plugin.
	 *
	 * @param \WC_Order $wc_order
	 *
	 * @return array
	 */
	private function get_tracking_data( \WC_Order $wc_order ) {
		$tracking_data = $wc_order->get_meta( 'wf_wc_shipment_result', true );
		if ( ! is_array( $tracking_data ) || ! isset( $tracking_data['tracking_info'] ) ) {
			return array();
		}

		return $tracking_data['tracking_info'];
	}
}
