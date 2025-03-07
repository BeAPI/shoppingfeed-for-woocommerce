<?php

namespace ShoppingFeed\ShoppingFeedWC\Feed\FeedBuilder;

use ShoppingFeed\Feed\ProductFeedMetadata;
use ShoppingFeed\ShoppingFeedWC\Feed\Generator;
use ShoppingFeed\ShoppingFeedWC\Products\Products;
use ShoppingFeed\ShoppingFeedWC\ShoppingFeedHelper;

abstract class FeedBuilder {

	/**
	 * Get path for the products' feeds folder.
	 *
	 * @return string
	 */
	public static function get_feed_folder_path(): string {
		return ShoppingFeedHelper::get_feed_directory();
	}

	/**
	 * Get path for a products' feed file.
	 *
	 * @param string|null $lang
	 * @param bool $include_prefix
	 * @param bool $tmp
	 *
	 * @return string
	 */
	public static function get_feed_file_path( string $lang = null, bool $include_prefix = true, bool $tmp = false ): string {
		$dir  = self::get_feed_folder_path();
		$file = ShoppingFeedHelper::get_feed_filename();

		// maybe add language code in filename.
		if ( null !== $lang ) {
			$file .= '_' . $lang;
		}

		// maybe add temp suffix in filename.
		if ( $tmp ) {
			$file .= '_tmp';
		}

		$file_path = $dir . '/' . $file . '.xml';

		return $include_prefix ? 'file://' . $file_path : $file_path;
	}

	/**
	 * Get path for the products' feeds parts.
	 *
	 * @param string|null $lang
	 *
	 * @return string
	 */
	public static function get_feed_parts_folder_path( string $lang = null ): string {
		$dir = ShoppingFeedHelper::get_feed_parts_directory();
		if ( null !== $lang ) {
			$dir .= '/' . $lang;
		}

		return $dir;
	}

	/**
	 * Get path for a products' feed part file.
	 *
	 * @param string|null $lang
	 * @param int $page
	 * @param bool $include_prefix
	 *
	 * @return string
	 */
	public static function get_feed_parts_file_path( string $lang = null, int $page = 1, bool $include_prefix = true ): string {
		$dir       = self::get_feed_parts_folder_path( $lang );
		$file      = sprintf( 'part_%s', zeroise( $page, 2 ) );
		$file_path = $dir . '/' . $file . '.xml';

		return $include_prefix ? 'file://' . $file_path : $file_path;
	}

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
	public function render_feed( $lang = null ): void {
		$file_path = self::get_feed_file_path( $lang, false );

		if ( ! is_file( $file_path ) ) {
			if ( ShoppingFeedHelper::is_process_running( 'sf_feed_generation_process' ) ) {
				wp_die( 'Feed generation already launched' );
			}

			ShoppingFeedHelper::get_feedbuilder_manager()->launch_feed_generation( ShoppingFeedHelper::get_sf_part_size() );
			wp_die( 'Feed generation launched' );
		}

		header( 'Content-Type: application/xml; charset=utf-8' );
		header( 'Content-Length: ' . filesize( $file_path ) );
		nocache_headers();
		readfile( $file_path );
		exit;
	}

	/**
	 * @param string $file_path
	 * @param array $products
	 *
	 * @return bool|\WP_Error
	 */
	protected function write_products_feed( string $file_path, array $products ) {
		$products_list = Products::get_instance()->format_products( $products );
		try {
			$generator = Generator::make( $file_path );
			$generator->write( $products_list );
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

		update_option( Generator::SF_FEED_LAST_GENERATION_DATE, time() );

		return true;
	}

	protected function clean_feed_parts_directory( string $lang = null ): void {
		$dir_parts = self::get_feed_parts_folder_path( $lang );
		$files     = glob( $dir_parts . '/*.xml' ); // @codingStandardsIgnoreLine.
		if ( is_array( $files ) && ! empty( $files ) ) {
			foreach ( $files as $file ) {
				unlink( $file );
			}
		}
	}
}
