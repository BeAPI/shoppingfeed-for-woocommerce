<?php

namespace ShoppingFeed\ShoppingFeedWC\Tests\wpunit\Product;

class ProductTest extends \Codeception\TestCase\WPTestCase {
	/**
	 * @var \WpunitTester
	 */
	protected $tester;

	public function setUp(): void {
		parent::setUp();

		as_unschedule_all_actions( 'sf_update_product_data' );
		as_unschedule_all_actions( 'sf_update_product_stock' );
		remove_all_filters( 'pre_option_sf_feed_options' );
	}

	/**
	 * @covers \ShoppingFeed\ShoppingFeedWC\Admin\WoocommerceActions::schedule_product_update
	 */
	public function test_updating_product_schedule_an_event() {
		$wc_product = wc_get_product( 13 );
		$wc_product->set_description( 'a new description' );
		$wc_product->save();

		$this->assertCount(
			1,
			as_get_scheduled_actions(
				[
					'hook' => 'sf_update_product_data',
					'args' => [ 'product' => 13 ]
				]
			),
			'Updating a product schedule an event.'
		);

		$wc_product = wc_get_product( 13 );
		$wc_product->set_description( 'another description' );
		$wc_product->save();

		$this->assertCount(
			1,
			as_get_scheduled_actions(
				[
					'hook' => 'sf_update_product_data',
					'args' => [ 'product' => 13 ]
				]
			),
			"Updating a product multiple time doesn't schedule multiple events."
		);
	}

	/**
	 * @covers \ShoppingFeed\ShoppingFeedWC\Admin\WoocommerceActions::schedule_product_update
	 */
	public function test_updating_product_schedule_an_event_if_synchro_stock_is_disabled() {
		add_filter(
			'pre_option_sf_feed_options',
			function ( $value ) {
				return [
					'synchro_stock' => 'no',
					'synchro_price' => 'yes',
				];
			}
		);

		$wc_product = wc_get_product( 13 );
		$wc_product->set_description( 'a new description' );
		$wc_product->save();

		$this->assertCount(
			1,
			as_get_scheduled_actions(
				[
					'hook' => 'sf_update_product_data',
					'args' => [ 'product' => 13 ]
				]
			)
		);
	}

	/**
	 * @covers \ShoppingFeed\ShoppingFeedWC\Admin\WoocommerceActions::schedule_product_update
	 */
	public function test_updating_product_schedule_an_event_if_synchro_price_is_disabled() {
		add_filter(
			'pre_option_sf_feed_options',
			function ( $value ) {
				return [
					'synchro_stock' => 'yes',
					'synchro_price' => 'no',
				];
			}
		);

		$wc_product = wc_get_product( 13 );
		$wc_product->set_description( 'a new description' );
		$wc_product->save();

		$this->assertCount(
			1,
			as_get_scheduled_actions(
				[
					'hook' => 'sf_update_product_data',
					'args' => [ 'product' => 13 ]
				]
			)
		);
	}

	/**
	 * @covers \ShoppingFeed\ShoppingFeedWC\Admin\WoocommerceActions::schedule_product_update
	 */
	public function test_updating_product_doesnt_schedule_an_event_if_synchro_is_disabled() {
		add_filter(
			'pre_option_sf_feed_options',
			function ( $value ) {
				return [
					'synchro_stock' => 'no',
					'synchro_price' => 'no',
				];
			}
		);

		$wc_product = wc_get_product( 13 );
		$wc_product->set_description( 'a new description' );
		$wc_product->save();

		$this->assertCount(
			0,
			as_get_scheduled_actions(
				[
					'hook' => 'sf_update_product_data',
					'args' => [ 'product' => 13 ]
				]
			)
		);
	}

	/**
	 * @covers \ShoppingFeed\ShoppingFeedWC\Admin\WoocommerceActions::schedule_product_stock_update
	 */
	public function test_modifying_product_stock_schedule_an_event() {
		$wc_product = wc_get_product( 21 );
		wc_update_product_stock( $wc_product, 1, 'decrease' );

		$this->assertCount(
			1,
			as_get_scheduled_actions(
				[
					'hook' => 'sf_update_product_stock',
					'args' => [ 'product' => 21 ]
				]
			),
			'Modifying a product stock schedule an event.'
		);

		$wc_product = wc_get_product( 21 );
		wc_update_product_stock( $wc_product, 1, 'increase' );

		$this->assertCount(
			1,
			as_get_scheduled_actions(
				[
					'hook' => 'sf_update_product_stock',
					'args' => [ 'product' => 21 ]
				]
			),
			"Modifying a product stock multiple time doesn't schedule multiple events."
		);
	}

	/**
	 * @covers \ShoppingFeed\ShoppingFeedWC\Admin\WoocommerceActions::schedule_product_stock_update
	 */
	public function test_modifying_product_stock_doesnt_schedule_an_event_if_synchro_stock_is_disabled() {
		add_filter(
			'pre_option_sf_feed_options',
			function ( $value ) {
				return [
					'synchro_stock' => 'no',
					'synchro_price' => 'yes',
				];
			}
		);

		$wc_product = wc_get_product( 21 );
		wc_update_product_stock( $wc_product, 1, 'decrease' );

		$this->assertCount(
			0,
			as_get_scheduled_actions(
				[
					'hook' => 'sf_update_product_stock',
					'args' => [ 'product' => 21 ]
				]
			)
		);
	}
}
