<?php

namespace ShoppingFeed\ShoppingFeedWC\Feed;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

use Exception;
use ShoppingFeed\Feed\ProductGenerator;
use ShoppingFeed\ShoppingFeedWC\Products\Product;
use ShoppingFeed\ShoppingFeedWC\Products\Products;
use ShoppingFeed\ShoppingFeedWC\ShoppingFeedHelper;
use WP_Error;

/**
 * @psalm-consistent-constructor
 */
class Generator {

	/**
	 * Option name to save last generation date
	 */
	const SF_FEED_LAST_GENERATION_DATE = 'SF_FEED_LAST_GENERATION';

	public static function make( string $uri ): ProductGenerator {
		$generator = new ProductGenerator( $uri );
		$generator = self::set_platform( $generator );
		$generator = self::set_processors( $generator );
		$generator = self::set_filters( $generator );
		$generator = self::set_mappers( $generator );

		return $generator;
	}

	/**
	 * Set the platform
	 */
	protected static function set_platform( ProductGenerator $generator ): ProductGenerator {
		$generator->setPlatform(
			'WooCommerce',
			sprintf( '%s-module:%s', ShoppingFeedHelper::get_wc_version(), SF_VERSION )
		);

		return $generator;
	}

	/**
	 * In some case, you may need to pre-process data before to map them.
	 * This can be achieved in mappers or in your dataset, but sometimes things have to be separated, so you can register processors that are executed before mappers, and prepare your data before the mapping process.
	 */
	protected static function set_processors( ProductGenerator $generator ): ProductGenerator {
		return $generator;
	}

	/**
	 * Filters are designed discard some items from the feed.
	 * Filters are executed after processors, because item must be completely filled before to make the decision to keep it or not.
	 */
	protected static function set_filters( ProductGenerator $generator ): ProductGenerator {
		# Ignore all items with undefined price
		$generator->addFilter(
			function (
				array $sf_product
			) {
				$sf_product = reset( $sf_product );

				/** @var Product $sf_product */
				return ! empty( $sf_product->get_price() );
			}
		);

		return $generator;
	}

	/**
	 * As stated above, at least one mapper must be registered, this is where you populate the Product instance, which is later converted to XML by the library
	 */
	protected static function set_mappers( ProductGenerator $generator ): ProductGenerator {
		//Simple product mapping
		$generator->addMapper(
			function (
				array $sf_product, \ShoppingFeed\Feed\Product\Product $product
			) {
				$sf_product = reset( $sf_product );
				/** @var Product $sf_product */
				$product->setReference( $sf_product->get_sku() );
				$product->setName( $sf_product->get_name() );
				$product->setPrice( $sf_product->get_price() );

				if ( ! empty( $sf_product->get_ean() ) ) {
					$product->setGtin( $sf_product->get_ean() );
				}

				$product->setQuantity( $sf_product->get_quantity() );

				if ( ! empty( $sf_product->get_link() ) ) {
					$product->setLink( $sf_product->get_link() );
				}
				if ( $sf_product->is_on_sale() && ! empty( $sf_product->get_discount() ) ) {
					$product->addDiscount( $sf_product->get_discount() );
				}
				if ( ! empty( $sf_product->get_image_main() ) ) {
					$product->setMainImage( $sf_product->get_image_main() );
				}

				if ( ! empty( $sf_product->get_full_description() ) || ! empty( $sf_product->get_short_description() ) ) {
					$product->setDescription( $sf_product->get_full_description(), $sf_product->get_short_description() );
				}

				if ( ! empty( $sf_product->get_brand_name() ) ) {
					$product->setBrand( $sf_product->get_brand_name(), $sf_product->get_brand_link() );
				}

				if ( ! empty( $sf_product->get_weight() ) ) {
					$product->setWeight( (float) $sf_product->get_weight() );
				}

				if ( ! empty( $sf_product->get_length() ) ) {
					$product->setAttribute( 'length', (string) $sf_product->get_length() );
				}

				if ( ! empty( $sf_product->get_width() ) ) {
					$product->setAttribute( 'width', (string) $sf_product->get_width() );
				}

				if ( ! empty( $sf_product->get_height() ) ) {
					$product->setAttribute( 'height', (string) $sf_product->get_height() );
				}

				if ( ! empty( $sf_product->get_category_name() ) ) {
					$product->setCategory( $sf_product->get_category_name(), $sf_product->get_category_link() );
				}

				if ( ! empty( $sf_product->get_attributes() ) ) {
					$product->setAttributes( $sf_product->get_attributes() );
				}

				if ( ! empty( $sf_product->get_shipping_methods() ) ) {
					foreach ( $sf_product->get_shipping_methods() as $shipping_method ) {
						$product->addShipping( $shipping_method['cost'], $shipping_method['description'] );
					}
				}

				$images = $sf_product->get_images();
				if ( ! empty( $images ) ) {
					$product->setAdditionalImages( $sf_product->get_images() );
				}

				$extra_fields = $sf_product->get_extra_fields();
				if ( ! empty( $extra_fields ) ) {
					foreach ( $extra_fields as $field ) {
						if ( empty( $field['name'] ) ) {
							continue;
						}
						$product->setAttribute( $field['name'], $field['value'] );
					}
				}

			}
		);

		//Product with variations mapping
		$generator->addMapper(
			function (
				array $sf_product, \ShoppingFeed\Feed\Product\Product $product
			) {
				$sf_product = reset( $sf_product );
				/** @var Product $sf_product */

				$sf_product_variations = $sf_product->get_variations( true );

				if ( empty( $sf_product_variations ) ) {
					return;
				}
				foreach ( $sf_product_variations as $sf_product_variation ) {
					$variation = $product->createVariation();

					$variation
						->setReference( $sf_product_variation['sku'] )
						->setPrice( $sf_product_variation['price'] )
						->setQuantity( $sf_product_variation['quantity'] )
						->setGtin( $sf_product_variation['ean'] );

					if ( ! empty( $sf_product_variation['attributes'] ) ) {
						$variation
							->setAttributes( $sf_product_variation['attributes'] );
					}
					if ( ! empty( $sf_product_variation['discount'] ) ) {
						$variation
							->addDiscount( $sf_product_variation['discount'] );
					}
					if ( ! empty( $sf_product_variation['image_main'] ) ) {
						$variation
							->setMainImage( $sf_product_variation['image_main'] );
					}
					$variation_images = $sf_product->get_variation_images( $sf_product_variation['id'] );
					if ( ! empty( $variation_images ) ) {
						$variation->setAdditionalImages( $variation_images );
					}
					if ( ! empty( $sf_product_variation['width'] ) ) {
						$variation->setAttribute( 'width', (string) $sf_product_variation['width'] );
					}
					if ( ! empty( $sf_product_variation['length'] ) ) {
						$variation->setAttribute( 'length', (string) $sf_product_variation['length'] );
					}
					if ( ! empty( $sf_product_variation['height'] ) ) {
						$variation->setAttribute( 'height', (string) $sf_product_variation['height'] );
					}
				}
			}
		);

		return $generator;
	}
}
