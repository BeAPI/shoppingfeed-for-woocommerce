<?php
namespace ShoppingFeed\ShoppingFeedWC\Products;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

use ShoppingFeed\ShoppingFeedWC\ShoppingFeedHelper;

/**
 * @psalm-consistent-constructor
 */
class Products {

	/**
	 * @var Products
	 */
	private static $instance;

	/**
	 * Get the singleton instance.
	 *
	 * @return Products
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new static();
		}

		return self::$instance;
	}

	/**
	 * Singleton instance can't be cloned.
	 */
	private function __clone() {
	}

	/**
	 * Singleton instance can't be serialized.
	 */
	private function __wakeup() {
	}

	/**
	 * Generate products list
	 */
	public function get_list() {
		$default_args = array(
			'limit'   => - 1,
			'orderby' => 'date',
			'order'   => 'DESC',
			'status'  => 'publish',
		);

		/**
		 * Export only the categories selected in BO
		 */
		$export_categories = ShoppingFeedHelper::get_sf_feed_export_categories();
		if ( ! empty( $export_categories ) ) {
			$default_args['category'] = array_map(
				function ( $category_id ) {
					return get_term( $category_id, ShoppingFeedHelper::wc_category_taxonomy() )->slug;
				},
				$export_categories
			);
		}

		$args = wp_parse_args( ShoppingFeedHelper::wc_products_custom_query_args(), $default_args );

		$query = new \WC_Product_Query( $args );

		if ( ! empty( $query->get_products() ) ) {
			foreach ( $query->get_products() as $wc_product ) {
				yield [ new Product( $wc_product ) ];
			}
		}
	}
}
