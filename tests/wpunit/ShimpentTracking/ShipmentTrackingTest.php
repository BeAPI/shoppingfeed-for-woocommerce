<?php

namespace ShoppingFeed\ShoppingFeedWC\Tests\wpunit\ShimpentTracking;

use ShoppingFeed\ShoppingFeedWC\ShipmentTracking\Provider\NullShipmentTracking;
use ShoppingFeed\ShoppingFeedWC\ShipmentTracking\ShipmentTrackingManager;
use ShoppingFeed\ShoppingFeedWC\ShoppingFeedHelper;

class ShipmentTrackingTest extends \Codeception\TestCase\WPTestCase {
	/**
	 * @var \WpunitTester
	 */
	protected $tester;

	public function setUp(): void {
		parent::setUp();

		$dummy_provider = new DummyProvider();
		add_filter( 'sf_shipment_tracking_providers', function ( $providers ) use ( $dummy_provider ) {
			$providers[] = $dummy_provider;

			return $providers;
		} );
	}

	public function test_manager_return_fallback_if_selected_provider_doesnt_exist() {
		$options = [
			'tracking_provider' => 'invalid_provider',
		];
		$manager = ShipmentTrackingManager::create( $options );

		$this->assertInstanceOf( NullShipmentTracking::class, $manager->get_selected_provider() );
	}

	public function test_manager_return_fallback_if_selected_provider_is_not_available() {
		$options = [
			'tracking_provider' => 'sf_advanced'
		];
		$manager = ShipmentTrackingManager::create( $options );

		$this->assertInstanceOf( NullShipmentTracking::class, $manager->get_selected_provider() );
	}

	public function test_wc_tracking_number() {
		// Use Dummy as provider
		add_filter( 'pre_option_sf_shipping_options', function () {
			return [
				'tracking_provider' => 'dummy'
			];
		} );

		// Mock order
		$order_mock = $this->getMockBuilder( \WC_Order::class )->getMock();
		$order_mock->expects( $this->once() )
		           ->method( 'get_meta' )
		           ->with( 'test_tracking' )
		           ->willReturn( [ [ '60XXXXXX', 'https://example.com/track/60XXXXXX' ] ] );

		$this->assertEquals(
			'60XXXXXX',
			ShoppingFeedHelper::wc_tracking_number( $order_mock )
		);
	}

	public function test_wc_tracking_number_multiple() {
		// Use Dummy as provider
		add_filter( 'pre_option_sf_shipping_options', function () {
			return [
				'tracking_provider' => 'dummy'
			];
		} );

		// Mock order
		$order_mock = $this->getMockBuilder( \WC_Order::class )->getMock();
		$order_mock->expects( $this->once() )
		           ->method( 'get_meta' )
		           ->with( 'test_tracking' )
		           ->willReturn(
			           [
				           [ '60XXXXXX', 'https://example.com/track/60XXXXXX' ],
				           [ '70XXXXXX', 'https://example.com/track/70XXXXXX' ]
			           ]
		           );

		$this->assertEquals(
			'60XXXXXX,70XXXXXX',
			ShoppingFeedHelper::wc_tracking_number( $order_mock )
		);
	}

	public function test_wc_tracking_number_filter() {
		// Use Dummy as provider
		add_filter( 'pre_option_sf_shipping_options', function () {
			return [
				'tracking_provider' => 'dummy'
			];
		} );

		// Mock order
		$order_mock = $this->getMockBuilder( \WC_Order::class )->getMock();
		$order_mock->expects( $this->once() )
		           ->method( 'get_meta' )
		           ->with( 'test_tracking' )
		           ->willReturn( [ [ '60XXXXXX', 'https://example.com/track/60XXXXXX' ] ] );

		add_filter( 'shopping_feed_tracking_number', function () {
			return '123456';
		} );

		$this->assertEquals(
			'123456',
			ShoppingFeedHelper::wc_tracking_number( $order_mock )
		);
	}

	public function test_wc_tracking_number_filter_compat_meta() {
		// Use Dummy as provider
		add_filter( 'pre_option_sf_shipping_options', function () {
			return [
				'tracking_provider' => 'dummy'
			];
		} );

		// Mock order
		$order_mock = $this->getMockBuilder( \WC_Order::class )->getMock();
		$order_mock->expects( $this->exactly( 2 ) )
		           ->method( 'get_meta' )
		           ->withConsecutive( [ 'test_tracking' ], [ 'my_tracking_number' ] )
		           ->willReturnOnConsecutiveCalls( [ [ '60XXXXXX', 'https://example.com/track/60XXXXXX' ] ], '123456' );
		$order_mock->expects( $this->once() )
		           ->method( 'meta_exists' )
		           ->with( 'my_tracking_number' )
		           ->willReturn( true );

		add_filter( 'shopping_feed_tracking_number', function () {
			return 'my_tracking_number';
		} );

		$this->assertEquals(
			'123456',
			ShoppingFeedHelper::wc_tracking_number( $order_mock )
		);
	}

