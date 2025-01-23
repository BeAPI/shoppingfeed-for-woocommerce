<?php

namespace ShoppingFeed\ShoppingFeedWC\Tests\wpunit\ShimpentTracking;

use ShoppingFeed\ShoppingFeedWC\ShipmentTracking\ShipmentTrackingData;
use ShoppingFeed\ShoppingFeedWC\ShipmentTracking\ShipmentTrackingProvider;

class DummyProvider implements ShipmentTrackingProvider {

	public function id(): string {
		return 'dummy';
	}

	public function name(): string {
		return 'Dummy';
	}

	public function is_available(): bool {
		return true;
	}

	public function get_tracking_data( \WC_Order $order ): ShipmentTrackingData {
		$data = new ShipmentTrackingData();
		foreach ( $order->get_meta( 'test_tracking' ) as $tracking ) {
			$data->add_tracking_data( $tracking[0], $tracking[1] );
		}

		return $data;
	}
}
