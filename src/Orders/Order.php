<?php

namespace ShoppingFeed\ShoppingFeedWC\Orders;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

use Automattic\WooCommerce\Utilities\OrderUtil;
use ShoppingFeed\ShoppingFeedWC\Addons\Marketplace;
use ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Api\Order\OrderResource;
use ShoppingFeed\ShoppingFeedWC\Orders\Order\Address;
use ShoppingFeed\ShoppingFeedWC\Orders\Order\CustomerNote;
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

	public const RATE_ID = 999999999999;

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

	/** @var CustomerNote */
	private $note;

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
		$this->set_note();
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

		// Only run order validation if the order is fulfilled by the merchant.
		if ( ! $this->is_fulfilled_by_channel( $this->sf_order ) ) {
			$error = $this->validate_order();
			if ( is_wp_error( $error ) ) {
				ShoppingFeedHelper::get_logger()->error(
					$error->get_error_message(),
					[
						'source' => 'shopping-feed',
					]
				);

				Operations::acknowledge_error( $this->sf_order, $error->get_error_message() );

				return;
			}
		}

		//Add new order
		$wc_order = wc_create_order();

		//Addresses
		$wc_order->set_shipping_address( $this->shipping_address );
		$wc_order->set_billing_address( $this->billing_address );

		//Note
		$wc_order->set_customer_note( $this->note->get_note() );

		//Payment
		try {
			$wc_order->set_prices_include_tax( true );
			$wc_order->set_payment_method( $this->payment->get_method() );
		} catch ( \Exception $exception ) {
			$message = sprintf(
			/* translators: %1$1s: Order id. %2$2s: Error message. */
				__( 'Cant set payment to order  %1$1s => %2$2s', 'shopping-feed' ),
				$wc_order->get_id(),
				$exception->getMessage()
			);

			ShoppingFeedHelper::get_logger()->error(
				$message,
				[
					'source' => 'shopping-feed',
				]
			);

			Operations::acknowledge_error( $this->sf_order, $message );

			return;
		}

		if ( ! empty( $this->shipping->get_shipping_rate() ) ) {
			/** @var \WC_Shipping_Rate $shipping_rate */
			$shipping_rate = $this->shipping->get_shipping_rate();
			$item          = new \WC_Order_Item_Shipping();
			$item->set_shipping_rate( $shipping_rate );
			$item->set_taxes(
				[
					'total' => [
						self::RATE_ID => (float) $this->sf_order->toArray()['additionalFields']['shipping_tax'],
					],
				]
			);
			$item->save();
			$wc_order->add_item( $item );
			do_action( 'sf_after_order_add_shipping', $item, $wc_order );
		} else {
			try {
				$item = new \WC_Order_Item_Shipping();
				$item->set_method_title( $this->shipping->get_method() );
				$item->set_total( $this->shipping->get_total() );
				$item->set_taxes(
					[
						'total' => [
							self::RATE_ID => (float) $this->sf_order->toArray()['additionalFields']['shipping_tax'],
						],
					]
				);
				$item->save();
				$wc_order->add_item( $item );
				do_action( 'sf_after_order_add_shipping', $item, $wc_order );
			} catch ( \Exception $exception ) {
				$message = sprintf(
				/* translators: %s: Order id. */
					__( 'Cant set shipping for the order with the reference %s', 'shopping-feed' ),
					$this->sf_order->getReference(),
					$exception->getMessage()
				);

				ShoppingFeedHelper::get_logger()->error(
					$message,
					[
						'source' => 'shopping-feed',
					]
				);

				Operations::acknowledge_error( $this->sf_order, $message );

				return;
			}
		}

		// $this->products is not empty and all the products are in stock
		foreach ( $this->products as $product ) {
			$item = new \WC_Order_Item_Product();
			$item->set_props( $product['args'] );
			$item->set_order_id( $wc_order->get_id() );
			$item->save();
			$wc_order->add_item( $item );
			do_action( 'sf_after_order_add_item', $item, $wc_order );
		}

		/**
		 * Add Extra Fees
		 */
		if ( ! empty( $this->fees ) ) {
			$pre_save_fees = apply_filters( 'sf_pre_add_fees', false, $wc_order, $this->sf_order, $this->fees );

			if ( ! $pre_save_fees ) {
				$item_fee = new \WC_Order_Item_Fee();
				$item_fee->set_name( __( 'Fees', 'shopping-feed' ) );
				$item_fee->set_amount( $this->fees );
				$item_fee->set_tax_status( 'none' );
				$item_fee->set_total( $this->fees );
				$wc_order->add_item( $item_fee );
			}
		}

		/**
		 * Add Metas
		 */
		if ( ! empty( $this->metas->get_metas() ) ) {
			foreach ( $this->metas->get_metas() as $meta ) {
				$wc_order->add_meta_data( $meta['key'], $meta['value'], $meta['unique'] );
			}
		}

		$total_product_tax = 0;
		foreach ( $this->sf_order->getItems() as $item ) {
			$total_product_tax += $item->getTaxAmount();
		}

		$total_shipping_tax = 0;
		$lol                = $this->sf_order->toArray();
		if ( isset( $lol['additionalFields']['shipping_tax'] ) ) {
			$total_shipping_tax = (float) $lol['additionalFields']['shipping_tax'];
		}

		$tax = new \WC_Order_Item_Tax();
		$tax->set_props(
			[
				'rate_code'          => 'SF-VAT',
				'rate_id'            => self::RATE_ID,
				'label'              => __( 'VAT', 'shopping-feed' ),
				'tax_total'          => $total_product_tax,
				'shipping_tax_total' => $total_shipping_tax,
			]
		);
		$tax->save();
		$wc_order->add_item( $tax );

		$wc_order->set_status( $this->status->get_name(), $this->status->get_note() );
		$wc_order->calculate_totals( false );
		$wc_order->save();

		do_action( 'sf_before_add_order', $wc_order );

		//Acknowledge the order so we will not get it next time
		Operations::acknowledge_order( $wc_order->get_id() );
	}

	/**
	 * Check if the order can be imported in Woocommerce.
	 *
	 * Ensure the order is not empty and there are enough stock for each product.
	 *
	 * @return null|\WP_Error
	 */
	private function validate_order() {
		// Do not create and order with no products
		if ( empty( $this->products ) ) {
			$message = sprintf(
			/* translators: %s: Order reference */
				__( 'Cannot add order with no products : %s', 'shopping-feed' ),
				$this->sf_order->getReference()
			);

			return new \WP_Error( 'sf-invalid-order', $message );
		}

		// Do not create and order if at least one product is out of stock
		$missing_products_name = [];
		foreach ( $this->products as $product ) {
			if ( ! $product['is_available'] ) {
				$missing_products_name[] = sprintf( '%s (SKU: %s)', $product['args']['name'], $product['sf_ref'] );
			}
		}

		if ( ! empty( $missing_products_name ) ) {
			$message = sprintf(
			/* translators: %s: Products */
				__( 'Some product(s) are out of stock : %s.', 'shopping-feed' ),
				implode( ', ', $missing_products_name ),
				$this->sf_order->getId(),
				$this->sf_order->getReference(),
			);

			return new \WP_Error( 'sf-invalid-order', $message );
		}

		return null;
	}

	/**
	 * Check if the order already exists in WP
	 *
	 * @param $sf_order OrderResource
	 *
	 * @return bool
	 */
	public static function exists( $sf_order ) {
		$args = [ Query::WC_META_SF_REFERENCE => $sf_order->getReference() ];
		if ( class_exists( OrderUtil::class ) && OrderUtil::custom_orders_table_usage_is_enabled() ) {
			$args = [
				'meta_query' => [
					[
						'key'   => Query::WC_META_SF_REFERENCE,
						'value' => $sf_order->getReference(),
					],
				],
			];
		}

		return ! empty( wc_get_orders( $args ) );
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

	private function set_note() {
		$this->note = new CustomerNote( $this->sf_order );
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

		$sf_store_id     = $order->get_meta( Query::WC_META_SF_STORE_ID );
		$sf_reference    = $order->get_meta( Query::WC_META_SF_REFERENCE );
		$sf_channel_name = $order->get_meta( Query::WC_META_SF_CHANNEL_NAME );

		return array(
			'order'           => $order,
			'sf_store_id'     => $sf_store_id,
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

		return ! empty( $wc_order->get_meta( Query::WC_META_SF_REFERENCE ) );
	}

	/**
	 * Check if this is an SF order and if we can update stock
	 *
	 * @param $wc_order \WC_Order
	 *
	 * @return boolean
	 */
	public static function can_update_stock( $wc_order ) {
		return empty( $wc_order->get_meta( Query::WC_META_SF_REFERENCE ) ) || empty( $wc_order->get_meta( Metas::$dont_update_inventory ) );
	}
}
