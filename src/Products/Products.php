<?php

namespace ShoppingFeed\ShoppingFeedWC\Products;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

use ShoppingFeed\ShoppingFeedWC\ShoppingFeedHelper;
use WP_Term;

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
			'limit'        => - 1,
			'orderby'      => 'date',
			'order'        => 'DESC',
			'status'       => 'publish',
			'stock_status' => 'instock',
		);

		if ( true === ShoppingFeedHelper::show_out_of_stock_products_in_feed() ) {
			$default_args['stock_status'] = [ 'instock', 'outofstock' ];
		}

		/**
		 * Export only the categories selected in BO
		 */
		$current_language  = ShoppingFeedHelper::current_language();
		$export_categories = ShoppingFeedHelper::get_sf_feed_export_categories( $current_language );
		if ( ! empty( $export_categories ) ) {
			$categories = [];
			foreach ( $export_categories as $category ) {
				$term = get_term( $category, ShoppingFeedHelper::wc_category_taxonomy() );
				if ( ! $term instanceof WP_Term ) {
					continue;
				}

				$categories[] = $term->slug;
			}
			$default_args['category'] = $categories;
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
