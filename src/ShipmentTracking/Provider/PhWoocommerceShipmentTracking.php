<?php

namespace ShoppingFeed\ShoppingFeedWC\ShipmentTracking\Provider;

use ShoppingFeed\ShoppingFeedWC\ShipmentTracking\ShipmentTrackingData;
use ShoppingFeed\ShoppingFeedWC\ShipmentTracking\ShipmentTrackingProvider;

/**
 * Provider for the WooCommerce Shipment Tracking Pro plugin.
 */
class PhWoocommerceShipmentTracking implements ShipmentTrackingProvider {

	public function id(): string {
		return 'woo_shipment_tracking';
	}

	public function name(): string {
		return 'WooCommerce Shipment Tracking Pro';
	}

	public function is_available(): bool {
		return defined( 'PH_SHIPMENT_TRACKING_PLUGIN_VERSION' );
	}

	public function get_tracking_data( \WC_Order $order ): ShipmentTrackingData {
		$tracking_data    = new ShipmentTrackingData();
		$ph_tracking_data = $order->get_meta( 'wf_wc_shipment_result' );
		if ( is_array( $ph_tracking_data ) && isset( $ph_tracking_data['tracking_info'] ) ) {
			foreach ( $ph_tracking_data as $ph_tracking_datum ) {
				$tracking_data->add_tracking_data(
					$ph_tracking_datum['tracking_id'],
					$ph_tracking_datum['tracking_link']
				);
			}
		}

		return $tracking_data;
	}
}
