<?php

namespace ShoppingFeed\ShoppingFeedWC\Admin;

use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;
use ShoppingFeed\ShoppingFeedWC\Query\Query;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

/**
 * Marketplace and Reference columns on the orders list.
 */
class OrderListColumns {

	/** @var string */
	private $channel_name_meta;

	/** @var string */
	private $sf_reference_meta;

	public function __construct() {
		add_action( 'admin_init', array( $this, 'register_columns' ) );

		$this->channel_name_meta = Query::WC_META_SF_CHANNEL_NAME;
		$this->sf_reference_meta = Query::WC_META_SF_REFERENCE;
	}

	public function register_columns() {
		if ( class_exists( CustomOrdersTableController::class ) && wc_get_container()->get( CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled() ) {
			$screen = wc_get_page_screen_id( 'shop-order' );
			add_filter( "manage_{$screen}_columns", array( $this, 'custom_shop_order_column' ) );
			add_action( "manage_{$screen}_custom_column", array( $this, 'custom_orders_list_column_content' ), 10, 2 );
		} else {
			add_filter( 'manage_edit-shop_order_columns', array( $this, 'custom_shop_order_column' ) );
			add_action( 'manage_shop_order_posts_custom_column', array( $this, 'custom_orders_list_column_content' ), 10, 2 );
		}
	}

	/**
	 * @param array $columns Columns.
	 *
	 * @return array
	 */
	public function custom_shop_order_column( $columns ) {
		$reordered_columns = array();

		foreach ( $columns as $key => $column ) {
			$reordered_columns[ $key ] = $column;
			if ( 'order_status' !== $key ) {
				continue;
			}
			$reordered_columns[ $this->channel_name_meta ] = __( 'Market Place', 'shopping-feed' );
			$reordered_columns[ $this->sf_reference_meta ] = __( 'Reference', 'shopping-feed' );
		}

		return $reordered_columns;
	}

	/**
	 * @param string       $column Column key.
	 * @param int|\WC_Order $post_id_or_order_object Order or post ID.
	 */
	public function custom_orders_list_column_content( $column, $post_id_or_order_object ) {
		$order = false;
		if ( $post_id_or_order_object instanceof \WC_Order ) {
			$order = $post_id_or_order_object;
		} elseif ( is_int( $post_id_or_order_object ) && function_exists( 'wc_get_order' ) ) {
			$order = wc_get_order( $post_id_or_order_object );
		}
		if ( false === $order ) {
			return;
		}

		if ( $this->channel_name_meta === $column ) {
			$sf_name = $order->get_meta( $this->channel_name_meta, true );
			echo ! empty( $sf_name ) ? esc_html( $sf_name ) : '<small>(<em>' . esc_html__( 'None', 'shopping-feed' ) . '</em>)</small>';
		}
		if ( $this->sf_reference_meta === $column ) {
			$sf_reference = $order->get_meta( $this->sf_reference_meta, true );
			echo ! empty( $sf_reference ) ? esc_html( $sf_reference ) : '<small>(<em>' . esc_html__( 'None', 'shopping-feed' ) . '</em>)</small>';
		}
	}
}
