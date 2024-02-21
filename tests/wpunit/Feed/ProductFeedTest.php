<?php

namespace ShoppingFeed\ShoppingFeedWC\Tests\wpunit\Feed;

use ShoppingFeed\ShoppingFeedWC\Products\Product;
use ShoppingFeed\ShoppingFeedWC\Products\Products;

class ProductFeedTest extends \Codeception\TestCase\WPTestCase {
	/**
	 * @var \WpunitTester
	 */
	protected $tester;

	/**
	 * @covers \ShoppingFeed\ShoppingFeedWC\Products\Product::get_sku
	 */
	public function test_get_sku_for_feed_return_product_id_when_configured_to_use_identifier() {
		$wc_product = wc_get_product( 13 );
		$sf_product = new Product( $wc_product );
		$this->assertEquals( '13', $sf_product->get_sku() );
	}

	/**
	 * @covers \ShoppingFeed\ShoppingFeedWC\Products\Product::get_sku
	 */
	public function test_get_sku_for_feed_return_product_sku_when_configured_to_use_sku() {

		add_filter(
			'pre_option_sf_feed_options',
			function ( $value ) {
				return [
					'product_identifier' => 'sku',
				];
			}
		);

		$wc_product = wc_get_product( 13 );
		$sf_product = new Product( $wc_product );
		$this->assertEquals( 'woo-tshirt', $sf_product->get_sku() );
	}

	/**
	 * @covers \ShoppingFeed\ShoppingFeedWC\Products\Product::get_name
	 */
	public function test_get_name_for_feed() {
		$wc_product = wc_get_product( 13 );
		$sf_product = new Product( $wc_product );
		$this->assertEquals( 'T-Shirt', $sf_product->get_name() );
	}

	/**
	 * @covers \ShoppingFeed\ShoppingFeedWC\Products\Product::get_price
	 */
	public function test_get_price_for_feed() {
		$wc_product = wc_get_product( 13 );
		$sf_product = new Product( $wc_product );
		$this->assertEquals( 18, $sf_product->get_price() );
	}

	/**
	 * @covers \ShoppingFeed\ShoppingFeedWC\Products\Product::get_link
	 */
	public function test_get_link_for_feed() {
		$wc_product = wc_get_product( 13 );
		$sf_product = new Product( $wc_product );
		$this->assertStringEndsWith( '/product/t-shirt/', $sf_product->get_link() );
	}

	/**
	 * @covers \ShoppingFeed\ShoppingFeedWC\Products\Product::get_variations
	 */
	public function test_simple_product_return_empty_array() {
		$wc_product = wc_get_product( 13 );
		$sf_product = new Product( $wc_product );
		$this->assertEquals( [], $sf_product->get_variations() );
	}

	/**
	 * @covers \ShoppingFeed\ShoppingFeedWC\Products\Product::get_variations
	 */
	public function test_variable_product_return_variation_array() {
		$wc_product = wc_get_product( 11 );
		$sf_product = new Product( $wc_product );
		$this->assertCount( 4, $sf_product->get_variations(), 'assert variable product has 4 variations' );

		$variation = $sf_product->get_variations()[0];
		$this->assertArrayHasKey( 'id', $variation, 'assert variation data has an id ' );
		$this->assertArrayHasKey( 'sku', $variation, 'assert variation data has a sku' );
		$this->assertArrayHasKey( 'ean', $variation, 'assert variation data has an ean' );
		$this->assertArrayHasKey( 'quantity', $variation, 'assert variation data has a quantity' );
		$this->assertArrayHasKey( 'price', $variation, 'assert variation data has a price' );
		$this->assertArrayHasKey( 'discount', $variation, 'assert variation data has a discount' );
		$this->assertArrayHasKey( 'attributes', $variation, 'assert variation data has attributes' );
	}

	/**
	 * @covers \ShoppingFeed\ShoppingFeedWC\Products\Product::get_ean
	 */
	public function test_get_ean_return_emty_string_for_empty_wc_product_ean() {
		$wc_product = wc_get_product( 13 );
		$sf_product = new Product( $wc_product );
		$this->assertEquals( '', $sf_product->get_ean() );
	}

	/**
	 * @covers \ShoppingFeed\ShoppingFeedWC\Products\Product::get_ean
	 */
	public function test_get_ean_return_meta_value_for_meta_name_wc_product_ean() {
		$wc_product = wc_get_product( 13 );
		$sf_product = new Product( $wc_product );

		$wc_product->add_meta_data( 'custom_ean_meta', 'custom-ean', true );
		$wc_product->save();

		add_filter(
			'shopping_feed_custom_ean',
			function ( $value ) {
				return 'custom_ean_meta';
			}
		);

		$this->assertEquals( 'custom-ean', $sf_product->get_ean() );
	}

	/**
	 * @covers \ShoppingFeed\ShoppingFeedWC\Products\Products::get_list_args
	 */
	public function test_get_products_for_feed_query_args() {
		$products_query_args = Products::get_instance()->get_list_args();
		$this->assertEqualSets(
			array(
				'limit'        => - 1,
				'orderby'      => 'date',
				'order'        => 'DESC',
				'status'       => 'publish',
				'stock_status' => 'instock',
			),
			$products_query_args
		);

		add_filter(
			'pre_option_sf_feed_options',
			function ( $value ) {
				return [
					'out_of_stock_products_in_feed' => 'on',
				];
			}
		);

		$products_query_args = Products::get_instance()->get_list_args();
		$this->assertEqualSets(
			array(
				'limit'        => - 1,
				'orderby'      => 'date',
				'order'        => 'DESC',
				'status'       => 'publish',
				'stock_status' => [ 'instock', 'outofstock' ],
			),
			$products_query_args
		);
	}
}
