<?php

namespace ShoppingFeed\ShoppingFeedWC\ShipmentTracking\Provider;

use ShoppingFeed\ShoppingFeedWC\ShipmentTracking\ShipmentTrackingData;
use ShoppingFeed\ShoppingFeedWC\ShipmentTracking\ShipmentTrackingProvider;

class NullShipmentTracking implements ShipmentTrackingProvider {

	/**
	 * @inheritDoc
	 */
	public function id(): string {
		return 'default';
	}

	/**
	 * @inheritDoc
	 */
	public function name(): string {
		return 'Default';
	}

	/**
	 * @inheritDoc
	 */
	public function is_available(): bool {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function get_tracking_data( \WC_Order $order ): ShipmentTrackingData {
		return new ShipmentTrackingData();
	}
}
