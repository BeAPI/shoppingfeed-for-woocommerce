<?php

namespace ShoppingFeed\ShoppingFeedWC\Tests\wpunit\Order;

use ShoppingFeed;
use ShoppingFeed\Sdk\Api\Order\OrderResource;
use ShoppingFeed\Sdk\Hal\{HalClient, HalResource};
use ShoppingFeed\ShoppingFeedWC\Query\Query;

class OrderImportTest extends \Codeception\TestCase\WPTestCase {
	/**
	 * @var \WpunitTester
	 */
	protected $tester;

	public function test_import_simple_order() {
		$order_resource = $this->get_order_resource( 'simple-order' );
		$sf_order       = new ShoppingFeed\ShoppingFeedWC\Orders\Order( $order_resource );
		$sf_order->add();

		$results = wc_get_orders( [ Query::WC_META_SF_REFERENCE => $order_resource->getReference() ] );
		$this->assertCount( 1, $results, 'Assert only one order exist with the reference' );

		$wc_order = reset( $results );
		$this->assertEquals( $this->get_default_order_status(), $wc_order->get_status(), 'Assert the order use the correct status' );
		$this->assertEquals( 62, $wc_order->get_total(), 'Assert the order total match the value from ShoppingFeed' );
		$this->assertEquals( 1, $wc_order->get_item_count(), 'Assert the order contain the same number of product from ShoppingFeed' );
	}

	public function test_order_are_created_with_failed_status_if_original_order_contain_out_of_stock_products() {
		$order_resource = $this->get_order_resource( 'order-no-stock' );
		$sf_order       = new ShoppingFeed\ShoppingFeedWC\Orders\Order( $order_resource );
		$sf_order->add();

		$results  = wc_get_orders( [ Query::WC_META_SF_REFERENCE => $order_resource->getReference() ] );
		$wc_order = reset( $results );
		$this->assertEquals( $this->get_error_order_status(), $wc_order->get_status() );
	}

	private function get_order_resource( string $name ): OrderResource {
		$raw         = file_get_contents( __DIR__ . '/data/' . $name . '.json' );
		$json        = json_decode( $raw, true );
		$client      = $this->createMock( HalClient::class );
		$halResource = $this
			->getMockBuilder( HalResource::class )
			->setConstructorArgs( [ $client, $json, $json['_links'], $json['_embedded'] ] )
			->setMethods( [ 'getFirstResources' ] )
			->getMock();

		return new OrderResource( $halResource );
	}

	private function get_default_order_status(): string {
		$status = \ShoppingFeed\ShoppingFeedWC\ShoppingFeedHelper::get_sf_default_order_status();
		if ( str_starts_with( $status, 'wc-' ) ) {
			$status = substr( $status, 3 );
		}

		return $status;
	}

	private function get_error_order_status(): string {
		return 'failed';
	}
}
