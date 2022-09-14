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
	 * @throws \Exception
	 */
	public function __wakeup() {
		throw new \Exception( 'Cannot serialize singleton' );
	}

	public function get_list_args() {
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

		return wp_parse_args( ShoppingFeedHelper::wc_products_custom_query_args(), $default_args );
	}

	/**
	 * Generate products list
	 */
	public function get_list() {
		$products = $this->get_products();

		if ( ! empty( $products ) ) {
			foreach ( $products as $wc_product ) {
				yield array( new Product( $wc_product ) );
			}
		}
	}

	public function get_products( $args = array() ) {
		$query = new \WC_Product_Query( wp_parse_args( $args, $this->get_list_args() ) );

		return $query->get_products();
	}


	public function format_products( $wc_products ) {
		foreach ( $wc_products as $wc_product ) {
			yield array( new Product( $wc_product ) );
		}
	}

}
