<?php

namespace ShoppingFeed\ShoppingFeedWC\Tests\wpunit\Admin;

use ShoppingFeed\ShoppingFeedWC\Admin\OrderListColumns;
use ShoppingFeed\ShoppingFeedWC\Query\Query;

class OrderListColumnsTest extends \Codeception\TestCase\WPTestCase {

	public function test_adds_marketplace_and_reference_columns_after_order_status() {
		new OrderListColumns();
		do_action( 'admin_init' );

		$columns = apply_filters(
			'manage_edit-shop_order_columns',
			[
				'cb'           => '<input>',
				'order_status' => 'Status',
				'date'         => 'Date',
			]
		);

		$keys = array_keys( $columns );
		$status_index = array_search( 'order_status', $keys, true );
		$this->assertNotFalse( $status_index );
		$this->assertSame( Query::WC_META_SF_CHANNEL_NAME, $keys[ $status_index + 1 ] );
		$this->assertSame( Query::WC_META_SF_REFERENCE, $keys[ $status_index + 2 ] );
	}
}
