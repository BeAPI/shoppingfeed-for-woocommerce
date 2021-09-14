<?php

namespace ShoppingFeed\ShoppingFeedWC\Admin;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

use ShoppingFeed\ShoppingFeedWC\Orders\Order;

/**
 * Class Filters
 * @package ShoppingFeed\ShoppingFeedWC\Admin
 */
class WoocommerceFilters {

	/**
	 * WoocommerceFilters constructor.
	 */
	public function __construct() {
		$this->sf_disable_wc_emails_filters();
		$this->sf_disable_wc_stock_change();
		$this->sf_extra_attributes();
	}

	public function sf_extra_attributes() {
		add_filter(
			'shopping_feed_extra_attributes',
			array( $this, 'shopping_feed_extra_attributes_values' ),
			10,
			2
		);
		add_filter(
			'shopping_feed_extra_variation_attributes',
			array( $this, 'shopping_feed_extra_attributes_values' ),
			10,
			2
		);
	}

	/**
	 * @param array $attributes
	 * @param \WC_Product|\WC_Product_Variation $wc_product
	 *
	 * @return mixed
	 */
	public function shopping_feed_extra_attributes_values( $attributes, $wc_product ) {
		$weight = $wc_product->get_weight();
		if ( empty( $weight ) ) {
			return $attributes;
		}

		$attributes['weight'] = $weight;

		return $attributes;
	}


	/**
	 * Disable Stock change for SF orders already shipped
	 */
	public function sf_disable_wc_stock_change() {
		add_filter(
			'woocommerce_can_reduce_order_stock',
			array( $this, 'sf_can_reduce_order_stock' ),
			10,
			2
		);
	}

	public function sf_can_reduce_order_stock( $true, $wc_order ) {
		return Order::can_update_stock( $wc_order );
	}

	/**
	 * Disable Sending Emails for SF Orders
	 */
	private function sf_disable_wc_emails_filters() {
		add_filter( 'woocommerce_email_enabled_new_order', array( $this, 'disable_wc_emails' ), 10, 2 );
		add_filter( 'woocommerce_email_enabled_cancelled_order', array( $this, 'disable_wc_emails' ), 10, 2 );
		add_filter( 'woocommerce_email_enabled_customer_completed_order', array( $this, 'disable_wc_emails' ), 10, 2 );
		add_filter( 'woocommerce_email_enabled_customer_invoice', array( $this, 'disable_wc_emails' ), 10, 2 );
		add_filter( 'woocommerce_email_enabled_customer_note', array( $this, 'disable_wc_emails' ), 10, 2 );
		add_filter( 'woocommerce_email_enabled_customer_on_hold_order', array( $this, 'disable_wc_emails' ), 10, 2 );
		add_filter( 'woocommerce_email_enabled_customer_processing_order', array( $this, 'disable_wc_emails' ), 10, 2 );
		add_filter( 'woocommerce_email_enabled_customer_refunded_order', array( $this, 'disable_wc_emails' ), 10, 2 );
		add_filter( 'woocommerce_email_enabled_failed_order', array( $this, 'disable_wc_emails' ), 10, 2 );
	}

	/**
	 * @param $is_enabled
	 * @param $wc_order \WC_Order
	 *
	 * @return bool
	 */
	public function disable_wc_emails( $is_enabled, $wc_order ) {
		if ( null === $wc_order || ! Order::is_sf_order( $wc_order ) ) {
			return $is_enabled;
		}

		return false;
	}
}
