<?php

namespace ShoppingFeed\ShoppingFeedWC\Orders\Order;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

use ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Api\Order\OrderResource;
use ShoppingFeed\ShoppingFeedWC\Orders\Order;
use ShoppingFeed\ShoppingFeedWC\ShoppingFeedHelper;

/**
 * Class Shipping
 * @package ShoppingFeed\Orders\Order
 */
class Shipping {
	/**
	 * @var OrderResource $sf_order
	 */
	public $sf_order;

	/**
	 * @var string $method
	 */
	public $method;

	/**
	 * @var string $colis_number
	 */
	public $colis_number;

	/**
	 * @var float $total
	 */
	private $total;

	/** @var float $total_tax */
	private $total_tax;

	/** @var bool $include_vat */
	private $include_vat;

	/** @var array|\WC_Shipping_Rate $shipping_rate */
	private $shipping_rate;

	/** @var array $shipping_meta */
	private $shipping_meta;

	/**
	 * Shipping constructor.
	 *
	 * @param OrderResource $sf_order
	 * @param bool $include_vat
	 */
	public function __construct( $sf_order, $include_vat = false ) {
		$this->sf_order    = $sf_order;
		$this->include_vat = $include_vat;

		$this->set_shipping_method_and_colis_number();
		$this->set_total();
		$this->set_total_tax();
	}

	/**
	 * @return string
	 */
	public function get_method() {
		return $this->method;
	}

	/**
	 * @return string
	 */
	public function get_colis_number() {
		return $this->colis_number;
	}

	/**
	 * Set shipping method and colis number
	 */
	private function set_shipping_method_and_colis_number() {
		$this->method       = $this->sf_order->getShipment()['carrier'];
		$this->colis_number = '';

		do_action_ref_array( 'sf_set_shipping_method_and_colis_number', array( $this ) );

		$this->set_shipping_rate();
	}

	/**
	 * Set shipping rate
	 */
	private function set_shipping_rate() {
		$this->shipping_rate = array();

		$default_shipping_method = ShoppingFeedHelper::get_default_shipping_method();

		if ( empty( $this->method ) && empty( $default_shipping_method ) ) {
			return;
		}
		$shipping_rate = ShoppingFeedHelper::get_wc_shipping_from_sf_carrier( $this->method );
		if ( empty( $shipping_rate ) ) {
			$shipping_rate = $default_shipping_method;
		}

		$taxes = [];
		if ( $this->include_vat && $this->get_total_tax() > 0 ) {
			$taxes = [
				'total' => [
					Order::RATE_ID => $this->get_total_tax(),
				],
			];
		}

		$rate                = new \WC_Shipping_Rate(
			$shipping_rate['method_rate_id'],
			$shipping_rate['method_title'],
			$this->get_total_shipping() ? $this->get_total_shipping() : ShoppingFeedHelper::get_sf_default_shipping_fees(),
			$taxes,
			$shipping_rate['method_rate_id'],
			$shipping_rate['method_id']
		);
		$this->shipping_meta = $shipping_rate;
		$this->shipping_rate = $rate;
	}

	/**
	 * Get total shipping amount.
	 *
	 * @return float
	 */
	public function get_total() {
		return $this->total;
	}

	/**
	 * Set total shipping amount.
	 */
	public function set_total() {
		$this->total = $this->get_total_shipping();
	}

	/**
	 * Get total shipping tax amount.
	 *
	 * @return float
	 */
	public function get_total_tax() {
		return $this->total_tax;
	}

	/**
	 * Set total shipping tax amount.
	 */
	public function set_total_tax() {
		$this->total_tax = isset( $this->sf_order->toArray()['additionalFields']['shipping_tax'] ) ? (float) $this->sf_order->toArray()['additionalFields']['shipping_tax'] : 0;
	}

	/**
	 * @return array|\WC_Shipping_Rate $shipping_rate
	 */
	public function get_shipping_rate() {
		return $this->shipping_rate;
	}

	/**
	 * @return array
	 */
	public function get_shipping_meta() {
		return $this->shipping_meta;
	}

	/**
	 * @return float
	 */
	private function get_total_shipping() {
		return ( new Payment( $this->sf_order ) )->get_total_shipping();
	}
}
