<?php

namespace ShoppingFeed\ShoppingFeedWC\Orders;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

use ShoppingFeed\Sdk\Api\Order\OrderResource;
use ShoppingFeed\ShoppingFeedWC\Addons\Marketplace;
use ShoppingFeed\ShoppingFeedWC\Orders\Order\Address;
use ShoppingFeed\ShoppingFeedWC\Orders\Order\Fees;
use ShoppingFeed\ShoppingFeedWC\Orders\Order\Metas;
use ShoppingFeed\ShoppingFeedWC\Orders\Order\Payment;
use ShoppingFeed\ShoppingFeedWC\Orders\Order\Products;
use ShoppingFeed\ShoppingFeedWC\Orders\Order\Shipping;
use ShoppingFeed\ShoppingFeedWC\Orders\Order\Status;
use ShoppingFeed\ShoppingFeedWC\Query\Query;
use ShoppingFeed\ShoppingFeedWC\ShoppingFeedHelper;

/**
 * Class Order
 * @package ShoppingFeed\Orders
 */
class Order {

	use Marketplace;

	/** @var OrderResource $sf_order */
	private $sf_order;

	/** @var array $shipping_address */
	private $shipping_address;

	/** @var array $billing_address */
	private $billing_address;

	/** @var array $products */
	private $products;

	/** @var Payment */
	private $payment;

	/** @var Shipping */
	private $shipping;

	/** @var Status */
	private $status;

	/** @var float */
	private $fees;

	/** @var Metas */

	private $metas;

	/**
	 * Order constructor.
	 * Init all order requirements
	 *
	 * @param $sf_order OrderResource
	 */
	public function __construct( $sf_order ) {

		$this->sf_order = $sf_order;

		$this->set_shipping_address();
		$this->set_billing_address();
		$this->set_payment();
		$this->set_shipping();
		$this->set_products();
		$this->set_status();
		$this->set_fees();
		$this->set_metas();
	}

	/**
	 * Add new order
	 */
	public function add() {
		//Add new order
		$wc_order = wc_create_order();

		//Addresses
		$wc_order->set_address( $this->shipping_address, 'shipping' );
		$wc_order->set_address( $this->billing_address );

		//Payment
		try {
			$wc_order->set_prices_include_tax( $this->payment->get_total() );
			$wc_order->set_payment_method( $this->payment->get_method() );
		} catch ( \Exception $exception ) {
			ShoppingFeedHelper::get_logger()->error(
				sprintf(
				/* translators: %1$1s: Order id. %2$2s: Error message. */
					__( 'Cant set payment to order  %1$1s => %2$2s', 'shopping-feed' ),
					$wc_order->get_id(),
					$exception->getMessage()
				),
				array(
					'source' => 'shopping-feed',
				)
			);
		}

		if ( ! empty( $this->shipping->get_shipping_rate() ) ) {
			/** @var \WC_Shipping_Rate $shipping_rate */
			$shipping_rate = $this->shipping->get_shipping_rate();
			$item          = new \WC_Order_Item_Shipping();
			$item->set_shipping_rate( $shipping_rate );
			$wc_order->add_item( $item );
		} else {
			try {
				$item = new \WC_Order_Item_Shipping();
				$item->set_method_title( $this->shipping->get_method() );
				$item->set_total( $this->shipping->get_total() );
				$item->save();
				$wc_order->add_item( $item );
			} catch ( \Exception $exception ) {
				ShoppingFeedHelper::get_logger()->error(
					sprintf(
					/* translators: %1$1s: Order id. %2$2s: Error message. */
						__( 'Cant set shipping to order  %1$1s => %2$2s', 'shopping-feed' ),
						$wc_order->get_id(),
						$exception->getMessage()
					),
					array(
						'source' => 'shopping-feed',
					)
				);
			}
		}

		$valid = true;
		/**
		 * If we cant match any product in the order
		 */
		if ( empty( $this->products ) ) {
			$valid = false;
			ShoppingFeedHelper::get_logger()->error(
				sprintf(
				/* translators: %1$1s: Order reference. %2$2s: Order id. */
					__( 'Cant add order with no products  %1$1s => %2$2s', 'shopping-feed' ),
					$this->sf_order->getReference(),
					$this->sf_order->getId()
				),
				array(
					'source' => 'shopping-feed',
				)
			);
		} else {
			foreach ( $this->products as $product ) {
				$item = new \WC_Order_Item_Product();
				$item->set_props( $product['args'] );
				$item->set_order_id( $wc_order->get_id() );
				/**
				 * If we the product is out of stock we add a meta data to mark it and set the order as failed to notify the merchant
				 */
				if ( $product['outofstock'] ) {
					$valid = false;
					$item->add_meta_data(
						'OUT_OF_STOCK',
						sprintf(
						/* translators: %1$1s: Product quantity. %2$2s: Quantity needed. */
							__( 'Available: %1$1s - Need : %2$2s', 'shopping-feed' ),
							$product['product_quantity'],
							$product['quantity_needed']
						)
					);
				}
				$item->save();
				$wc_order->add_item( $item );
				do_action( 'sf_after_order_add_item', $item, $wc_order );
			}
		}

		/**
		 * Add Extra Fees
		 */
		if ( ! empty( $this->fees ) ) {
			$pre_save_fees = apply_filters( 'sf_pre_add_fees', $wc_order, $this );

			if ( ! $pre_save_fees ) {
				$item_fee = new \WC_Order_Item_Fee();
				$item_fee->set_name( __( 'Fees', 'shopping-feed' ) );
				$item_fee->set_amount( $this->fees );
				$item_fee->set_tax_status( 'none' );
				$item_fee->set_total( $this->fees );
				$wc_order->add_item( $item_fee );
			}

			do_action( 'sf_after_order_add_fee_item', $this->fees, $wc_order, $this );
		}

		/**
		 * Add Metas
		 */
		if ( ! empty( $this->metas->get_metas() ) ) {
			foreach ( $this->metas->get_metas() as $meta ) {
				$wc_order->add_meta_data( $meta['key'], $meta['value'], $meta['unique'] );
			}
		}

		/**
		 * If one or more products are unavailable
		 */
		if ( $valid ) {
			$wc_order->set_status( $this->status->get_name(), $this->status->get_note() );
			$message = '';
		} else {
			$message = __( 'Products are unavailable', 'shopping-feed' );
			$wc_order->set_status( 'wc-failed', $message );
		}

		$wc_order->calculate_totals( false );
		$wc_order->save();

		do_action( 'sf_before_add_order', $wc_order );

		//Acknowledge the order so we will not get it next time
		Operations::acknowledge_order( $wc_order->get_id(), $message );
	}


