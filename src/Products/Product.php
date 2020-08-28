<?php

namespace ShoppingFeed\ShoppingFeedWC\Products;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

use ShoppingFeed\ShoppingFeedWC\ShoppingFeedHelper;

class Product {

	/**
	 * @var \WC_Product
	 */
	private $product;

	/**
	 * @var string
	 */
	private $product_identifier;

	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var bool|mixed|\WP_Term
	 */
	private $brand;

	/**
	 * @var bool|mixed|\WP_Term
	 */
	private $category;

	/**
	 * Product constructor.
	 *
	 * @param $product
	 */
	public function __construct( $product ) {
		$this->product            = wc_get_product( $product );
		$this->product_identifier = ShoppingFeedHelper::get_sf_feed_product_identifier();
		$this->id                 = $this->product->get_id();
		$this->brand              = $this->set_brand();
		$this->category           = $this->set_category();

		return $this;
	}

	/**
	 * @return bool|mixed|\WP_Term
	 */
	private function set_brand() {
		$brand_taxonomy = ShoppingFeedHelper::wc_brand_taxonomy();

		if ( empty( $brand_taxonomy ) ) {
			return false;
		}

		$terms = get_the_terms( $this->id, $brand_taxonomy );

		if ( empty( $terms ) ) {
			return false;
		}

		return reset( $terms );
	}

	/**
	 * @return bool|mixed|\WP_Term
	 */
	private function set_category() {
		$terms = get_the_terms( $this->id, ShoppingFeedHelper::wc_category_taxonomy() );

		if ( empty( $terms ) ) {
			return false;
		}

		return reset( $terms );
	}

	/**
	 * @return string|int
	 */
	public function get_sku() {
		if ( 'id' === $this->product_identifier ) {
			return $this->product->get_id();
		}

		return $this->product->get_sku();
	}

	/**
	 * @return string
	 */
	public function get_name() {
		return $this->product->get_name();
	}

	/**
	 * @return int
	 */
	public function get_quantity() {
		return ! is_null( $this->product->get_stock_quantity() ) ? $this->product->get_stock_quantity() : ShoppingFeedHelper::get_default_product_quantity();
	}

	/**
	 * @return string
	 */
	public function get_price() {
		return $this->product->get_regular_price() ? $this->product->get_regular_price() : $this->product->get_price();
	}

	/**
	 * @return string
	 */
	public function get_discount() {
		return $this->product->get_sale_price();
	}

	/**
	 * @return string
	 */
	public function get_link() {
		return $this->product->get_permalink();
	}

	/**
	 * @return string
	 */
	public function get_full_description() {
		return $this->product->get_description();
	}

	/**
	 * @return string
	 */
	public function get_short_description() {
		return $this->product->get_short_description();
	}

	/**
	 * @return string
	 */
	public function get_brand_name() {
		if ( empty( $this->brand ) ) {
			return '';
		}

		return $this->brand->name;
	}

	/**
	 * @return string
	 */
	public function get_brand_link() {
		if ( empty( $this->brand ) ) {
			return '';
		}

		return get_term_link( $this->brand );
	}

	/**
	 * @return string
	 */
	public function get_category_name() {
		if ( empty( $this->category ) ) {
			return '';
		}

		return $this->category->name;
	}

	/**
	 * @return string
	 */
	public function get_category_link() {
		if ( empty( $this->category ) ) {
			return '';
		}

		return get_term_link( $this->category );
	}

	/**
	 * @return array
	 */
	public function get_shipping_methods() {

		$sf_default_shipping_zone = ShoppingFeedHelper::get_sf_default_shipping_zone();
		$sf_default_shipping_fees = ShoppingFeedHelper::get_sf_default_shipping_fees();

		if ( ! $sf_default_shipping_fees && ! $sf_default_shipping_zone ) {
			return array();
		}

		if ( empty( $sf_default_shipping_zone ) ) {
			return array(
				array(
					'cost'        => $sf_default_shipping_fees,
					'description' => 'Default',
				),
			);
		}

		$zone                  = \WC_Shipping_Zones::get_zone( $sf_default_shipping_zone );
		$zone_shipping_methods = $zone->get_shipping_methods( true );

		if ( empty( $zone_shipping_methods ) ) {
			return array(
				array(
					'cost'        => $sf_default_shipping_fees,
					'description' => 'Default',
				),
			);
		}

		$shipping_methods = array();
		foreach ( $zone_shipping_methods as $method ) {
			/** @var \WC_Shipping_Flat_Rate $method */
			$cost               = isset( $method->instance_settings['cost'] ) ? $method->instance_settings['cost'] : 0;
			$shipping_methods[] = array(
				'cost'        => $cost,
				'description' => $method->get_title(),
			);
		}

		return $shipping_methods;
	}

