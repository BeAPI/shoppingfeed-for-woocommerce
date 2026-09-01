<?php

namespace ShoppingFeed\ShoppingFeedWC\Products;

defined( 'ABSPATH' ) || exit;

use ShoppingFeed\ShoppingFeedWC\ShoppingFeedHelper;

/**
 * Diagnose why a product is or is not included in the products feed.
 */
class ProductFeedDiagnostic {

	/**
	 * Run diagnostic checks for a product against feed query and pipeline rules.
	 *
	 * @param int $product_id Product ID.
	 *
	 * @return array{
	 *     product_id: int,
	 *     sku: string,
	 *     name: string,
	 *     type: string,
	 *     language: string,
	 *     in_query: bool,
	 *     checks: list<array{key: string, label: string, pass: bool, detail: string}>,
	 *     variations: list<array{id: int, sku: string, included: bool, reason: string}>
	 * }
	 */
	public function diagnose( int $product_id ): array {
		$result = [
			'product_id' => $product_id,
			'sku'        => '',
			'name'       => '',
			'type'       => '',
			'language'   => '',
			'in_query'   => false,
			'checks'     => [],
			'variations' => [],
		];

		$wc_product = wc_get_product( $product_id );
		if ( ! $wc_product instanceof \WC_Product ) {
			$result['checks'][] = [
				'key'    => 'exists',
				'label'  => __( 'Product exists', 'shopping-feed' ),
				'pass'   => false,
				'detail' => __( 'No WooCommerce product found for this ID.', 'shopping-feed' ),
			];

			return $result;
		}

		$result['sku']      = (string) $wc_product->get_sku();
		$result['name']     = $wc_product->get_name();
		$result['type']     = $wc_product->get_type();
		$result['language'] = $this->get_product_language( $product_id );
		$lang               = $result['language'];

		$result['checks'][] = [
			'key'    => 'exists',
			'label'  => __( 'Product exists', 'shopping-feed' ),
			'pass'   => true,
			'detail' => sprintf(
				/* translators: 1: product name, 2: product type */
				__( 'Found "%1$s" (%2$s).', 'shopping-feed' ),
				$wc_product->get_name(),
				$wc_product->get_type()
			),
		];

		$list_args = Products::get_instance()->get_list_args( $lang );

		$result['checks'][] = $this->check_status( $wc_product, $list_args );
		$result['checks'][] = $this->check_stock_status( $wc_product, $list_args );
		$result['checks'][] = $this->check_category( $wc_product, $list_args );

		$in_query_check     = $this->check_in_query( $product_id, $lang, $list_args );
		$result['checks'][] = $in_query_check;
		$result['in_query'] = (bool) $in_query_check['pass'];

		if ( ! $result['in_query'] ) {
			return $result;
		}

		$sf_product         = new Product( $wc_product );
		$result['checks'][] = $this->check_price( $sf_product );

		if ( 'variable' === $wc_product->get_type() ) {
			$result['variations'] = $this->diagnose_variations( $sf_product );
		}

		return $result;
	}

	/**
	 * Resolve the product language for multilingual feeds (Polylang / WPML).
	 *
	 * Matches the language context used when that product ID is included in a feed.
	 *
	 * @param int $product_id Product ID.
	 *
	 * @return string Language slug or empty string when unavailable / monolingual.
	 */
	private function get_product_language( int $product_id ): string {
		if ( ! ShoppingFeedHelper::support_multilingual_feed() ) {
			return '';
		}

		if ( function_exists( 'pll_get_post_language' ) ) {
			$lang = pll_get_post_language( $product_id, 'slug' );
			return is_string( $lang ) ? $lang : '';
		}

		$lang = apply_filters(
			'wpml_element_language_code',
			null,
			[
				'element_id'   => $product_id,
				'element_type' => 'post_' . get_post_type( $product_id ),
			]
		);

		return is_string( $lang ) ? $lang : '';
	}

	/**
	 * @param array $list_args Feed product query args.
	 *
	 * @return array{key: string, label: string, pass: bool, detail: string}
	 */
	private function check_status( \WC_Product $wc_product, array $list_args ): array {
		$expected = isset( $list_args['status'] ) ? (string) $list_args['status'] : 'publish';
		$actual   = $wc_product->get_status();
		$pass     = $actual === $expected;

		return [
			'key'    => 'status',
			'label'  => __( 'Status', 'shopping-feed' ),
			'pass'   => $pass,
			'detail' => $pass
				? sprintf(
					/* translators: %s: product status */
					__( 'Status is "%s".', 'shopping-feed' ),
					$actual
				)
				: sprintf(
					/* translators: 1: actual status, 2: expected status */
					__( 'Status is "%1$s" (expected: "%2$s").', 'shopping-feed' ),
					$actual,
					$expected
				),
		];
	}

	/**
	 * @param array $list_args Feed product query args.
	 *
	 * @return array{key: string, label: string, pass: bool, detail: string}
	 */
	private function check_stock_status( \WC_Product $wc_product, array $list_args ): array {
		$allowed = isset( $list_args['stock_status'] ) ? (array) $list_args['stock_status'] : [ 'instock' ];
		$actual  = $wc_product->get_stock_status();
		$pass    = in_array( $actual, $allowed, true );

		return [
			'key'    => 'stock_status',
			'label'  => __( 'Stock status', 'shopping-feed' ),
			'pass'   => $pass,
			'detail' => $pass
				? sprintf(
					/* translators: %s: stock status */
					__( 'Stock status is "%s".', 'shopping-feed' ),
					$actual
				)
				: sprintf(
					/* translators: 1: actual stock status, 2: comma-separated allowed statuses */
					__( 'Stock status is "%1$s" (allowed: %2$s).', 'shopping-feed' ),
					$actual,
					implode( ', ', $allowed )
				),
		];
	}