	public function test_wc_tracking_number_filter_compat_sf_advanced() {
		// Use Dummy as provider
		add_filter( 'pre_option_sf_shipping_options', function () {
			return [
				'tracking_provider' => 'dummy'
			];
		} );

		// Mock order
		$order_mock = $this->getMockBuilder( \WC_Order::class )->getMock();
		$order_mock->expects( $this->once() )
		           ->method( 'get_meta' )
		           ->willReturn( [ [ '60XXXXXX', 'https://example.com/track/60XXXXXX' ] ] );
		$order_mock->expects( $this->once() )
		           ->method( 'meta_exists' )
		           ->with( '60XXXXXX' );

		define( 'TRACKING_NUMBER_FIELD_SLUG', 'sf_advanced_tracking_number_field' );
		add_filter( 'shopping_feed_tracking_number', function () {
			return TRACKING_NUMBER_FIELD_SLUG;
		} );

		$this->assertEquals(
			'60XXXXXX',
			ShoppingFeedHelper::wc_tracking_number( $order_mock )
		);
	}

	public function test_wc_tracking_link() {
		// Use Dummy as provider
		add_filter( 'pre_option_sf_shipping_options', function () {
			return [
				'tracking_provider' => 'dummy'
			];
		} );

		// Mock order
		$order_mock = $this->getMockBuilder( \WC_Order::class )->getMock();
		$order_mock->expects( $this->once() )
		           ->method( 'get_meta' )
		           ->willReturn( [ [ '60XXXXXX', 'https://example.com/track/60XXXXXX' ] ] );

		$this->assertEquals(
			'https://example.com/track/60XXXXXX',
			ShoppingFeedHelper::wc_tracking_link( $order_mock )
		);
	}

	public function test_wc_tracking_link_multiple() {
		// Use Dummy as provider
		add_filter( 'pre_option_sf_shipping_options', function () {
			return [
				'tracking_provider' => 'dummy'
			];
		} );

		// Mock order
		$order_mock = $this->getMockBuilder( \WC_Order::class )->getMock();
		$order_mock->expects( $this->once() )
		           ->method( 'get_meta' )
		           ->willReturn(
			           [
				           [ '60XXXXXX', 'https://example.com/track/60XXXXXX' ],
				           [ '70XXXXXX', 'https://example.com/track/70XXXXXX' ]
			           ]
		           );

		$this->assertEquals(
			'https://example.com/track/60XXXXXX,https://example.com/track/70XXXXXX',
			ShoppingFeedHelper::wc_tracking_link( $order_mock )
		);
	}

	public function test_wc_tracking_link_filter() {
		// Use Dummy as provider
		add_filter( 'pre_option_sf_shipping_options', function () {
			return [
				'tracking_provider' => 'dummy'
			];
		} );

		// Mock order
		$order_mock = $this->getMockBuilder( \WC_Order::class )->getMock();
		$order_mock->expects( $this->once() )
		           ->method( 'get_meta' )
		           ->willReturn( [ [ '60XXXXXX', 'https://example.com/track/60XXXXXX' ] ] );

		add_filter( 'shopping_feed_tracking_link', function () {
			return 'https://example.com/track/123456';
		} );

		$this->assertEquals(
			'https://example.com/track/123456',
			ShoppingFeedHelper::wc_tracking_link( $order_mock )
		);
	}

	public function test_wc_tracking_link_filter_compat_meta() {
		// Use Dummy as provider
		add_filter( 'pre_option_sf_shipping_options', function () {
			return [
				'tracking_provider' => 'dummy'
			];
		} );

		// Mock order
		$order_mock = $this->getMockBuilder( \WC_Order::class )->getMock();
		$order_mock->expects( $this->exactly( 2 ) )
		           ->method( 'get_meta' )
		           ->withConsecutive( [ 'test_tracking' ], [ 'my_tracking_link' ] )
		           ->willReturnOnConsecutiveCalls(
			           [ [ '60XXXXXX', 'https://example.com/track/60XXXXXX' ] ],
			           'https://example.com/track/123456'
		           );
		$order_mock->expects( $this->once() )
		           ->method( 'meta_exists' )
		           ->with( 'my_tracking_link' )
		           ->willReturn( true );

		add_filter( 'shopping_feed_tracking_link', function () {
			return 'my_tracking_link';
		} );

		$this->assertEquals(
			'https://example.com/track/123456',
			ShoppingFeedHelper::wc_tracking_link( $order_mock )
		);
	}

	public function test_wc_tracking_link_filter_compat_sf_advanced() {
		// Use Dummy as provider
		add_filter( 'pre_option_sf_shipping_options', function () {
			return [
				'tracking_provider' => 'dummy'
			];
		} );

		// Mock order
		$order_mock = $this->getMockBuilder( \WC_Order::class )->getMock();
		$order_mock->expects( $this->once() )
		           ->method( 'get_meta' )
		           ->willReturn( [ [ '60XXXXXX', 'https://example.com/track/60XXXXXX' ] ] );
		$order_mock->expects( $this->once() )
		           ->method( 'meta_exists' )
		           ->with( 'https://example.com/track/60XXXXXX' );

		define( 'TRACKING_LINK_FIELD_SLUG', 'sf_advanced_tracking_link_field' );
		add_filter( 'shopping_feed_tracking_link', function () {
			return TRACKING_LINK_FIELD_SLUG;
		} );

		$this->assertEquals(
			'https://example.com/track/60XXXXXX',
			ShoppingFeedHelper::wc_tracking_link( $order_mock )
		);
	}
}
