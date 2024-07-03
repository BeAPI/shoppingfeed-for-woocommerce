<?php

namespace ShoppingFeed\ShoppingFeedWC\Orders\Order;

use ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Api\Order\OrderResource;
use ShoppingFeed\ShoppingFeedWC\Query\Query;

/**
 * Class Metas
 * @package ShoppingFeed\ShoppingFeedWC\Orders\Order
 */
class Metas {
	/** @var OrderResource */
	public $sf_order;

	/** @var Shipping */
	public $shipping;

	/** @var array */
	public $sf_order_array;

	/** @var array */
	private $metas = array();

	public static $dont_update_inventory = 'dont_update_inventory';

	/**
	 * Metas constructor.
	 *
	 * @param $sf_order OrderResource
	 * @param $shipping Shipping
	 */
	public function __construct( $sf_order, $shipping ) {
		$this->sf_order       = $sf_order;
		$this->shipping       = $shipping;
		$this->sf_order_array = $sf_order->toArray();

		$this->add_order_referene();
		$this->add_order_channel_name();
		$this->add_order_shipping();
		$this->add_sf_store_id();
		$this->add_order_nif();

		do_action_ref_array( 'sf_add_metas', array( $this ) );
	}

	/**
	 * Add the 'buyer_identification_number' as sf_nif custom field if exists
	 *
	 * @author Stéphane Gillot
	 */
	private function add_order_nif() {
		$sf_nif = $this->sf_order_array['additionalFields']['buyer_identification_number'] ?? '';
		if ( ! empty( $sf_nif ) ) {
			$this->add_meta( 'sf_nif', $sf_nif, true );
		}
	}

	/**
	 * Add Order Reference
	 */
	private function add_order_referene() {
		$this->add_meta( Query::WC_META_SF_REFERENCE, $this->sf_order->getReference(), true );
	}

	/**
	 * Add Order Channel Name
	 */
	private function add_order_channel_name() {
		$this->add_meta( Query::WC_META_SF_CHANNEL_NAME, $this->sf_order->getChannel()->getName() );
	}

	/**
	 * Add Order Shipping Meta
	 */
	private function add_order_shipping() {
		$shipping_meta = $this->shipping->get_shipping_meta();
		if ( ! empty( $shipping_meta ) ) {
			$this->add_meta( 'sf_shipping', wp_json_encode( $shipping_meta ) );
		}
	}

	/**
	 * @return array
	 */
	public function get_metas() {
		return $this->metas;
	}

	public function add_meta( $key, $value, $unique = false ) {
		$this->metas [] = array(
			'key'    => $key,
			'value'  => $value,
			'unique' => $unique,
		);
	}

	/**
	 * Add store id
	 */
	private function add_sf_store_id() {
		$sf_order_array = $this->sf_order->toArray();
		$this->add_meta( Query::WC_META_SF_STORE_ID, $sf_order_array['storeId'] );
	}
}
