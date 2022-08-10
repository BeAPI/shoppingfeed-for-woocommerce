<?php

namespace ShoppingFeed\ShoppingFeedWC\Addons\Plugins\ASTPlugin;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

/**
 * Class ASTPlugin to manage the plugin advanced shipment tracking
 * @link https://wordpress.org/plugins/woo-advanced-shipment-tracking/
 * @package ShoppingFeed\ShoppingFeedWC\Addons\Plugins\ASTPlugin
 */
class ASTPlugin {

	public function __construct() {
		if ( ! defined( 'SHIPMENT_TRACKING_PATH' ) ) {
			return;
		}

		add_filter( 'shopping_feed_tracking_number', array( $this, 'get_tracking_number' ), 11, 2 );
		add_filter( 'shopping_feed_tracking_link', array( $this, 'get_tracking_link' ), 11, 2 );
	}

	/**
	 * Get Tracking Number
	 *
	 * @param string $tracking_number
	 * @param \WC_Order|\WP_Error $wc_order
	 */
	public function get_tracking_number( $tracking_number, $wc_order ) {
		$tracking_infos = $this->get_ast_tracking_data( $wc_order );

		// Return default tracking number if AST data is empty
		if ( ! is_array( $tracking_infos ) || empty( $tracking_infos ) ) {
			return $tracking_number;
		}

		// Return first key of AST data if only one set of tracking data
		if ( count( $tracking_infos ) === 1 ) {
			$tracking_number_single = $tracking_infos[0]['tracking_number'];

			return empty( $tracking_number_single ) ? $tracking_number : $tracking_number_single;
		}

		$tracking_numbers = [];

		// Add every tracking number from AST data to array
		foreach ( $tracking_infos as $info ) {
			$tracking_numbers[] = $info['tracking_number'];
		}

		// Implode to comma separated string, depending on PHP version
		if ( version_compare( PHP_VERSION, '7.4.0', '>=' ) ) {
			$tracking_numbers = implode(',',  $tracking_numbers );
		} else {
			$tracking_numbers = implode( $tracking_numbers, ',' );
		}

		return $tracking_numbers;
	}

	/**
	 * Get AST tracking data for the order.
	 *
	 * @param \WC_Order $wc_order
	 *
	 * @return array
	 */
	public function get_ast_tracking_data( $wc_order ) {
		if ( ! $wc_order instanceof \WC_Order ) {
			return array();
		}

		$tracking_info = array();
		if ( function_exists( 'ast_get_tracking_items' ) ) { // AST >= 3.0 & AST Pro
			$tracking_info = ast_get_tracking_items( $wc_order->get_id() );
		} elseif ( class_exists( '\WC_Advanced_Shipment_Tracking_Actions' ) ) { // AST < 3.0
			$ast           = \WC_Advanced_Shipment_Tracking_Actions::get_instance();
			$tracking_info = $ast->get_tracking_items( $wc_order->get_id(), true );
		}

		if ( ! is_array( $tracking_info ) ) {
			return array();
		}

		return $tracking_info;
	}

	/**
	 * Get Tracking Link
	 *
	 * @param string $tracking_link
	 * @param \WC_Order|\WP_Error $wc_order
	 */
	public function get_tracking_link( $tracking_link, $wc_order ) {
		$tracking_info = $this->get_ast_tracking_data( $wc_order );

		// Can me a multidimension array, take only first tracking link of data set
		$tracking_info = reset( $tracking_info );

		return empty( $tracking_info['formatted_tracking_link'] ) ? $tracking_link : $tracking_info['formatted_tracking_link'];
	}
}
