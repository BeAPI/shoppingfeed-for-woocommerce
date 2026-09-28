<?php

namespace ShoppingFeed\ShoppingFeedWC\Addons\Advanced;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

/**
 * Helper for Advanced product fields.
 */
class AdvancedHelper {

	/**
	 * Handle back-compat with EAN meta saved with a key containing an index.
	 *
	 * @param \WC_Product $wc_product Product.
	 *
	 * @return string
	 */
	public static function find_old_variation_ean_meta_key( $wc_product ) {
		$meta_key = '';
		if ( 'variation' !== $wc_product->get_type() ) {
			return $meta_key;
		}

		$meta_data = $wc_product->get_meta_data();
		foreach ( $meta_data as $meta_datum ) {
			if ( false !== stripos( $meta_datum->get_data()['key'], EAN_FIELD_SLUG ) ) {
				$meta_key = $meta_datum->get_data()['key'];
				break;
			}
		}

		return $meta_key;
	}
}
