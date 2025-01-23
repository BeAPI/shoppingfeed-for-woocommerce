<?php

namespace ShoppingFeed\ShoppingFeedWC\ShipmentTracking\Provider;

use ShoppingFeed\ShoppingFeedWC\ShipmentTracking\ShipmentTrackingData;
use ShoppingFeed\ShoppingFeedWC\ShipmentTracking\ShipmentTrackingProvider;

/**
 * Provider for the Advanced Shipment Tracking / Advanced Shipment Tracking Pro plugin.
 */
class AdvancedShipmentTracking implements ShipmentTrackingProvider {

	/**
	 * @inerhitDoc
	 */
	public function id(): string {
		return 'advanced_shipment_tracking';
	}

	/**
	 * @inerhitDoc
	 */
	public function name(): string {
		return 'Advanced Shipment Tracking / Advanced Shipment Tracking Pro';
	}

	/**
	 * @inerhitDoc
	 */
	public function is_available(): bool {
		return defined( 'SHIPMENT_TRACKING_PATH' );
	}

	/**
	 * @inheritDoc
	 */
	public function get_tracking_data( \WC_Order $order ): ShipmentTrackingData {
		$tracking_data = new ShipmentTrackingData();
		$tracking_info = [];
		if ( function_exists( 'ast_get_tracking_items' ) ) { // AST >= 3.0 & AST Pro
			$tracking_info = ast_get_tracking_items( $order->get_id() );
		} elseif ( class_exists( '\WC_Advanced_Shipment_Tracking_Actions' ) ) { // AST < 3.0
			$ast           = \WC_Advanced_Shipment_Tracking_Actions::get_instance();
			$tracking_info = $ast->get_tracking_items( $order->get_id(), true );
		}

		if ( is_array( $tracking_info ) ) {
			foreach ( $tracking_info as $tracking_item ) {
				$tracking_data->add_tracking_data(
					$tracking_item['tracking_number'],
					$tracking_item['formatted_tracking_link']
				);
			}
		}

		return $tracking_data;
	}
}
