<?php

namespace ShoppingFeed\ShoppingFeedWC\Tests\wpunit\Order;

use Automattic\WooCommerce\Internal\DataStores\Orders\OrdersTableDataStore;
use ShoppingFeed;
use ShoppingFeed\ShoppingFeedWC\Query\Query;

class OrderImportHposTest extends OrderImportTestCase {
	/**
	 * @var \WpunitTester
	 */
	protected $tester;

	private const IN_STOCK_PRODUCT_ID_USE_BY_ORDER_DATA = 12;

	public function setUp(): void {
		parent::setUp();

		add_filter( 'pre_option_woocommerce_custom_orders_table_enabled', [ $this, 'custom_orders_table' ] );
	}

	public function tearDown(): void {
		remove_filter( 'pre_option_woocommerce_custom_orders_table_enabled', [ $this, 'custom_orders_table' ] );

		parent::tearDown();
	}

	public function test_import_simple_order() {
		$product_before = wc_get_product( self::IN_STOCK_PRODUCT_ID_USE_BY_ORDER_DATA );
		$original_stock = $product_before->get_stock_quantity();

		$order_resource = $this->get_order_resource( 'simple-order' );
		$sf_order       = new ShoppingFeed\ShoppingFeedWC\Orders\Order( $order_resource );
		$sf_order->add();

		$product_after = wc_get_product( self::IN_STOCK_PRODUCT_ID_USE_BY_ORDER_DATA );
		$results       = wc_get_orders( [ Query::WC_META_SF_REFERENCE => $order_resource->getReference() ] );
		$this->assertCount( 1, $results, 'Assert only one order exist with the reference' );

		$wc_order = reset( $results );
		$this->assertEquals( $this->get_default_order_status(), $wc_order->get_status(), 'Assert the order use the correct status' );
		$this->assertEquals( 65, $wc_order->get_total(), 'Assert the order total match the value from ShoppingFeed' );
		$this->assertEquals( 1, $wc_order->get_item_count(), 'Assert the order contain the same number of product from ShoppingFeed' );
		$this->assertEquals( $original_stock - 1, $product_after->get_stock_quantity(), 'Assert stock for the product has been decreased' );
	}

	public function test_order_are_created_with_failed_status_if_original_order_contain_out_of_stock_products() {
		$order_resource = $this->get_order_resource( 'order-no-stock' );
		$sf_order       = new ShoppingFeed\ShoppingFeedWC\Orders\Order( $order_resource );
		$sf_order->add();

		$results  = wc_get_orders( [ Query::WC_META_SF_REFERENCE => $order_resource->getReference() ] );
		$wc_order = reset( $results );
		$this->assertEquals( $this->get_error_order_status(), $wc_order->get_status() );
	}

	public function test_order_fulfilled_by_channels_are_not_imported() {
		$order_resource = $this->get_order_resource( 'fulfilled-by-channel' );
		$orders         = ShoppingFeed\ShoppingFeedWC\Orders\Orders::get_instance();
		$this->assertNotTrue( $orders->can_import_order( $order_resource ) );
	}

	public function test_user_can_force_import_of_order_fulfilled_by_channels() {
		add_filter(
			'pre_option_sf_orders_options',
			function ( $value ) {
				return [
					'import_order_fulfilled_by_marketplace' => true,
				];
			}
		);

		$order_resource = $this->get_order_resource( 'fulfilled-by-channel' );
		$orders         = ShoppingFeed\ShoppingFeedWC\Orders\Orders::get_instance();
		$this->assertTrue( $orders->can_import_order( $order_resource ) );
	}

	public function test_orders_fulfilled_by_channel_dont_decrease_stock_when_imported() {
		add_filter(
			'pre_option_sf_orders_options',
			function ( $value ) {
				return [
					'import_order_fulfilled_by_marketplace' => true,
				];
			}
		);

		$product_before = wc_get_product( self::IN_STOCK_PRODUCT_ID_USE_BY_ORDER_DATA );
		$original_stock = $product_before->get_stock_quantity();

		$order_resource = $this->get_order_resource( 'fulfilled-by-channel' );
		$sf_order       = new ShoppingFeed\ShoppingFeedWC\Orders\Order( $order_resource );
		$sf_order->add();

		$product_after = wc_get_product( self::IN_STOCK_PRODUCT_ID_USE_BY_ORDER_DATA );
		$results       = wc_get_orders( [ Query::WC_META_SF_REFERENCE => $order_resource->getReference() ] );
		$wc_order      = reset( $results );
		$this->assertEquals( 'completed', $wc_order->get_status(), 'Orders fulfilled by channel are imported with the status "completed" by default.' );
		$this->assertTrue( (bool) $wc_order->get_meta( 'dont_update_inventory' ), 'Orders fulfilled by channel have a custom meta when imported.' );
		$this->assertEquals( $original_stock, $product_after->get_stock_quantity(), 'Orders fulfilled by channel don\'t change stock when imported.' );
	}

	public function test_order_exist() {
		$order_resource = $this->get_order_resource( 'simple-order' );
		$sf_order       = new ShoppingFeed\ShoppingFeedWC\Orders\Order( $order_resource );
		$sf_order->add();

		$this->assertEquals( OrdersTableDataStore::class, \WC_Data_Store::load( 'order' )->get_current_class_name() );
		$this->assertTrue( ShoppingFeed\ShoppingFeedWC\Orders\Order::exists( $order_resource ) );

		$order_resource_bis = $this->get_order_resource( 'simple-order' );
		$this->assertEquals( OrdersTableDataStore::class, \WC_Data_Store::load( 'order' )->get_current_class_name() );
		$this->assertFalse( ShoppingFeed\ShoppingFeedWC\Orders\Order::exists( $order_resource_bis ) );
	}

	public function custom_orders_table( $value ) {
		return 'yes';
	}
}
