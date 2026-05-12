<?php

namespace ShoppingFeed\ShoppingFeedWC\Orders\Order;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

use ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Api\Order\{OrderItem, OrderResource};
use ShoppingFeed\ShoppingFeedWC\Orders\Order;
use ShoppingFeed\ShoppingFeedWC\ShoppingFeedHelper;

/**
 * @psalm-consistent-constructor
 */
class Products {

	/**
	 * @var OrderResource $sf_order
	 */
	private $sf_order;

	/**
	 * @var bool $include_vat
	 */
	private $include_vat;

	/**
	 * @var array $products
	 */
	private $products;

	/**
	 * Products constructor.
	 *
	 * @param OrderResource $sf_order
	 * @param bool $include_vat
	 */
	public function __construct( $sf_order, $include_vat = false ) {
		$this->sf_order    = $sf_order;
		$this->include_vat = $include_vat;
		$this->set_products();
	}

	/**
	 * Set Products
	 */
	private function set_products() {
		$this->products     = array();
		$references_aliases = $this->sf_order->getItemsReferencesAliases();
		foreach ( $this->sf_order->getItems() as $sf_product ) {
			$product = $this->mapping_product( $sf_product, $references_aliases );
			if ( empty( $product ) ) {
				ShoppingFeedHelper::get_logger()->error(
					sprintf(
						/* translators: %1$s: product reference or alias, %2$s: original product reference, %3$s: order id. */
						__( 'Can\'t match product "%1$s" (original ref: %2$s) in order %3$s', 'shopping-feed' ),
						( $references_aliases[ $sf_product->getReference() ] ?? $sf_product->getReference() ),
						$sf_product->getReference(),
						$this->sf_order->getId()
					),
					array(
						'source' => 'shopping-feed',
					)
				);
				continue;
			}

			$this->products[] = $product;
		}
	}

	/**
	 * Map products in SF order to Woocommerce products.
	 *
	 * @param OrderItem $sf_product
	 * @param array $references_aliases
	 *
	 * @return array
	 */
	private function mapping_product( $sf_product, $references_aliases = [] ) {
		$product_identifier = ShoppingFeedHelper::get_sf_feed_product_identifier();
		$wc_product_id      = $references_aliases[ $sf_product->getReference() ] ?? $sf_product->getReference();

		if ( 'sku' === $product_identifier ) {

			/**
			 * Filter the SKU from the ShoppingFeed order.
			 *
			 * @param string $wc_product_id
			 * @param OrderItem $sf_product
			 * @param array $references_aliases
			 */
			$wc_product_id = apply_filters( 'shopping_feed_order_products_product_sku', $wc_product_id, $sf_product, $references_aliases );

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

		if ( $this->include_vat && $sf_product->getTaxAmount() > 0 ) {
			$args['taxes'] = [
				'subtotal' => [
					Order::RATE_ID => $sf_product->getTaxAmount(),
				],
				'total'    => [
					Order::RATE_ID => $sf_product->getTaxAmount(),
				],
			];
		}

		return array(
			'args'         => $args,
			'is_available' => $wc_product->is_in_stock() && $wc_product->has_enough_stock( $sf_product_quantity ),
			'sf_ref'       => $sf_product->getReference(),
			'wc_ref'       => $references_aliases[ $sf_product->getReference() ] ?? $sf_product->getReference(),
		);
	}

	/**
	 * @return array
	 */
	public function get_products() {
		return $this->products;
	}
}
