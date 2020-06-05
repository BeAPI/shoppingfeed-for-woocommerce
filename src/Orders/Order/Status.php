<?php
namespace ShoppingFeed\ShoppingFeedWC\Orders\Order;

// Exit on direct access
defined( 'ABSPATH' ) || exit;


use ShoppingFeed\Sdk\Api\Order\OrderResource;
use ShoppingFeed\ShoppingFeedWC\ShoppingFeedHelper;

/**
 * Class Status
 * @package ShoppingFeed\Orders\Order
 */
class Status {

	/**
	 * @var OrderResource $sf_order
	 */
	private $sf_order;

	/**
	 * @var string $name
	 */
	private $name;

	/**
	 * @var string $note
	 */
	private $note;

	/**
	 * Products constructor.
	 *
	 * @param $sf_order OrderResource
	 */
	public function __construct( $sf_order ) {
		$this->sf_order = $sf_order;

		$this->set_name( ShoppingFeedHelper::get_sf_default_order_status() );
		$this->set_note( sprintf( 'Order from : %s', $sf_order->getChannel()->getName() ) );
	}

	private function set_name( $name ) {
		$this->name = $name;
	}

	public function get_name() {
		return $this->name;
	}

	public function get_note() {
		return $this->note;
	}

	private function set_note( $note ) {
		$this->note = $note;
	}

}
