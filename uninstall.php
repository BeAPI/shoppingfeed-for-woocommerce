<?php

namespace ShoppingFeed\ShoppingFeedWC;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

// Load composer autoload
if ( file_exists( plugin_dir_path( __FILE__ ) . '/vendor/autoload.php' ) ) {
	require_once plugin_dir_path( __FILE__ ) . '/vendor/autoload.php';
}

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

ShoppingFeed::uninstall();
