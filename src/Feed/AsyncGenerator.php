<?php

namespace ShoppingFeed\ShoppingFeedWC\Feed;

use ShoppingFeed\Feed\ProductGenerator;
use ShoppingFeed\ShoppingFeedWC\Products\Products;
use ShoppingFeed\ShoppingFeedWC\ShoppingFeed;
use ShoppingFeed\ShoppingFeedWC\ShoppingFeedHelper;

class AsyncGenerator extends Generator {

	/**
	 * Launch the feed generation process
	 */
	public function launch() {
		// Ensure all necessary directories have been created.
		ShoppingFeed::add_sf_directory();

		// Clean directory containing feed parts generated by the async generator.
		// This is to avoid reusing old parts that could remain if a previous execution of the generator failed.
		$this->clean_feed_parts_directory();

		$part_size           = ShoppingFeedHelper::get_sf_part_size();
		$available_languages = ShoppingFeedHelper::get_available_languages();
		if ( ! empty( $available_languages ) ) {
			foreach ( $available_languages as $language ) {
				as_schedule_single_action(
					time() + 10,
					'sf_feed_generation_part',
					array(
						1,
						$part_size,
						$language,
					),
					'sf_feed_generation_process'
				);
			}

			return;
		}

		as_schedule_single_action(
			time() + 10,
			'sf_feed_generation_part',
			array(
				1,
				$part_size
			),
			'sf_feed_generation_process'
		);
	}

	/**
	 * Generate feed part
	 *
	 * @param int $page
	 * @param int $post_per_page
	 * @param string $lang
	 *
	 * @return bool|\WP_Error
	 */
	public function generate_feed_part( $page = 1, $post_per_page = 20, $lang = '' ) {
		$args = array(
			'page'   => $page,
			'limit'  => $post_per_page,
			'return' => 'ids',
		);
		if ( ! empty( $lang ) ) {
			$args['lang'] = $lang;
			$current_language = apply_filters( 'wpml_current_language', null );
			do_action( 'wpml_switch_language', $lang );
		}
		$products = Products::get_instance()->get_products( $args );
		if ( ! empty( $lang ) ) {
			do_action( 'wpml_switch_language', $current_language );
		}

		// If the query doesn't return any products, schedule the combine action and stop the current action.
		if ( empty( $products ) ) {
			as_schedule_single_action(
				time(),
				'sf_feed_generation_combine_feed_parts',
				array( $lang ),
				'sf_feed_generation_process'
			);
			as_enqueue_async_action(
				'sf_feed_generation_combine_feed_parts',
				array( $lang ),
				'sf_feed_generation_process'
			);

			return true;
		}

		// Process products returned by the query and reschedule the action for the next page.
		$path          = sprintf(
			'file://%s/%s.xml',
			ShoppingFeedHelper::get_feed_parts_directory(),
			! empty( $lang ) ? 'part_' . $page . '_' . $lang : 'part_' . $page
		);
		$products_list = Products::get_instance()->format_products( $products );
		try {
			$this->generator = new ProductGenerator();
			$this->generator->setPlatform( (string) $page, (string) $page );
			$this->generator->setUri( $path );
			$this->set_filters();
			$this->set_mappers();
			$this->generator->write( $products_list );

			$page ++;
			as_schedule_single_action(
				time() + 5,
				'sf_feed_generation_part',
				array(
					$page,
					$post_per_page,
					$lang,
				),
				'sf_feed_generation_process'
			);
		} catch ( \Exception $exception ) {
			return new \WP_Error( 'shopping_feed_generation_error', $exception->getMessage() );
		}

		return true;
	}

