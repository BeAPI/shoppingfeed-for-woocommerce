<?php

namespace ShoppingFeed\ShoppingFeedWC\ShipmentTracking\Provider;

use ShoppingFeed\ShoppingFeedWC\ShipmentTracking\ShipmentTrackingData;
use ShoppingFeed\ShoppingFeedWC\ShipmentTracking\ShipmentTrackingProvider;

/**
 * Provider for the ShoppingFeed Advanced plugin.
 */
class ShoppingfeedAdvanced implements ShipmentTrackingProvider {

	/**
	 * @inheritDoc
	 */
	public function id(): string {
		return 'sf_advanced';
	}

	/**
	 * @inheritDoc
	 */
	public function name(): string {
		return 'ShoppingFeed Advanced';
	}

	/**
	 * @inheritDoc
	 */
	public function is_available(): bool {
		return defined( 'SFA_PLUGIN_VERSION' );
	}

	/**
	 * @inheritDoc
	 */
	public function get_tracking_data( \WC_Order $order ): ShipmentTrackingData {
		$tracking_data   = new ShipmentTrackingData();
		$tracking_number = (string) $order->get_meta( TRACKING_NUMBER_FIELD_SLUG );
		$tracking_link   = (string) $order->get_meta( TRACKING_LINK_FIELD_SLUG );

		if ( ! empty( $tracking_number ) ) {
			$tracking_data->add_tracking_data(
				$tracking_number,
				! empty( $tracking_link ) ? $tracking_link : '',
			);
		}

		return $tracking_data;
	}
}
