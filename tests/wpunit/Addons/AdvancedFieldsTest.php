<?php

namespace ShoppingFeed\ShoppingFeedWC\Tests\wpunit\Addons;

use ShoppingFeed\ShoppingFeedWC\Addons\Advanced\AdvancedHelper;
use ShoppingFeed\ShoppingFeedWC\Addons\Advanced\Fields;
use ShoppingFeed\ShoppingFeedWC\ShoppingFeedHelper;
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

	/**
	 * Saving a variation EAN must remove leftover indexed legacy keys.
	 */
	public function test_save_variation_ean_deletes_legacy_indexed_keys() {
		$product      = WC_Helper_Product::create_variation_product();
		$children     = $product->get_children();
		$variation_id = $children[0];
		$variation    = wc_get_product( $variation_id );

		$legacy_key = EAN_FIELD_SLUG . '_0';
		$variation->update_meta_data( $legacy_key, '1111111111111' );
		$variation->save_meta_data();

		$_POST[ EAN_FIELD_SLUG . '_0' ] = '2222222222222';
		$this->fields->save_fields_product_option_variation( $variation_id, 0 );

		$variation = wc_get_product( $variation_id );
		$this->assertSame( '2222222222222', $variation->get_meta( EAN_FIELD_SLUG ) );
		$this->assertFalse( $variation->meta_exists( $legacy_key ) );

		unset( $_POST[ EAN_FIELD_SLUG . '_0' ] );
		WC_Helper_Product::delete_product( $product->get_id() );
	}

	/**
	 * Clearing a variation EAN must not resurrect a leftover indexed legacy value in the editor.
	 */
	public function test_cleared_variation_ean_does_not_render_legacy_value() {
		$this->load_woocommerce_meta_box_functions();

		$product      = WC_Helper_Product::create_variation_product();
		$children     = $product->get_children();
		$variation_id = $children[0];
		$variation    = wc_get_product( $variation_id );

		$legacy_key = EAN_FIELD_SLUG . '_0';
		$variation->update_meta_data( EAN_FIELD_SLUG, '' );
		$variation->update_meta_data( $legacy_key, '3333333333333' );
		$variation->save_meta_data();

		$variation_post = get_post( $variation_id );
		ob_start();
		$this->fields->fields_product_option_variation( 0, array(), $variation_post );
		$output = ob_get_clean();

		$this->assertStringNotContainsString( '3333333333333', $output );
		$this->assertMatchesRegularExpression(
			'/name="' . preg_quote( EAN_FIELD_SLUG . '_0', '/' ) . '"[^>]*value=""/',
			$output
		);

		WC_Helper_Product::delete_product( $product->get_id() );
	}

	/**
	 * find_old_variation_ean_meta_key must ignore the current empty key and return only indexed legacy keys.
	 */
	public function test_find_old_variation_ean_skips_current_key() {
		$product      = WC_Helper_Product::create_variation_product();
		$children     = $product->get_children();
		$variation_id = $children[0];
		$variation    = wc_get_product( $variation_id );

		$legacy_key = EAN_FIELD_SLUG . '_0';
		$variation->update_meta_data( EAN_FIELD_SLUG, '' );
		$variation->update_meta_data( $legacy_key, '4444444444444' );
		$variation->save_meta_data();

		$this->assertSame( $legacy_key, AdvancedHelper::find_old_variation_ean_meta_key( $variation ) );

		WC_Helper_Product::delete_product( $product->get_id() );
	}

	/**
	 * Once the current EAN meta exists (even empty), the feed helper must not fall back to legacy keys.
	 */
	public function test_wc_product_ean_does_not_fallback_when_current_meta_exists_empty() {
		$product      = WC_Helper_Product::create_variation_product();
		$children     = $product->get_children();
		$variation_id = $children[0];
		$variation    = wc_get_product( $variation_id );

		$legacy_key = EAN_FIELD_SLUG . '_0';
		$variation->update_meta_data( EAN_FIELD_SLUG, '' );
		$variation->update_meta_data( $legacy_key, '5555555555555' );
		$variation->save_meta_data();

		$this->assertSame( EAN_FIELD_SLUG, ShoppingFeedHelper::wc_product_ean( $variation ) );

		WC_Helper_Product::delete_product( $product->get_id() );
	}

	/**
	 * Unmigrated variations that only have an indexed legacy key still resolve via fallback.
	 */
	public function test_wc_product_ean_falls_back_to_legacy_when_current_meta_missing() {
		$product      = WC_Helper_Product::create_variation_product();
		$children     = $product->get_children();
		$variation_id = $children[0];
		$variation    = wc_get_product( $variation_id );

		$legacy_key = EAN_FIELD_SLUG . '_0';
		$variation->delete_meta_data( EAN_FIELD_SLUG );
		$variation->update_meta_data( $legacy_key, '6666666666666' );
		$variation->save_meta_data();

		$this->assertSame( $legacy_key, ShoppingFeedHelper::wc_product_ean( $variation ) );

		WC_Helper_Product::delete_product( $product->get_id() );
	}
}
