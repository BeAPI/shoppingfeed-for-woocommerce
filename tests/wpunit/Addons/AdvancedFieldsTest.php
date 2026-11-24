<?php

namespace ShoppingFeed\ShoppingFeedWC\Tests\wpunit\Addons;

use ShoppingFeed\ShoppingFeedWC\Addons\Advanced\Fields;
use ShoppingFeed\ShoppingFeedWC\Tests\wpunit\WC_Helper_Product;

class AdvancedFieldsTest extends \Codeception\TestCase\WPTestCase {

	/** @var Fields */
	private $fields;

	public function setUp(): void {
		parent::setUp();

		if ( ! defined( 'EAN_FIELD_SLUG' ) ) {
			define( 'EAN_FIELD_SLUG', 'sf_advanced_ean_field' );
		}

		$this->fields = new Fields();
	}

	public function tearDown(): void {
		unset( $_POST[ EAN_FIELD_SLUG ] );
		parent::tearDown();
	}

	public function test_save_ean_from_inventory_post() {
		$product = WC_Helper_Product::create_simple_product();

		$_POST[ EAN_FIELD_SLUG ] = '1234567890123';

		$this->fields->save_fields_product_option( $product );

		$this->assertSame( '1234567890123', $product->get_meta( EAN_FIELD_SLUG ) );

		WC_Helper_Product::delete_product( $product->get_id() );
	}

	/**
	 * Variation EAN must render even when WooCommerce passes empty variation attributes.
	 */
	public function test_variation_ean_field_renders_with_empty_variation_data() {
		$this->load_woocommerce_meta_box_functions();

		$product   = WC_Helper_Product::create_variation_product();
		$children  = $product->get_children();
		$variation = get_post( $children[0] );

		ob_start();
		$this->fields->fields_product_option_variation( 0, array(), $variation );
		$output = ob_get_clean();

		$this->assertStringContainsString( EAN_FIELD_SLUG . '_0', $output );
		$this->assertStringContainsString( 'name="' . EAN_FIELD_SLUG . '_0"', $output );

		WC_Helper_Product::delete_product( $product->get_id() );
	}

	/**
	 * Admin-only WooCommerce helpers are not loaded in wpunit by default.
	 */
	private function load_woocommerce_meta_box_functions() {
		if ( function_exists( 'woocommerce_wp_text_input' ) ) {
			return;
		}

		include_once WP_PLUGIN_DIR . '/woocommerce/includes/admin/wc-meta-box-functions.php';
	}

	public function test_save_variation_ean_from_post() {
		$product      = WC_Helper_Product::create_variation_product();
		$children     = $product->get_children();
		$variation_id = $children[0];

		$_POST[ EAN_FIELD_SLUG . '_0' ] = '9876543210987';

		$this->fields->save_fields_product_option_variation( $variation_id, 0 );

		$variation = wc_get_product( $variation_id );
		$this->assertSame( '9876543210987', $variation->get_meta( EAN_FIELD_SLUG ) );

		unset( $_POST[ EAN_FIELD_SLUG . '_0' ] );
		WC_Helper_Product::delete_product( $product->get_id() );
	}
}
