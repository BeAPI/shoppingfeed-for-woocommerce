<?php

namespace ShoppingFeed\ShoppingFeedWC\Products;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

use ShoppingFeed\ShoppingFeedWC\ShoppingFeedHelper;
use WP_Query;

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
	 * @var string
	 */
	private $weight;	/**
 * @var string
 */

	private $length;	/**
 * @var string
 */
	private $width;	/**
 * @var string
 */
	private $height;

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
		$this->weight             = $this->product->get_weight();
		$this->length             = $this->product->get_length();
		$this->width              = $this->product->get_width();
		$this->height             = $this->product->get_height();
	}

	/**
	 * Return WC Product
	 * @return false|\WC_Product|null
	 */
	public function get_wc_product() {
		return $this->product;
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

		$return           = '';
		$taxonomy_name    = ShoppingFeedHelper::wc_category_taxonomy();
		$sf_yoast_options = ShoppingFeedHelper::get_sf_yoast_options();

		if ( class_exists( \WPSEO_Primary_Term::class ) && 1 === (int) $sf_yoast_options['use_principal_categories'] ) {
			// Show Primary category by Yoast if it is enabled & set
			$wpseo_primary_term = new \WPSEO_Primary_Term( $taxonomy_name, $this->id );
			$primary_term       = get_term( $wpseo_primary_term->get_primary_term() );
			if ( ! is_wp_error( $primary_term ) ) {
				$return = $primary_term;
			}
		}

		if ( empty( $return ) ) {
			$categories_list = get_the_terms( $this->id, $taxonomy_name );
			if ( ! empty( $categories_list ) && ! is_wp_error( $categories_list ) ) {
				$return = $categories_list[0];
			}
		}

		return empty( $return ) ? false : $return;
	}

	/**
	 * @return string
	 */
	public function get_sku() {
		if ( 'id' === $this->product_identifier ) {
			return (string) $this->product->get_id();
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
		if ( $this->product instanceof \WC_Product_Variable ) {
			$quantity = 0;
			if ( $this->product->has_child() ) {
				foreach ( $this->product->get_children() as $child ) {
					$wc_product_variation = wc_get_product( $child );
					if ( ! $wc_product_variation ) {
						continue;
					}

					$quantity += $this->_get_quantity( $wc_product_variation );
				}
			}

			return $quantity;
		}

		return $this->_get_quantity( $this->product );
	}

	/**
	 * @return float
	 */
	public function get_price() {
		return (float) $this->product->get_regular_price() ? $this->product->get_regular_price() : $this->product->get_price();
	}

	/**
	 * @return float
	 */
	public function get_discount() {
		return (float) $this->product->get_sale_price();
	}

	/**
	 * @return bool
	 */
	public function is_on_sale() {
		return (bool) $this->product->is_on_sale();
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
	public function get_weight() {
		if ( empty( $this->weight ) ) {
			return '';
		}

		return $this->weight;
	}

	/**
	 * @return string
	 */
	public function get_length() {
		if ( empty( $this->length ) ) {
			return '';
		}

		return $this->length;
	}

	/**
	 * @return string
	 */
	public function get_width() {
		if ( empty( $this->width ) ) {
			return '';
		}

		return $this->width;
	}

	/**
	 * @return string
	 */
	public function get_height() {
		if ( empty( $this->height ) ) {
			return '';
		}

		return $this->height;
	}

	/**
	 * @return string
	 */
	public function get_category_name() {
		if ( empty( $this->category ) ) {
			return '';
		}

		$category_display_mode = ShoppingFeedHelper::get_sf_feed_category_display_mode();
		if ( 'normal' === $category_display_mode ) {
			return $this->category->name;
		}

		return $this->get_category_hierarchy();
	}

	/**
	 * @return string
	 */
	public function get_category_hierarchy() {

		$taxonomy   = ShoppingFeedHelper::wc_category_taxonomy();
		$parents    = get_ancestors( $this->category->term_id, $taxonomy, 'taxonomy' );
		$count      = count( $parents );
		$breadcrumb = '';
		array_unshift( $parents, $this->category->term_id );
		foreach ( array_reverse( $parents ) as $key => $term_id ) {
			$parent     = get_term( $term_id, $taxonomy );
			$name       = $parent->name;
			$breadcrumb .= $name;
			if ( $key < $count ) {
				$breadcrumb .= ' > ';
			}
		}

		return $breadcrumb;
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

		$attributes = array();
		if ( ! empty( $wc_attributes ) ) {
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
		}

		return apply_filters( 'shopping_feed_extra_attributes', $attributes, $this->product );
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

	/**
	 * @param int $variation_id
	 *
	 * @return array
	 */
	public function get_variation_images( $variation_id = 0 ) {
		return apply_filters( 'shopping_feed_variation_images', [], $this->product, $variation_id );
	}

	public function has_variations() {
		return ! empty( $this->get_variations() );
	}

	/**
	 * Get product's variations.
	 *
	 * @param bool $for_feed enable custom checks when loading variations to include in the products feed.
	 *
	 * @return array
	 */
	public function get_variations( $for_feed = false ) {
		if ( 'variable' !== \WC_Product_Factory::get_product_type( $this->id ) ) {
			return array();
		}

		$product                      = new \WC_Product_Variable( $this->id );
		$show_out_of_stock_variations = $for_feed && ShoppingFeedHelper::show_out_of_stock_products_in_feed();
		$variations                   = [];
		foreach ( $product->get_children() as $variation_id ) {
			$variation = wc_get_product( $variation_id );

			// Hide out of stock variations if '$show_out_of_stock_variations' is true.
			if ( ! $variation || ! $variation->exists() || ( ! $show_out_of_stock_variations && ! $variation->is_in_stock() ) ) {
				continue;
			}

			// Filter 'woocommerce_hide_invisible_variations' to optionally hide invisible variations (disabled variations and variations with empty price).
			if ( apply_filters( 'woocommerce_hide_invisible_variations', true, $variation->get_id(), $variation ) && ! $variation->variation_is_visible() ) {
				continue;
			}

			$variation_data             = [];
			$variation_data['id']       = $variation->get_id();
			$variation_data['sku']      = ( 'id' === $this->product_identifier ) ? $variation->get_id() : $variation->get_sku();
			$variation_data['ean']      = $this->get_ean( $variation );
			$variation_data['quantity'] = $this->_get_quantity( $variation );
			$variation_data['price']    = ! is_null( $variation->get_regular_price() ) ? $variation->get_regular_price() : $variation->get_price();
			$variation_data['discount'] = $variation->is_on_sale() ? $variation->get_sale_price() : 0;
			$variation_data['width']    = $variation->get_width();
			$variation_data['height']   = $variation->get_height();
			$variation_data['length']   = $variation->get_length();


			if ( ! empty( get_the_post_thumbnail_url( $variation->get_id(), 'full' ) ) ) {
				$variation_data['image_main'] = get_the_post_thumbnail_url( $variation->get_id(), 'full' );
			}


			$variation_data['attributes'] = $this->get_variation_attributes( $variation );
			$variations[]                 = $variation_data;
		}

		return $variations;
	}

	/**
	 * @return string
	 */
	public function get_ean( $wc_product = false ) {
		$ean_meta_key = ShoppingFeedHelper::wc_product_ean( $wc_product );

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
	 * @param \WC_Product_Variation $variation
	 *
	 * @return array
	 */
	public function get_variation_attributes( $variation ) {
		$attribute_names = array();

		$attributes = $variation->get_attributes();
		if ( ! empty( $attributes ) ) {
			foreach ( $attributes as $attribute => $value ) {
				$attribute_names[ wc_attribute_label( $attribute ) ] = $variation->get_attribute( $attribute );
			}
		}


		return apply_filters( 'shopping_feed_extra_variation_attributes', $attribute_names, $variation );
	}

	/**
	 * Get Extra fields
	 * Field : ['name'=>'', 'value'=>'']
	 */
	public function get_extra_fields() {
		return apply_filters( 'shopping_feed_extra_fields', [], $this->product );
	}

	/**
	 * Get product's stock quantity.
	 *
	 * @param \WC_Product $product
	 *
	 * @return int
	 */
	private function _get_quantity( $product ) {
		$quantity = 0;
		if ( $product->is_in_stock() ) {
			$quantity = $product->managing_stock() ? $product->get_stock_quantity() : ShoppingFeedHelper::get_default_product_quantity();
		}

		return $quantity;
	}
}
