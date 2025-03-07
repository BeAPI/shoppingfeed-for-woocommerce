<?php

namespace ShoppingFeed\ShoppingFeedWC\Feed\FeedBuilder;

use ShoppingFeed\Feed\ProductFeedMetadata;
use ShoppingFeed\Feed\ProductGenerator;
use ShoppingFeed\ShoppingFeedWC\Feed\Generator;
use ShoppingFeed\ShoppingFeedWC\Products\Products;
use ShoppingFeed\ShoppingFeedWC\ShoppingFeedHelper;

abstract class FeedBuilder extends Generator {

	/**
	 * Check if the builder is available.
	 *
	 * @return bool
	 */
	abstract public function is_available(): bool;

	/**
	 * Launch products' feed generation.
	 *
	 * @return bool|\WP_Error
	 */
	abstract public function generate_feed( $lang = null );

	/**
	 * Launch async products' feed generation.
	 *
	 * @return void
	 */
	abstract public function launch_feed_generation( int $page_size ): void;

	/**
	 * List all products' feeds URLs.
	 *
	 * @return array
	 */
	abstract public function get_feed_urls(): array;

	/**
	 * Render a feed.
	 *
	 * @return void
	 */
	abstract public function render_feed( $lang = null ): void;

	/**
	 * @param string $file_path
	 * @param array $products
	 *
	 * @return bool|\WP_Error
	 */
	protected function write_products_feed( string $file_path, array $products ) {
		$products_list = Products::get_instance()->format_products( $products );
		try {
			$this->generator = new ProductGenerator();
			$this->generator->setPlatform(
				'WooCommerce',
				sprintf( '%s-module:%s', ShoppingFeedHelper::get_wc_version(), SF_VERSION )
			);
			$this->generator->setUri( $file_path );
			$this->set_filters();
			$this->set_mappers();
			$this->generator->write( $products_list );
		} catch ( \Exception $exception ) {
			return new \WP_Error( 'shopping_feed_generation_error', $exception->getMessage() );
		}

		return true;
	}

	/**
	 * @param array $files
	 * @param string $tmp_file_path
	 * @param string $final_file_path
	 *
	 * @return bool|\WP_Error
	 */
	protected function combine_and_write_product_feed( array $files, string $tmp_file_path, string $final_file_path ) {
		$writer = new \XMLWriter();
		$writer->openUri( $tmp_file_path );
		$writer->setIndent( true );

		$writer->startDocument( '1.0', 'utf-8' );
		$writer->startElement( 'catalog' );
		$writer->startElement( 'products' );

		foreach ( $files as $file ) {
			$doc = new \DOMDocument();
			if ( ! $doc->load( $file ) ) {
				unset( $doc, $writer );

				return new \WP_Error(
					'shopping_feed_combine_error',
					sprintf(
						'Unable to load XML file: %s',
						str_replace( wp_normalize_path( WP_CONTENT_DIR ), '', wp_normalize_path( $file ) )
					)
				);
			}

			$xpath = new \DOMXpath( $doc );
			foreach ( $xpath->query( '//catalog/products/*' ) as $p ) {
				$writer->writeRaw( $doc->saveXML( $p ) );
			}

			unset( $doc, $xpath );
			unlink( $file );
		}

		$writer->endElement(); // products

		$metadata = new ProductFeedMetadata();
		$metadata->setPlatform(
			'WooCommerce',
			sprintf( '%s-module:%s', ShoppingFeedHelper::get_wc_version(), SF_VERSION )
		);
		$writer->startElement( 'metadata' );
		$writer->writeElement( 'platform', $metadata->getPlatform() );
		$writer->writeElement( 'agent', $metadata->getAgent() );
		$writer->writeElement( 'module', $metadata->getModule() );
		$writer->endElement(); // metadata
		$writer->endElement(); // catalog

		$writer->flush();

		// unlink object reference
		unset( $writer );

		if ( ! rename( $tmp_file_path, $final_file_path ) ) {
			unlink( $tmp_file_path );

			return new \WP_Error(
				'shopping_feed_combine_error',
				'Failed to replace the products feed file.'
			);
		}

		update_option( self::SF_FEED_LAST_GENERATION_DATE, time() );

		return true;
	}
}
