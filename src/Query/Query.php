<?php

namespace ShoppingFeed\ShoppingFeedWC\Query;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

/**
 * Class Query to manage custom WC queries & metas
 */
class Query {
	/**
	 * Custom Meta for SF accpunt ID
	 * @var string
	 */
	public static $wc_meta_sf_store_id = 'sf_store_id';

	/**
	 * Custom Meta for SF reference
	 * @var string
	 */
	public static $wc_meta_sf_reference = 'sf_reference';

	/**
	 * Custom Meta for SF channel name
	 * @var string
	 */
	public static $wc_meta_sf_channel_name = 'sf_marketplace';

	public function __construct() {
		add_filter(
			'woocommerce_order_data_store_cpt_get_orders_query',
			array(
				$this,
				'wc_get_by_sf_reference',
			),
			10,
			2
		);
	}

	/**
	 * Get orders by wc_meta_sf_reference
	 *
	 * @param array $query - Args for WP_Query.
	 * @param array $query_vars - Query vars from WC_Order_Query.
	 *
	 * @return array modified $query
	 */
	public function wc_get_by_sf_reference( $query, $query_vars ) {
		if ( ! empty( $query_vars[ self::$wc_meta_sf_reference ] ) ) {
			$query['meta_query'][] = array(
				'key'   => self::$wc_meta_sf_reference,
				'value' => esc_attr( $query_vars[ self::$wc_meta_sf_reference ] ),
			);
		}

		return $query;
	}
}
