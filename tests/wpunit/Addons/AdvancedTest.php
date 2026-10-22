<?php

namespace ShoppingFeed\ShoppingFeedWC\Tests\wpunit\Addons;

use ShoppingFeed\ShoppingFeedWC\Addons\Advanced\Filters;
use ShoppingFeed\ShoppingFeedWC\Addons\Advanced\Taxonomies;
use ShoppingFeed\ShoppingFeedWC\Products\Product;
use ShoppingFeed\ShoppingFeedWC\Tests\wpunit\WC_Helper_Product;

class AdvancedTest extends \Codeception\TestCase\WPTestCase {

	public function setUp(): void {
		parent::setUp();
		new Filters();
	}

	public function test_default_ean_meta_key_filter() {
		$this->assertSame(
			'sf_advanced_ean_field',
			apply_filters( 'shopping_feed_custom_ean', '', false )
		);
	}

	public function test_default_brand_taxonomy_filter() {
		$this->assertSame(
			Taxonomies::BRAND_TAXONOMY_SLUG,
			apply_filters( 'shopping_feed_custom_brand_taxonomy', '' )
		);
	}

	public function test_get_ean_uses_builtin_meta_key() {
		$wc_product = wc_get_product( 13 );
		$wc_product->update_meta_data( 'sf_advanced_ean_field', '1234567890123' );
		$wc_product->save();

		$sf_product = new Product( $wc_product );
		$this->assertSame( '1234567890123', $sf_product->get_ean() );
	}

	public function test_product_brand_taxonomy_is_registered() {
		$this->assertTrue( taxonomy_exists( Taxonomies::BRAND_TAXONOMY_SLUG ) );
	}

	public function test_get_brand_name_uses_first_product_brand_term() {
		$taxonomies = new Taxonomies();
		$taxonomies->add_brand();

		$term = wp_insert_term( 'Feed Brand Alpha', Taxonomies::BRAND_TAXONOMY_SLUG );
		$this->assertIsArray( $term );

		$product = WC_Helper_Product::create_simple_product();
		wp_set_object_terms( $product->get_id(), array( (int) $term['term_id'] ), Taxonomies::BRAND_TAXONOMY_SLUG );

		$sf_product = new Product( $product );
		$this->assertSame( 'Feed Brand Alpha', $sf_product->get_brand_name() );

		WC_Helper_Product::delete_product( $product->get_id() );
		wp_delete_term( (int) $term['term_id'], Taxonomies::BRAND_TAXONOMY_SLUG );
	}
}
