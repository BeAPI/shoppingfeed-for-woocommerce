<?php
/**
 * Plugin Name:     ShoppingFeed
 * Plugin URI:      https://wordpress.org/plugins/shoppingfeed/
 * Description:     WordPress connection Controller Plugin for ShoppingFeed - Sell on Amazon, Ebay, Google, and 1000's of international marketplaces
 * Author:          Shopping-Feed
 * Author URI:      https://www.shopping-feed.com/
 * Text Domain:     shopping-feed
 * Domain Path:     /languages
 * Version:         6.0.27
 * Requires at least WP: 5.7
 * Requires at least WooCommerce: 5.1.0
 * Requires PHP:      5.6
 * License:         GPLv3 or later
 * License URI:     https://www.gnu.org/licenses/gpl-3.0.html
 */

namespace ShoppingFeed\ShoppingFeedWC;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

// Load composer autoload
if ( file_exists( plugin_dir_path( __FILE__ ) . '/vendor/autoload.php' ) ) {
	require_once plugin_dir_path( __FILE__ ) . '/vendor/autoload.php';
}

define( 'SF_VERSION', '6.0.27' );
define( 'SF_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SF_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'SF_FEED_DIR', wp_upload_dir()['basedir'] . '/shopping-feed' );
define( 'SF_FEED_PARTS_DIR', SF_FEED_DIR . '/parts' );

// Plugin activate/deactivate hooks
register_activation_hook( __FILE__, array( '\\ShoppingFeed\ShoppingFeedWC\ShoppingFeed', 'activate' ) );
register_deactivation_hook( __FILE__, array( '\\ShoppingFeed\ShoppingFeedWC\ShoppingFeed', 'deactivate' ) );

/**
 * Plugin bootstrap function.shopping-feed/src/ShoppingFeed.php
 */
function init() {
	load_plugin_textdomain( 'shopping-feed', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );

	ShoppingFeed::get_instance();

	if ( ! defined( 'WP_CLI' ) ) {
		return;
	}

	//Add CLI command for Feed Generation
	\WP_CLI::add_command( 'shopping-feed feed-generation', '\\ShoppingFeed\ShoppingFeedWC\Cli\FeedGeneration' );
}

\add_action( 'plugins_loaded', __NAMESPACE__ . '\\init' );
