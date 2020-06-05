<?php

namespace ShoppingFeed\ShoppingFeedWC\Addons\Marketplaces\Types;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

use ShoppingFeed\ShoppingFeedWC\Addons\Marketplace;
use ShoppingFeed\ShoppingFeedWC\Orders\Order\Metas;

class AmazonPrime {

	use Marketplace;

	public function __construct() {
		add_action( 'sf_add_metas', array( $this, 'add_metas' ) );
	}

	/**
	 * @param $metas Metas
	 */
	public function add_metas( $metas ) {
		if (
			empty( $metas->sf_order_array['additionalFields']['is_prime'] ) ||
			true !== $this->is_amazon( $metas->sf_order )
		) {
			return;
		}

		$metas->add_meta( 'sf_type', __( 'Amazon Prime', 'shopping-feed' ) );
	}
}