	/**
	 * @return array
	 */
	public function get_attributes() {

		$wc_attributes = $this->product->get_attributes();

		if ( empty( $wc_attributes ) ) {
			return array();
		}

		$attributes = array();
		foreach ( $wc_attributes as $taxonomy => $attribute_obj ) {
			$attribute = reset( $attribute_obj );

			if ( empty( $attribute ) || empty( $attribute['options'] ) ) {
				continue;
			}

			$attribute_names = array();
			foreach ( $attribute['options'] as $option ) {
				$attribute_names[] = term_exists( $option ) ? get_term( $option )->name : $option;
			}

			$attributes [ wc_attribute_label( $taxonomy ) ] = implode( ',', $attribute_names );
		}

		return $attributes;
	}

	/**
	 * @return string
	 */
	public function get_image_main() {
		return ! empty( get_the_post_thumbnail_url( $this->id, 'full' ) ) ? get_the_post_thumbnail_url( $this->id, 'full' ) : '';
	}

	/**
	 * @return array
	 */
	public function get_images() {
		return array_map(
			function ( $img ) {
				return wp_get_attachment_image_url( $img, 'full' );
			},
			$this->product->get_gallery_image_ids()
		);
	}

	public function has_variations() {
		return ! empty( $this->get_variations() );
	}

	/**
	 * @return array
	 */
	public function get_variations() {
		if ( 'variable' === \WC_Product_Factory::get_product_type( $this->id ) ) {
			$product               = new \WC_Product_Variable( $this->id );
			$wc_product_variations = $product->get_available_variations();
			if ( empty( $wc_product_variations ) ) {
				return array();
			}

			$variations = array();
			foreach ( $wc_product_variations as $wc_product_variation ) {
				$wc_product_variation  = new \WC_Product_Variation( $wc_product_variation['variation_id'] );
				$variation             = array();
				$variation['sku']      = ( 'id' === $this->product_identifier ) ? $wc_product_variation->get_id() : $wc_product_variation->get_sku();
				$variation['ean']      = $this->get_ean( $wc_product_variation );
				$variation['quantity'] = ! is_null( $wc_product_variation->get_stock_quantity() ) ? $wc_product_variation->get_stock_quantity() : ShoppingFeedHelper::get_default_product_quantity();
				$variation['price']    = ! is_null( $wc_product_variation->get_regular_price() ) ? $wc_product_variation->get_regular_price() : $wc_product_variation->get_price();
				$variation['discount'] = $wc_product_variation->get_sale_price();
				if ( ! empty( get_the_post_thumbnail_url( $wc_product_variation->get_id(), 'full' ) ) ) {
					$variation['image_main'] = get_the_post_thumbnail_url( $wc_product_variation->get_id(), 'full' );
				}

				$variation['attributes'] = $this->get_variation_attributes( $wc_product_variation );
				$variations []           = $variation;
			}

			return $variations;
		}

		return array();
	}

	/**
	 * @return string
	 */
	public function get_ean( $wc_product = false ) {
		$ean_meta_key = ShoppingFeedHelper::wc_product_ean();

		if ( empty( $ean_meta_key ) ) {
			return '';
		}

		if ( ! empty( $wc_product ) ) {
			return $wc_product->get_meta( $ean_meta_key ) ? $wc_product->get_meta( $ean_meta_key ) : '';
		}

		return $this->product->get_meta( $ean_meta_key ) ? $this->product->get_meta( $ean_meta_key ) : '';
	}

	/**
	 * Get Variation Attributes
	 *
	 * @param $variation
	 *
	 * @return array
	 */
	public function get_variation_attributes( $variation ) {
		$attribute_names = array();
		foreach ( $variation->get_attributes() as $attribute => $value ) {
			$attribute_names[ wc_attribute_label( $attribute ) ] = $variation->get_attribute( $attribute );
		}

		return $attribute_names;
	}
}
