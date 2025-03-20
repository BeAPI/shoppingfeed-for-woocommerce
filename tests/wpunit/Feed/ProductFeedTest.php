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
	 * @covers
	 */

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

	/**
	 * @covers \ShoppingFeed\ShoppingFeedWC\Products\Product::get_length
	 * @covers \ShoppingFeed\ShoppingFeedWC\Products\Product::get_height
	 * @covers \ShoppingFeed\ShoppingFeedWC\Products\Product::get_width
	 *
	 * @author Stéphane Gillot
	 */
	public function test_get_simple_product_dimensions_when_defined(){
		$wc_product = WC_Helper_Product::create_simple_product();
		$wc_product->set_length( 5 );
		$wc_product->set_height( 10 );
		$wc_product->set_width( 15 );
		$wc_product->save();

		$p = new Product( $wc_product->get_id() );

		$this->assertEquals( 5, $p->get_length(), 'Product length should be 5.' );
		$this->assertEquals( 10, $p->get_height(), 'Product height should be 10.' );
		$this->assertEquals( 15, $p->get_width(), 'Product width should be 15.' );
	}

	/**
	 * @covers \ShoppingFeed\ShoppingFeedWC\Products\Product::get_length
	 * @covers \ShoppingFeed\ShoppingFeedWC\Products\Product::get_height
	 * @covers \ShoppingFeed\ShoppingFeedWC\Products\Product::get_width
	 *
	 * @author Stéphane Gillot
	 */
	public function test_get_simple_product_dimensions_when_not_defined(){
		$wc_product = WC_Helper_Product::create_simple_product();
		$wc_product->set_length('' );
		$wc_product->set_height( '' );
		$wc_product->set_width( '' );
		$wc_product->save();

		$p = new Product( $wc_product->get_id() );

		$this->assertEquals( '', $p->get_length(), 'Product length should be an empty string.' );
		$this->assertEquals( '', $p->get_height(), 'Product height should be an empty string.' );
		$this->assertEquals( '', $p->get_width(), 'Product width should be an empty string.' );
	}

	public function test_get_variation_dimensions_when_it_not_defined() {

		// Prepare the variable product object
		$wc_variable_product = new \WC_Product_Variable();
		$wc_variable_product->set_length( 5 );
		$wc_variable_product->set_height( 10 );
		$wc_variable_product->set_width( 15 );
		$wc_variable_product->save();

		$variation = WC_Helper_Product::create_variation_product();
		$variation->set_parent_id( $wc_variable_product->get_id() );
		$variation->save();

		// Create an SF variation
		$sf_product = new Product( $variation );
		$this->assertEquals( '', $sf_product->get_length(), 'Product length should be null.' );
		$this->assertEquals( '', $sf_product->get_height(), 'Product height should be null.' );
		$this->assertEquals( '', $sf_product->get_width(), 'Product width should be null.' );
	}

	public function test_get_variation_dimensions_when_it_overrides_parent_dimensions(){

		// Prepare the variable product object
		$wc_variable_product = new \WC_Product_Variable();
		$wc_variable_product->set_length( 5 );
		$wc_variable_product->set_height( 10 );
		$wc_variable_product->set_width( 15 );
		$wc_variable_product->save();

		$variation = WC_Helper_Product::create_variation_product();
		$variation->set_parent_id( $wc_variable_product->get_id() );
		$variation->set_length(20);
		$variation->set_height(30);
		$variation->set_width(40);
		$variation->save();


		// Create an SF variation
		$sf_product = new Product( $variation );
		$this->assertEquals( 20, $sf_product->get_length(), 'Product length should be 20.' );
		$this->assertEquals( 30, $sf_product->get_height(), 'Product height should be 30.' );
		$this->assertEquals( 40, $sf_product->get_width(), 'Product width should be 40.' );
	}

	public function test_variation_set_custom_main_image() {
		add_filter(
			'shopping_feed_variation_main_image',
			function( $image, $variation, $product ) {
				return 'https://example.com/image.jpg';
			},
			10,
			3
		);

		$image_id = $this->factory()->attachment->create_object(
			[
				'file' => codecept_data_dir( 'images/image1.png' ),
				'post_mime_type' => 'image/png',
				'post_title' => 'Test Image',
				'post_content' => '',
				'post_status' => 'inherit',
			]
		);
		
		$variable_product = new \WC_Product_Variable();
		$variable_product_id = $variable_product->save();

		WC_Helper_Product::create_product_variation_object( $variable_product_id, 'variation-1', 10, [], true );

		$sf_product = new Product( $variable_product_id );
		$this->assertCount( 1, $sf_product->get_variations(), 'Variable product should have 1 variation.' );
		$this->assertArrayHasKey( 'image_main', $sf_product->get_variations()[0], 'Variation should have an image_main key.' );
		$this->assertEquals( 'https://example.com/image.jpg', $sf_product->get_variations()[0]['image_main'], 'Product main image should be equal to the custom value from the filter.' );
	}

	public function test_variation_use_thumbnail_as_main_image() {
		$image_id = $this->factory()->attachment->create_object(
			[
				'file' => codecept_data_dir( 'images/image1.png' ),
				'post_mime_type' => 'image/png',
				'post_title' => 'Test Image',
				'post_content' => '',
				'post_status' => 'inherit',
			]
		);
		$image_url = wp_get_attachment_image_url( $image_id, 'full' );
		
		$variable_product = new \WC_Product_Variable();
		$variable_product_id = $variable_product->save();

		$variation_product = WC_Helper_Product::create_product_variation_object( $variable_product_id, 'variation-1', 10, [], true );
		$variation_product->set_image_id( $image_id );
		$variation_product->save();

		$sf_product = new Product( $variable_product_id );
		$this->assertCount( 1, $sf_product->get_variations(), 'Variable product should have 1 variation.' );
		$this->assertArrayHasKey( 'image_main', $sf_product->get_variations()[0], 'Variation should have an image_main key.' );
		$this->assertEquals( $image_url, $sf_product->get_variations()[0]['image_main'], 'Product main image should be empty.' );
	}

	public function test_variation_empty_main_image_if_no_image_set() {
		$image_id = $this->factory()->attachment->create_object(
			[
				'file' => codecept_data_dir( 'images/image1.png' ),
				'post_mime_type' => 'image/png',
				'post_title' => 'Test Image',
				'post_content' => '',
				'post_status' => 'inherit',
			]
		);
		
		$variable_product = new \WC_Product_Variable();
		$variable_product->set_image_id( $image_id );
		$variable_product_id = $variable_product->save();

		WC_Helper_Product::create_product_variation_object( $variable_product_id, 'variation-1', 10, [], true );

		$sf_product = new Product( $variable_product_id );
		$this->assertCount( 1, $sf_product->get_variations(), 'Variable product should have 1 variation.' );
		$this->assertArrayHasKey( 'image_main', $sf_product->get_variations()[0], 'Variation should have an image_main key.' );
		$this->assertEquals( '', $sf_product->get_variations()[0]['image_main'], 'Product main image should be empty.' );
	}
}
