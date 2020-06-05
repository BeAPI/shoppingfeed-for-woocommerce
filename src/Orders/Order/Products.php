<?php

namespace ShoppingFeed\ShoppingFeedWC\Orders\Order;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

use ShoppingFeed\Sdk\Api\Order\OrderItem;
use ShoppingFeed\Sdk\Api\Order\OrderResource;
use ShoppingFeed\ShoppingFeedWC\ShoppingFeedHelper;
use WC_Product;

/**
 * Class Products
 * @package ShoppingFeed\Orders\Order
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

		$default_args = array(
			'name'         => $wc_product->get_name(),
			'tax_class'    => $wc_product->get_tax_class(),
			'product_id'   => $wc_product->is_type( 'variation' ) ? $wc_product->get_parent_id() : $wc_product->get_id(),
			'variation_id' => $wc_product->is_type( 'variation' ) ? $wc_product->get_id() : 0,
			'variation'    => $wc_product->is_type( 'variation' ) ? $wc_product->get_attributes() : array(),
		);

		$args = array(
			'subtotal' => $sf_product->getUnitPrice(),
			'total'    => $sf_product->getTotalPrice(),
			'quantity' => $sf_product->getQuantity(),
		);

		$wc_product_quantity = $wc_product->get_stock_quantity() ? $wc_product->get_stock_quantity() : 0;

		return array(
			'args'             => wp_parse_args( $args, $default_args ),
			'outofstock'       => ! $this->validate_product( $wc_product, $sf_product ),
			'product_quantity' => $wc_product_quantity,
			'quantity_needed'  => $sf_product->getQuantity() - $wc_product_quantity,
		);
	}

	/**
	 * @param $wc_product WC_Product
	 * @param $sf_product OrderItem
	 *
	 * @return bool
	 */
	private function validate_product( $wc_product, $sf_product ) {
		//automatically validate product if backorders or allowed and managin stock is disabled
		if ( $wc_product->backorders_allowed() || ! $wc_product->managing_stock() ) {
			return true;
		}

		return $sf_product->getQuantity() <= $wc_product->get_stock_quantity();
	}

	/**
	 * @return array
	 */
	public function get_products() {
		return $this->products;
	}
}
