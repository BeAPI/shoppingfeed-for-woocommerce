<?php

namespace ShoppingFeed\ShoppingFeedWC\Orders\Order;

use ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Api\Order\OrderResource;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

/**
 * Class Status
 * @package ShoppingFeed\Orders\Order
 */
class Fees {

	const FEES_FIELD = 'INTERETBCA';

	/**
	 * @param $sf_order OrderResource
	 *
	 * @return float|int
	 */
	public static function get_fees( $sf_order ) {
		$sf_order = $sf_order->toArray();
		if ( empty( $sf_order['additionalFields'][ self::FEES_FIELD ] ) ) {
			return 0;
		}

		return (float) $sf_order['additionalFields'][ self::FEES_FIELD ];
	}
}
