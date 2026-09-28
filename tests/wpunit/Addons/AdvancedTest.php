<?php

namespace ShoppingFeed\ShoppingFeedWC\Tests\wpunit\Addons;

use ShoppingFeed\ShoppingFeedWC\Addons\Advanced\Filters;
use ShoppingFeed\ShoppingFeedWC\Addons\Advanced\Taxonomies;
use ShoppingFeed\ShoppingFeedWC\Products\Product;

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
}
