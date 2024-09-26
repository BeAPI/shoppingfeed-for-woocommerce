<?php

namespace ShoppingFeed\ShoppingFeedWC\Tests\wpunit\Feed;

use ShoppingFeed\ShoppingFeedWC\Products\Product;
use ShoppingFeed\ShoppingFeedWC\Products\Products;
use ShoppingFeed\ShoppingFeedWC\ShoppingFeedHelper;
use ShoppingFeed\ShoppingFeedWC\Tests\wpunit\WC_Helper_Product;

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
			[
				'limit'        => - 1,
				'orderby'      => 'date',
				'order'        => 'DESC',
				'status'       => 'publish',
				'stock_status' => 'instock',
			],
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
			[
				'limit'        => - 1,
				'orderby'      => 'date',
				'order'        => 'DESC',
				'status'       => 'publish',
				'stock_status' => [ 'instock', 'outofstock' ],
			],
			$products_query_args
		);
	}

	/**
	 * @covers \ShoppingFeed\ShoppingFeedWC\Products\Product::get_quantity
	 */
	public function test_get_product_quantity_instock() {
		$wc_product = wc_get_product( 13 );
		$wc_product->set_stock_status( 'instock' );
		$wc_product->save();
		$p = new Product( wc_get_product( 13 ) );
		$this->assertEquals( ShoppingFeedHelper::get_default_product_quantity(), $p->get_quantity() );
	}

	/**
	 * @covers \ShoppingFeed\ShoppingFeedWC\Products\Product::get_quantity
	 */
	public function test_get_product_quantity_outofstock() {
		$wc_product = wc_get_product( 13 );
		$wc_product->set_stock_status( 'outofstock' );
		$wc_product->save();

		$p = new Product( wc_get_product( 13 ) );
		$this->assertEquals( 0, $p->get_quantity() );
	}

	/**
	 * @covers \ShoppingFeed\ShoppingFeedWC\Products\Product::get_quantity
	 */
	public function test_get_product_quantity_instock_manage_stock() {
		$wc_product = wc_get_product( 13 );
		$wc_product->set_manage_stock( true );
		$wc_product->set_stock_quantity( 17 );
		$wc_product->save();

		$p = new Product( wc_get_product( 13 ) );
		$this->assertEquals( 17, $p->get_quantity() );
	}

	/**
	 * @covers \ShoppingFeed\ShoppingFeedWC\Products\Product::get_quantity
	 */
	public function test_get_product_quantity_outofstock_manage_stock() {
		$wc_product = wc_get_product( 13 );
		$wc_product->set_manage_stock( true );
		$wc_product->set_stock_quantity( 0 );
		$wc_product->save();

		$p = new Product( wc_get_product( 13 ) );
		$this->assertEquals( 0, $p->get_quantity() );
	}

	/**
	 * @covers \ShoppingFeed\ShoppingFeedWC\Products\Product::get_attributes
	 *
	 * @author Stéphane Gillot, Clément Boirie
	 */
	public function test_attribute_on_variable_product_is_applied_to_variations() {
		// Prepare the attribute object
		$attribute = new \WC_Product_Attribute();
		$attribute->set_name( 'material' );
		$attribute->set_options( [ 'Coton', 'Linen' ] );
		$attribute->set_variation( 'true' );
		$attribute->set_visible( 'true' );

		// Prepare the variable product object
		$wc_variable_product = WC_Helper_Product::create_variation_product();
		$wc_variable_product->set_attributes( [ $attribute ] );
		$wc_variable_product->save();

		// Prepare the sf product object
		$sf_product = new Product( $wc_variable_product->get_id() );

		$this->assertEquals( [], $sf_product->get_attributes() );
	}

	/**
	 * @covers \ShoppingFeed\ShoppingFeedWC\Products\Product::get_attributes
	 *
	 * @author Stéphane Gillot, Clément Boirie
	 */
	public function test_attribute_on_variable_product_is_not_applied_to_variations() {
		// Prepare the attribute object
		$attribute = new \WC_Product_Attribute();
		$attribute->set_name( 'material' );
		$attribute->set_options( [ 'Coton', 'Linen' ] );
		$attribute->set_variation( 'false' );
		$attribute->set_visible( 'true' );

		// Prepare the variable product object
		$wc_variable_product = WC_Helper_Product::create_variation_product();
		$wc_variable_product->set_attributes( [ $attribute ] );
		$wc_variable_product->save();

		// Prepare the sf product object
		$sf_product = new Product( $wc_variable_product );

		$this->assertEquals( [ 'material' => 'Coton,Linen' ], $sf_product->get_attributes() );
	}

	/**
	 * @covers \ShoppingFeed\ShoppingFeedWC\Products\Product::get_attributes
	 *
	 * @author Stéphane Gillot, Clément Boirie
	 */
	public function test_attribute_on_simple_product_exists() {
		// Prepare the attribute object
		$attribute = new \WC_Product_Attribute();
		$attribute->set_name( 'material' );
		$attribute->set_options( [ 'Coton' ] );
		$attribute->set_visible( 'true' );

		// Prepare the variable product object
		$wc_simple_product = WC_Helper_Product::create_simple_product();
		$wc_simple_product->set_attributes( [ $attribute ] );
		$wc_simple_product->save();

		// Prepare the sf product object
		$sf_product = new Product( $wc_simple_product );

		$this->assertEquals( [ 'material' => 'Coton' ], $sf_product->get_attributes() );
	}

	/**
	 * @covers \ShoppingFeed\ShoppingFeedWC\Products\Product::get_attributes
	 *
	 * @author Stéphane Gillot, Clément Boirie
	 */
	public function test_attribute_on_simple_product_does_not_exist() {
		// Prepare the variable product object
		$wc_simple_product = WC_Helper_Product::create_simple_product();

		// Prepare the sf product object
		$sf_product = new Product( $wc_simple_product );

		$this->assertEquals( [], $sf_product->get_attributes() );
	}
}
