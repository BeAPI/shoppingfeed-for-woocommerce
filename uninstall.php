<?php

namespace ShoppingFeed\ShoppingFeedWC;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

// Load composer autoload
if ( file_exists( plugin_dir_path( __FILE__ ) . '/vendor/autoload.php' ) ) {
	require_once plugin_dir_path( __FILE__ ) . '/vendor/autoload.php';
}

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
