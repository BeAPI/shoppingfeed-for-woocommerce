<?php

namespace ShoppingFeed\ShoppingFeedWC\Addons\Plugins\ASTPlugin;

// Exit on direct access
use WC_Advanced_Shipment_Tracking_Actions;

defined( 'ABSPATH' ) || exit;

/**
 * Class ASTPlugin to manage the plugin advanced shipment tracking
 * @link https://wordpress.org/plugins/woo-advanced-shipment-tracking/
 * @package ShoppingFeed\ShoppingFeedWC\Addons\ASTPlugin
 */
class ASTPlugin {

	public function __construct() {
		if ( ! class_exists( '\WC_Advanced_Shipment_Tracking_Actions' ) ) {
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
		$tracking_info = $this->get_ast_tracking_data( $wc_order );

		return empty( $tracking_info['tracking_number'] ) ? $tracking_number : $tracking_info['tracking_number'];
	}

	public function get_ast_tracking_data( $wc_order ) {
		if (
		! $wc_order instanceof \WC_Order
		) {
			return array();
		}

		$ast           = WC_Advanced_Shipment_Tracking_Actions::get_instance();
		$tracking_info = $ast->get_tracking_items( $wc_order->get_id(), true );
		$tracking_info = end( $tracking_info );

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

		return empty( $tracking_info['formatted_tracking_link'] ) ? $tracking_link : $tracking_info['formatted_tracking_link'];
	}
}
