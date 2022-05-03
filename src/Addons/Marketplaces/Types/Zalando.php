<?php

namespace ShoppingFeed\ShoppingFeedWC\Addons\Marketplaces\Types;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

use ShoppingFeed\ShoppingFeedWC\Addons\Marketplace;
use ShoppingFeed\ShoppingFeedWC\Orders\Order\Metas;

class Zalando {

	use Marketplace;

	public function __construct() {
		add_action( 'sf_add_metas', [ $this, 'add_metas' ] );
	}

	/**
	 * @param $metas Metas
	 */
	public function add_metas( $metas ) {
		if (
			empty( $metas->sf_order_array['additionalFields'] ) ||
			! is_array( $metas->sf_order_array['additionalFields'] ) ||
			true !== $this->is_zalando( $metas->sf_order )
		) {
			return;
		}

		$metas->add_meta( 'sf_type', __( 'Zalando', 'shopping-feed' ) );

		foreach ( $metas->sf_order_array['additionalFields'] as $key => $value ) {
			$metas->add_meta( 'sf_' . trim( $key ), $value );
		}
	}
}
