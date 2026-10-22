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
}
