<?php

namespace ShoppingFeed\ShoppingFeedWC\Tests\wpunit\Order;

use ShoppingFeed\Sdk\Api\Order\OrderResource;
use ShoppingFeed\Sdk\Hal\HalClient;
use ShoppingFeed\Sdk\Hal\HalResource;

class OrderImportTestCase extends \Codeception\TestCase\WPTestCase {

	protected function get_order_resource( string $name ): OrderResource {
		$order_id  = random_int( 10000000000, 90000000000 );
		$order_ref = uniqid( 'TEST-' );
		codecept_debug( $order_id );
		codecept_debug( $order_ref );

		$raw         = str_replace(
			[ '%order_id%', '%order_reference%' ],
			[ $order_id, $order_ref ],
			file_get_contents( __DIR__ . '/data/' . $name . '.json' )
		);
		$json        = json_decode( $raw, true );
		$client      = $this->createMock( HalClient::class );
		$halResource = $this
			->getMockBuilder( HalResource::class )
			->setConstructorArgs( [ $client, $json, $json['_links'], $json['_embedded'] ] )
			->setMethods( [ 'getFirstResources' ] )
			->getMock();

		return new OrderResource( $halResource );
	}

	protected function get_default_order_status(): string {
		$status = \ShoppingFeed\ShoppingFeedWC\ShoppingFeedHelper::get_sf_default_order_status();
		if ( str_starts_with( $status, 'wc-' ) ) {
			$status = substr( $status, 3 );
		}

		return $status;
	}

	protected function get_error_order_status(): string {
		return 'failed';
	}
}