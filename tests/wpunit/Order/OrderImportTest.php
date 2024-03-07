<?php

namespace ShoppingFeed\ShoppingFeedWC\Tests\wpunit\Order;

use ShoppingFeed;

class OrderImportTest extends OrderImportTestCase {
	/**
	 * @var \WpunitTester
	 */
	protected $tester;

	public function test_can_import_order_fulfilledby_store() {
		$order_resource = $this->get_order_resource( 'simple-order' );
		$orders         = ShoppingFeed\ShoppingFeedWC\Orders\Orders::get_instance();
		$this->assertTrue( $orders->can_import_order( $order_resource ) );
	}

	public function test_cannot_import_anonymized_order() {
		$order_resource = $this->get_order_resource( 'order-anonymized' );
		$orders         = ShoppingFeed\ShoppingFeedWC\Orders\Orders::get_instance();
		$this->assertWPError( $orders->can_import_order( $order_resource ) );
	}

	public function test_cannot_import_order_fulfilledby_store_with_incorrect_status() {
		$order_resource = $this->get_order_resource( 'order-fulfilled-by-store-shipped' );
		$orders         = ShoppingFeed\ShoppingFeedWC\Orders\Orders::get_instance();
		$this->assertWPError( $orders->can_import_order( $order_resource ) );
	}

	public function test_cannot_import_order_fulfilledby_channel_by_default() {
		$order_resource = $this->get_order_resource( 'fulfilled-by-channel' );
		$orders         = ShoppingFeed\ShoppingFeedWC\Orders\Orders::get_instance();
		$this->assertWPError( $orders->can_import_order( $order_resource ) );
	}

	public function test_can_import_order_fulfilledby_channel_if_flag_is_true() {
		$order_resource = $this->get_order_resource( 'fulfilled-by-channel' );
		$orders         = ShoppingFeed\ShoppingFeedWC\Orders\Orders::get_instance();
		$this->assertTrue( $orders->can_import_order( $order_resource, true ) );
	}
}
