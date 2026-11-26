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
	 * Legacy variation EANs were stored as `{EAN_FIELD_SLUG}_{index}` (e.g.
	 * `sf_advanced_ean_field_0`). The current key is exactly `EAN_FIELD_SLUG`
	 * and must not be treated as a legacy key — otherwise an empty current
	 * value still matches and can resurrect a leftover indexed meta.
	 *
	 * @param \WC_Product|\WC_Product_Variation $wc_product Product.
	 *
	 * @return string Legacy indexed meta key, or empty string when none found.
	 */
	public static function find_old_variation_ean_meta_key( $wc_product ) {
		$meta_key = '';
		if ( 'variation' !== $wc_product->get_type() ) {
			return $meta_key;
		}

		$meta_data = $wc_product->get_meta_data();
		foreach ( $meta_data as $meta_datum ) {
			$key = $meta_datum->get_data()['key'];
			if ( EAN_FIELD_SLUG === $key ) {
				continue;
			}
			if ( false !== stripos( $key, EAN_FIELD_SLUG ) ) {
				$meta_key = $key;
				break;
			}
		}

		return $meta_key;
	}

	/**
	 * Delete legacy indexed EAN meta keys on a variation.
	 *
	 * @param \WC_Product|\WC_Product_Variation $wc_product Product.
	 */
	public static function delete_old_variation_ean_meta_keys( $wc_product ) {
		if ( 'variation' !== $wc_product->get_type() ) {
			return;
		}

		foreach ( $wc_product->get_meta_data() as $meta_datum ) {
			$key = $meta_datum->get_data()['key'];
			if ( EAN_FIELD_SLUG === $key ) {
				continue;
			}
			if ( false !== stripos( $key, EAN_FIELD_SLUG ) ) {
				$wc_product->delete_meta_data( $key );
			}
		}
	}
}
