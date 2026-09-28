<?php

namespace ShoppingFeed\ShoppingFeedWC\Tests\wpunit\Admin;

use ShoppingFeed\ShoppingFeedWC\ShipmentTracking\Provider\ShoppingfeedAdvanced;

class OrderTrackingMetaboxTest extends \Codeception\TestCase\WPTestCase {

	public function test_shoppingfeed_advanced_provider_reads_tracking_metas() {
		$provider = new ShoppingfeedAdvanced();
		$this->assertTrue( $provider->is_available() );

		$order = wc_create_order();
		$order->update_meta_data( \TRACKING_NUMBER_FIELD_SLUG, 'TRACK123' );
		$order->update_meta_data( \TRACKING_LINK_FIELD_SLUG, 'https://example.com/track' );
		$order->save();

		$data = $provider->get_tracking_data( $order );

		$this->assertTrue( $data->has_tracking_data() );
		$this->assertContains( 'TRACK123', $data->get_tracking_numbers() );
		$this->assertContains( 'https://example.com/track', $data->get_tracking_links() );
	}
}
