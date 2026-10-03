<?php

namespace ShoppingFeed\ShoppingFeedWC\Tests\wpunit\Admin;

use ShoppingFeed\ShoppingFeedWC\Admin\OrderTrackingMetabox;
use ShoppingFeed\ShoppingFeedWC\ShipmentTracking\Provider\ShoppingfeedAdvanced;

class OrderTrackingMetaboxTest extends \Codeception\TestCase\WPTestCase {

	public function tearDown(): void {
		unset( $_POST['_sfa_tracking_nonce'], $_POST[ \TRACKING_NUMBER_FIELD_SLUG ], $_POST[ \TRACKING_LINK_FIELD_SLUG ] );
		parent::tearDown();
	}

	public function test_save_metabox_persists_tracking_for_shop_manager() {
		$shop_manager_id = $this->factory->user->create( array( 'role' => 'shop_manager' ) );
		wp_set_current_user( $shop_manager_id );

		$order = wc_create_order();
		$order->save();

		$_POST['_sfa_tracking_nonce']              = wp_create_nonce( sprintf( 'save_sfa_tracking_%d', $order->get_id() ) );
		$_POST[ \TRACKING_NUMBER_FIELD_SLUG ]       = 'SAVE-TRACK-456';
		$_POST[ \TRACKING_LINK_FIELD_SLUG ]         = 'https://example.com/saved-track';

		$metabox = new OrderTrackingMetabox();
		$metabox->save_metabox( $order->get_id() );

		$order = wc_get_order( $order->get_id() );
		$this->assertSame( 'SAVE-TRACK-456', $order->get_meta( \TRACKING_NUMBER_FIELD_SLUG ) );
		$this->assertSame( 'https://example.com/saved-track', $order->get_meta( \TRACKING_LINK_FIELD_SLUG ) );
	}

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
