<?php

namespace ShoppingFeed\ShoppingFeedWC\Addons\Plugins\YoastSeoPlugin;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

/**
 * Export Yoast SEO title and meta description in the product feed.
 */
class YoastMetas {

	public function __construct() {
		if ( ! $this->is_yoast_available() ) {
			return;
		}

		add_filter( 'shopping_feed_extra_fields', array( $this, 'add_yoast_metas' ), 10, 2 );
	}

	/**
	 * @return bool
	 */
	private function is_yoast_available(): bool {
		if ( defined( 'WPSEO_VERSION' ) ) {
			return true;
		}

		return class_exists( '\WPSEO_Meta' ) && class_exists( '\WPSEO_Replace_Vars' );
	}

	/**
	 * @param array       $fields   Extra feed fields.
	 * @param \WC_Product $product  WooCommerce product.
	 *
	 * @return array
	 */
	public function add_yoast_metas( $fields, $product ) {
		if ( ! $product instanceof \WC_Product ) {
			return $fields;
		}

		$post = get_post( $product->get_id() );
		if ( ! $post instanceof \WP_Post ) {
			return $fields;
		}

		$replace_vars = new \WPSEO_Replace_Vars();

		$fields[] = array(
			'name'  => 'meta-title',
			'value' => $replace_vars->replace( \WPSEO_Meta::get_value( 'title', $post->ID ), $post ),
		);
		$fields[] = array(
			'name'  => 'meta-description',
			'value' => $replace_vars->replace( \WPSEO_Meta::get_value( 'metadesc', $post->ID ), $post ),
		);

		return $fields;
	}
}
