<?php
// if uninstall.php is not called by WordPress, die
use ShoppingFeed\ShoppingFeedWC\Admin\Options;
use ShoppingFeed\ShoppingFeedWC\Feed\Generator;

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

//delete plugins options
delete_option( Options::SF_ACCOUNT_OPTIONS );
delete_option( Options::SF_FEED_OPTIONS );
delete_option( Options::SF_SHIPPING_OPTIONS );
delete_option( Options::SF_ORDERS_OPTIONS );
delete_option( Options::SF_CARRIERS );
delete_option( Generator::SF_FEED_LAST_GENERATION_DATE );
