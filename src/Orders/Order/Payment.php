<?php

namespace ShoppingFeed\ShoppingFeedWC\Orders\Order;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

use ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Api\Order\OrderResource;

/**
 * Class Payment
 * @package ShoppingFeed\Orders\Order
 */
class Payment {

	/**
	 * @var string $method
	 */
	private $method;

	/** @var float $total */
	private $total;

	/** @var float $total_shipping */
	private $total_shipping;

	/**
	 * Payment constructor.
	 *
	 * @param $sf_order OrderResource
	 */
	public function __construct( $sf_order ) {
		$sf_payment = $sf_order->getPaymentInformation();

		$payment_gateways = WC()->payment_gateways->payment_gateways();
		if ( empty( $payment_gateways['shopping-feed'] ) ) {
			$payment_gateways['shopping-feed'] = reset( $payment_gateways );
		}

		$this->set_method( $payment_gateways['shopping-feed'] );
		$this->set_total( $sf_payment['totalAmount'] );
		$this->set_total_shipping( $sf_payment['shippingAmount'] );
	}

	/**
	 * @return string
	 */
	public function get_method() {
		return $this->method;
	}

	/**
	 * @param mixed $method
	 */
	private function set_method( $method ) {
		$this->method = $method;
	}

	/**
	 * @return float
	 */
	public function get_total() {
		return $this->total;
	}

	/**
	 * @param mixed $total
	 */
	private function set_total( $total ) {
		$this->total = ! empty( $total ) ? floatval( $total ) : 0;
	}

	/**
	 * @return float
	 */
	public function get_total_shipping() {
		return $this->total_shipping;
	}

	/**
	 * @param mixed $total_shipping
	 */
	private function set_total_shipping( $total_shipping ) {
		$this->total_shipping = ! empty( $total_shipping ) ? floatval( $total_shipping ) : 0;

	}
}
