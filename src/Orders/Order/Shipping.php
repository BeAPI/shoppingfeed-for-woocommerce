<?php

namespace ShoppingFeed\ShoppingFeedWC\Orders\Order;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

use ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Api\Order\OrderResource;
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

	/** @var array|\WC_Shipping_Rate $shipping_rate */
	private $shipping_rate;

	/** @var array $shipping_meta */
	private $shipping_meta;

	/**
	 * Shipping constructor.
	 *
	 * @param $sf_order OrderResource
	 */
	public function __construct( $sf_order ) {
		$this->sf_order = $sf_order;

		$this->set_shipping_method_and_colis_number();
		$this->set_total();
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

		$rate                = new \WC_Shipping_Rate(
			$shipping_rate['method_rate_id'],
			$shipping_rate['method_title'],
			$this->get_total_shipping() ? $this->get_total_shipping() : ShoppingFeedHelper::get_sf_default_shipping_fees(),
			array(),
			$shipping_rate['method_rate_id'],
			$shipping_rate['method_id']
		);
		$this->shipping_meta = $shipping_rate;
		$this->shipping_rate = $rate;
	}

	/**
	 * @return float
	 */
	public function get_total() {
		return $this->total;
	}

	/**
	 * Set total
	 */
	public function set_total() {
		$this->total = $this->get_total_shipping();
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