	/**
	 * Check if the order already exists in WP
	 *
	 * @param $sf_order OrderResource
	 *
	 * @return bool
	 */
	public static function exists( $sf_order ) {
		return ! empty( wc_get_orders( array( Query::$wc_meta_sf_reference => $sf_order->getReference() ) ) );
	}


	/**
	 * Set shipping from SF shipping
	 */
	private function set_shipping() {
		$this->shipping = new Shipping( $this->sf_order );
	}

	/**
	 * Set Payment from SF payment
	 */
	private function set_payment() {
		$this->payment = new Payment( $this->sf_order );
	}

	/**
	 * Set Billing address from SF address
	 */
	private function set_billing_address() {
		$address               = new Address( $this->sf_order->getBillingAddress() );
		$this->billing_address = $address->get_formatted_address();
		//If the billing address phone is empty, get the shipping one to display phone on the BO
		if ( empty( $this->billing_address['phone'] ) ) {
			$this->billing_address['phone'] = $this->shipping_address['phone'];
		}
	}

	/**
	 * Set Shipping address from SF address
	 */
	private function set_shipping_address() {
		$address                = new Address( $this->sf_order->getShippingAddress() );
		$this->shipping_address = $address->get_formatted_address();
	}

	/**
	 * Set products from SF products
	 */
	private function set_products() {
		if ( ! $this->has_products() ) {
			$this->products = array();
		}

		$products       = new Products( $this->sf_order );
		$this->products = $products->get_products();
	}

	/**
	 * Check if SF order has products
	 * @return bool
	 */
	private function has_products() {
		return 0 < count( $this->sf_order->getItems() );
	}

	/**
	 * Set status from SF status
	 */
	private function set_status() {
		$this->status = new Status( $this->sf_order );
	}

	/**
	 * Set Additional Fees
	 */
	private function set_fees() {
		$this->fees = Fees::get_fees( $this->sf_order );
	}

	/**
	 * Set Additional Metas
	 */
	private function set_metas() {
		$this->metas = new Metas( $this->sf_order, $this->shipping );
	}

	/**
	 * Return Order with Custom Metas for SF Orders
	 *
	 * @param $order_id
	 *
	 * @return array
	 */
	public static function get_order_sf_metas( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order instanceof \WC_Order ) {
			return array();
		}

		$sf_reference    = $order->get_meta( Query::$wc_meta_sf_reference );
		$sf_channel_name = $order->get_meta( Query::$wc_meta_sf_channel_name );

		return array(
			'order'           => $order,
			'sf_reference'    => $sf_reference,
			'sf_channel_name' => $sf_channel_name,
		);
	}

	/**
	 * Check if this is an imported order from SF
	 *
	 * @param $wc_order \WC_Order|int
	 *
	 * @return boolean
	 */
	public static function is_sf_order( $wc_order ) {
		$wc_order = wc_get_order( $wc_order );
		if ( ! $wc_order instanceof \WC_Order ) {
			return false;
		}

		return ! empty( $wc_order->get_meta( Query::$wc_meta_sf_reference ) );
	}

	/**
	 * Check if this is an SF order and if we can update stock
	 *
	 * @param $wc_order \WC_Order
	 *
	 * @return boolean
	 */
	public static function can_update_stock( $wc_order ) {
		return empty( $wc_order->get_meta( Query::$wc_meta_sf_reference ) ) || empty( $wc_order->get_meta( Metas::$dont_update_inventory ) );
	}
}
