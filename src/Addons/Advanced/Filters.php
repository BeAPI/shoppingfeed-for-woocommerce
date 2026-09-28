<?php

namespace ShoppingFeed\ShoppingFeedWC\Addons\Advanced;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

/**
 * Feed filters for EAN and Brand (always active).
 */
class Filters {

	public function __construct() {
		add_filter(
			'shopping_feed_custom_ean',
			function ( $meta_key, $wc_product = false ) {
				$meta_key = EAN_FIELD_SLUG;

				if ( $wc_product instanceof \WC_Product_Variation && empty( $wc_product->get_meta( $meta_key ) ) ) {
					$old_meta_key = AdvancedHelper::find_old_variation_ean_meta_key( $wc_product );
					if ( ! empty( $old_meta_key ) ) {
						$meta_key = $old_meta_key;
					}
				}

				return $meta_key;
			},
			10,
			2
		);

		add_filter(
			'shopping_feed_custom_brand_taxonomy',
			function () {
				return Taxonomies::BRAND_TAXONOMY_SLUG;
			}
		);
	}
}