	/**
	 * Combine the generated parts to a unique and final file
	 *
	 * @param string $lang
	 */
	public function combine_feed_parts( $lang = '' ) {
		$option = get_option( 'sf_feed_generation_process' );

		$dir       = ShoppingFeedHelper::get_feed_directory();
		$dir_parts = ShoppingFeedHelper::get_feed_parts_directory();

		$pattern = ! empty( $lang ) ? sprintf( '%s/*_%s.xml', $dir_parts, $lang ) : $dir_parts . '/*.xml';
		$files   = glob( $pattern ); // @codingStandardsIgnoreLine.

		$xml_content      = '<products>';
		$xml_invalid      = 0;
		$xml_ignored      = 0;
		$xml_written      = 0;
		$last_started_at  = '';
		$last_finished_at = '';
		foreach ( $files as $file ) {
			$file_xml         = simplexml_load_string( file_get_contents( $file ) );
			$last_started_at  = $file_xml->metadata->startedAt;
			$last_finished_at = $file_xml->metadata->finishedAt;
			$xml_invalid      += (int) $file_xml->metadata->invalid;
			$xml_ignored      += (int) $file_xml->metadata->ignored;
			$xml_written      += (int) $file_xml->metadata->written;
			$products         = $file_xml->products[0];
			foreach ( $products as $product ) {
				$xml_content .= $product->asXML();
			}
			wp_delete_file( $file );
		}
		$xml_content .= '</products>';
		$xml_content = simplexml_load_string( $xml_content );

		/**
		 * Save products tag to a temporary file
		 * Read and get the xml content from the file and remove the xml header
		 * Delete the temporary file
		 */
		$tmp_file_path = ! empty( $lang ) ? sprintf( '%s/products_%s_tmp.xml', $dir, $lang ) : $dir . '/products_tmp.xml';
		if ( ! file_put_contents( $tmp_file_path, $xml_content->asXML() ) ) {
			ShoppingFeedHelper::get_logger()->error(
				sprintf(
				/* translators: %s: Action name. */
					__( 'Cant read create temporary file (products_tmp.xml) : %s', 'shopping-feed' ),
					$tmp_file_path
				),
				array(
					'source' => 'shopping-feed',
				)
			);

			return;
		}
		$products = preg_replace( '/<\?xml version="1.0"\?>\n/', '', file_get_contents( $tmp_file_path ) );
		wp_delete_file( $tmp_file_path );

		$skelton = simplexml_load_string( ShoppingFeedHelper::get_feed_skeleton() );
		$this->simplexml_import_xml( $skelton->metadata, $products, true );
		$skelton->metadata->platform   = sprintf( 'WooCommerce:%s-module:%s', ShoppingFeedHelper::get_wc_version(), SF_VERSION );
		$skelton->metadata->startedAt  = $last_started_at;
		$skelton->metadata->finishedAt = $last_finished_at;
		$skelton->metadata->invalid    = $xml_invalid;
		$skelton->metadata->ignored    = $xml_ignored;
		$skelton->metadata->written    = $xml_written;
		$uri                           = Uri::get_instance()->get_file_path();
		if ( ! empty( $lang ) ) {
			$uri .= '_' . $lang;
		}
		$uri .= '.xml';
		if ( ! file_put_contents( $uri, $skelton->asXML() ) ) {
			ShoppingFeedHelper::get_logger()->error(
				sprintf(
				/* translators: %s: Action name. */
					__( 'Cant read create xml file (products.xml) : %s', 'shopping-feed' ),
					$uri
				),
				array(
					'source' => 'shopping-feed',
				)
			);

			return;
		}
		$option['file'] = $uri;
		update_option( 'sf_feed_generation_process', $option );
		update_option( self::SF_FEED_LAST_GENERATION_DATE, date_i18n( 'd/m/Y H:i:m' ) );
	}

	/**
	 * Insert XML into a SimpleXMLElement
	 *
	 * @param \SimpleXMLElement $parent
	 * @param string $xml
	 * @param bool $before
	 *
	 * @return bool XML string added
	 * @see https://gist.github.com/hakre/4761677
	 * @psalm-suppress all
	 */
	public function simplexml_import_xml( \SimpleXMLElement $parent, $xml, $before = false ) {
		$xml = (string) $xml;
		// @codingStandardsIgnoreStart
		// check if there is something to add
		if ( null === $parent[0] || $nodata = ! strlen( $xml ) ) {
			return $nodata;
		}

		$node     = dom_import_simplexml( $parent );
		$fragment = $node->ownerDocument->createDocumentFragment();
		$fragment->appendXML( $xml );

		if ( $before ) {
			return (bool) $node->parentNode->insertBefore( $fragment, $node );
		}

		return (bool) $node->appendChild( $fragment );
		// @codingStandardsIgnoreEnd
	}

	/**
	 * Remove all XML files in the feed parts directory.
	 *
	 * @return void
	 */
	public function clean_feed_parts_directory() {
		$dir_parts = ShoppingFeedHelper::get_feed_parts_directory();
		$files     = glob( $dir_parts . '/' . '*.xml' ); // @codingStandardsIgnoreLine.
		if ( is_array( $files ) && ! empty( $files ) ) {
			foreach ( $files as $file ) {
				wp_delete_file( $file );
			}
		}
	}
}
