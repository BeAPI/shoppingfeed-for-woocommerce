<?php

namespace ShoppingFeed\ShoppingFeedWC\Orders\Order;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

use ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Api\Order\{OrderItem, OrderResource};
use ShoppingFeed\ShoppingFeedWC\ShoppingFeedHelper;
use WC_Product;

/**
 * @psalm-consistent-constructor
 */
class Products {

	/**
	 * @var OrderResource $sf_order
	 */
	private $sf_order;

	/**
	 * @var array $products
	 */
	private $products;

	/**
	 * Products constructor.
	 *
	 * @param $sf_order OrderResource
	 */
	public function __construct( $sf_order ) {
		$this->sf_order = $sf_order;
		$this->set_products();
	}

	/**
	 * Set Products
	 */
	private function set_products() {
		$this->products = array();
		foreach ( $this->sf_order->getItems() as $sf_product ) {
			$product = $this->mapping_product( $sf_product );
			if ( empty( $product ) ) {
				ShoppingFeedHelper::get_logger()->error(
					sprintf(
					/* translators: %1$1s: Product reference. %2$2s: Order id. */
						__( 'cant match product  %1$1s => in order %2$2s', 'shopping-feed' ),
						$sf_product->getReference(),
						$this->sf_order->getId()
					),
					array(
						'source' => 'shopping-feed',
					)
				);
				continue;
			}
			$this->products[] = $this->mapping_product( $sf_product );
		}
	}

	/**
	 * @param $sf_product OrderItem
	 *
	 * @return array
	 */
	private function mapping_product( $sf_product ) {

		$product_identifier = ShoppingFeedHelper::get_sf_feed_product_identifier();
		$wc_product_id      = $sf_product->getReference();

		if ( 'sku' === $product_identifier ) {
			$wc_product_id = wc_get_product_id_by_sku( $wc_product_id );
			if ( ! $wc_product_id ) {
				return array();
			}
		}

		$wc_product = wc_get_product( $wc_product_id );
		if ( ! $wc_product ) {
			return array();
		}

		$sf_product_quantity = $sf_product->getQuantity();

		$args = array(
			'name'         => $wc_product->get_name(),
			'tax_class'    => $wc_product->get_tax_class(),
			'product_id'   => $wc_product->is_type( 'variation' ) ? $wc_product->get_parent_id() : $wc_product->get_id(),
			'variation_id' => $wc_product->is_type( 'variation' ) ? $wc_product->get_id() : 0,
			'variation'    => $wc_product->is_type( 'variation' ) ? $wc_product->get_attributes() : array(),
			'subtotal'     => $sf_product->getUnitPrice(),
			'total'        => $sf_product->getTotalPrice(),
			'quantity'     => $sf_product_quantity,
		);

		return array(
			'args'         => $args,
			'is_available' => $wc_product->is_in_stock() && $wc_product->has_enough_stock( $sf_product_quantity ),
		);
	}

	/**
	 * @return array
	 */
	public function get_products() {
		return $this->products;
	}
}
