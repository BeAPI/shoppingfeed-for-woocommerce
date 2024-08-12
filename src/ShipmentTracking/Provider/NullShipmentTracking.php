<?php

namespace ShoppingFeed\ShoppingFeedWC\ShipmentTracking\Provider;

use ShoppingFeed\ShoppingFeedWC\ShipmentTracking\ShipmentTrackingProvider;

class NullShipmentTracking implements ShipmentTrackingProvider {

	public function id(): string {
		return 'default';
	}

	public function name(): string {
		return 'Default';
	}

	public function is_available(): bool {
		return true;
	}

	public function get_tracking_data( \WC_Order $order ): ShipmentTrackingData {
		return new ShipmentTrackingData();
	}
}