	/**
	 * @param array $list_args Feed product query args.
	 *
	 * @return array{key: string, label: string, pass: bool, detail: string}
	 */
	private function check_category( \WC_Product $wc_product, array $list_args ): array {
		if ( empty( $list_args['category'] ) || ! is_array( $list_args['category'] ) ) {
			return [
				'key'    => 'category',
				'label'  => __( 'Category', 'shopping-feed' ),
				'pass'   => true,
				'detail' => __( 'No category filter configured (all categories are exported).', 'shopping-feed' ),
			];
		}

		$product_slugs = wp_get_post_terms( $wc_product->get_id(), ShoppingFeedHelper::wc_category_taxonomy(), [ 'fields' => 'slugs' ] );
		if ( is_wp_error( $product_slugs ) ) {
			$product_slugs = [];
		}

		$intersection = array_intersect( $product_slugs, $list_args['category'] );
		$pass         = ! empty( $intersection );

		return [
			'key'    => 'category',
			'label'  => __( 'Category', 'shopping-feed' ),
			'pass'   => $pass,
			'detail' => $pass
				? sprintf(
					/* translators: %s: matching category slugs */
					__( 'Product matches export categories: %s.', 'shopping-feed' ),
					implode( ', ', $intersection )
				)
				: sprintf(
					/* translators: 1: product category slugs, 2: allowed category slugs */
					__( 'Product categories (%1$s) do not match export categories (%2$s).', 'shopping-feed' ),
					! empty( $product_slugs ) ? implode( ', ', $product_slugs ) : __( 'none', 'shopping-feed' ),
					implode( ', ', $list_args['category'] )
				),
		];
	}

	/**
	 * Ground-truth check: run the feed query restricted to this product ID.
	 *
	 * @param array $list_args Feed product query args.
	 *
	 * @return array{key: string, label: string, pass: bool, detail: string}
	 */
	private function check_in_query( int $product_id, string $lang, array $list_args ): array {
		$query_args = wp_parse_args(
			[
				'include' => [ $product_id ],
				'limit'   => 1,
				'return'  => 'ids',
			],
			$list_args
		);

		$found = Products::get_instance()->get_products( $query_args, $lang );
		$found_ids = array_map(
			static function ( $item ) {
				return $item instanceof \WC_Product ? $item->get_id() : (int) $item;
			},
			(array) $found
		);
		$pass = in_array( $product_id, $found_ids, true );

		return [
			'key'    => 'in_query',
			'label'  => __( 'Included in feed query', 'shopping-feed' ),
			'pass'   => $pass,
			'detail' => $pass
				? __( 'Product is returned by the feed product query.', 'shopping-feed' )
				: __( 'Product is not returned by the feed product query (status, stock, category, or custom query args).', 'shopping-feed' ),
		];
	}

	/**
	 * @return array{key: string, label: string, pass: bool, detail: string}
	 */
	private function check_price( Product $sf_product ): array {
		$price = $sf_product->get_price();
		$pass  = ! empty( $price );

		return [
			'key'    => 'price',
			'label'  => __( 'Price', 'shopping-feed' ),
			'pass'   => $pass,
			'detail' => $pass
				? sprintf(
					/* translators: %s: product price */
					__( 'Price is set (%s).', 'shopping-feed' ),
					(string) $price
				)
				: __( 'Price is empty; product would be skipped by the feed generator filter.', 'shopping-feed' ),
		];
	}

	/**
	 * List variations with the same inclusion rules as Product::get_variations( true ).
	 *
	 * @return list<array{id: int, sku: string, included: bool, reason: string}>
	 */
	private function diagnose_variations( Product $sf_product ): array {
		$wc_product = $sf_product->get_wc_product();
		if ( ! $wc_product instanceof \WC_Product ) {
			return [];
		}

		$variable                     = new \WC_Product_Variable( $wc_product->get_id() );
		$show_out_of_stock_variations = ShoppingFeedHelper::show_out_of_stock_products_in_feed();
		$rows                         = [];

		foreach ( $variable->get_children() as $variation_id ) {
			$variation = wc_get_product( $variation_id );
			$row       = [
				'id'       => (int) $variation_id,
				'sku'      => '',
				'included' => false,
				'reason'   => '',
			];

			if ( ! $variation || ! $variation->exists() ) {
				$row['reason'] = __( 'Variation does not exist.', 'shopping-feed' );
				$rows[]        = $row;
				continue;
			}

			$row['sku'] = (string) $variation->get_sku();

			if ( ! $show_out_of_stock_variations && ! $variation->is_in_stock() ) {
				$row['reason'] = __( 'Out of stock.', 'shopping-feed' );
				$rows[]        = $row;
				continue;
			}

			if ( apply_filters( 'woocommerce_hide_invisible_variations', true, $variation->get_id(), $variation ) && ! $variation->variation_is_visible() ) {
				$row['reason'] = __( 'Invisible (disabled or empty price).', 'shopping-feed' );
				$rows[]        = $row;
				continue;
			}

			$row['included'] = true;
			$row['reason']   = __( 'Included in feed.', 'shopping-feed' );
			$rows[]          = $row;
		}

		return $rows;
	}
}
