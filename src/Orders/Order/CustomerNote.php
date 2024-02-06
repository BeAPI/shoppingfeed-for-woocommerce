<?php

namespace ShoppingFeed\ShoppingFeedWC\Orders\Order;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

use ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Api\Order\OrderResource;

/**
 * Class CustomerNote
 * @package ShoppingFeed\Orders\Order
 */
class CustomerNote {
	/**
	 * @var OrderResource $sf_order
	 */
	public $sf_order;

	/**
	 * @var mixed|string
	 */
	public $note;

	/**
	 * CustomerNote constructor.
	 *
	 * @param $sf_order OrderResource
	 */
	public function __construct( $sf_order ) {
		$this->sf_order = $sf_order;

		$this->note = $this->set_note();
	}

	/**
	 * Extract and return the "other" value from the sf_order
	 *
	 * @return mixed|string
	 * @author Stéphane Gillot
	 */
	private function set_note() {

		$sf_address = $this->sf_order->getShippingAddress();

		return ! empty( $sf_address['other'] ) ? $sf_address['other'] : '';
	}

	/**
	 * Return the customer's note string
	 *
	 * @return mixed|string
	 * @author Stéphane Gillot
	 */
	public function get_note() {
		return $this->note;
	}
}
