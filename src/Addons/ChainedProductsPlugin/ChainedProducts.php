<?php

namespace ShoppingFeed\ShoppingFeedWC\Addons\ChainedProductsPlugin;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

/**
 * Class ChainedProducts to manage the plugin chained products
 * @link https://woocommerce.com/products/chained-products/
 * @package ShoppingFeed\ShoppingFeedWC\Addons\ChainedProductsPlugin
 */
class ChainedProducts {

	public function __construct() {
		if ( ! class_exists( '\WC_Admin_Chained_Products' ) ) {
			return;
		}
		add_action( 'sf_after_order_add_item', array( $this, 'may_add_chained_products' ), 10, 2 );
	}

	/**
	 * Check if we have Chained Products and ADD them to Order
	 *
	 * @param \WC_Order_Item_Product $item_product
	 * @param \WC_Order|\WP_Error $wc_order
	 */
	public function may_add_chained_products( $item_product, $wc_order ) {
		global $wc_chained_products;

		if (
			! $wc_chained_products instanceof \WC_Admin_Chained_Products ||
			! $wc_order instanceof \WC_Order
		) {
			return;
		}

		$wc_product = $item_product->get_product();

		$chained_product_detail = $wc_chained_products->get_all_chained_product_details( $wc_product->get_id() );
		$chained_product_ids    = is_array( $chained_product_detail ) ? array_keys( $chained_product_detail ) : array();

		if ( empty( $chained_product_ids ) ) {
			return;
		}

		/**
		 * Map chained products and add them as order product item
		 */
		foreach ( $chained_product_ids as $chained_product_id ) {
			$wc_product_chained = wc_get_product( $chained_product_id );
			if ( ! $wc_product_chained instanceof \WC_Product ) {
				continue;
			}

			$priced_individually = ( ! empty( $chained_product_detail[ $chained_product_id ]['priced_individually'] ) ) ? $chained_product_detail[ $chained_product_id ]['priced_individually'] : 'no';
			$quantity            = ( ! empty( $chained_product_detail[ $chained_product_id ]['unit'] ) ) ? $chained_product_detail[ $chained_product_id ]['unit'] : 1;

			$args = array(
				'name'         => $wc_product_chained->get_name(),
				'tax_class'    => $wc_product_chained->get_tax_class(),
				'product_id'   => $wc_product_chained->is_type( 'variation' ) ? $wc_product_chained->get_parent_id() : $wc_product_chained->get_id(),
				'variation_id' => $wc_product_chained->is_type( 'variation' ) ? $wc_product_chained->get_id() : 0,
				'variation'    => $wc_product_chained->is_type( 'variation' ) ? $wc_product_chained->get_attributes() : array(),
				'subtotal'     => 0,
				'total'        => 0,
				'quantity'     => $quantity,
			);

			$item = new \WC_Order_Item_Product();
			$item->set_props( $args );
			$item->set_order_id( $wc_order->get_id() );
			$item->add_meta_data( '_chained_product_of', $wc_product->get_id() );
			$item->add_meta_data( '_cp_priced_individually', $priced_individually );
			if ( empty( $item->save() ) ) {
				continue;
			}
			$wc_order->add_item( $item );
		}
	}
}
