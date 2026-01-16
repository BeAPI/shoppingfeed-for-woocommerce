<?php

namespace ShoppingFeed\ShoppingFeedWC\Feed\FeedBuilder;

use ShoppingFeed\ShoppingFeedWC\Products\Products;
use ShoppingFeed\ShoppingFeedWC\ShoppingFeedHelper;

class FeedBuilderWpml extends FeedBuilder {

	/**
	 * @inerhitDoc
	 */
	public function is_available(): bool {
		return defined( 'ICL_SITEPRESS_VERSION' ) && ! empty( $this->get_languages() );
	}

	/**
	 * Launch products' feed generation.
	 *
	 * @return bool|\WP_Error
	 */
	public function generate_feed( $lang = null ) {
		$available_languages = $this->get_languages();
		if ( null !== $lang && ! in_array( $lang, $available_languages, true ) ) {
			return new \WP_Error( 'sf_feed_generation_invalid_language', sprintf( 'Language "%s" is not available.', $lang ) );
		}

		foreach ( $available_languages as $language ) {
			if ( null !== $lang && $lang !== $language ) {
				continue;
			}

			$original_language = apply_filters( 'wpml_current_language', null );
			do_action( 'wpml_switch_language', $language );

			$products = Products::get_instance()->get_products( [], $lang );
			$this->write_products_feed(
				self::get_feed_file_path( $language ),
				$products
			);

			do_action( 'wpml_switch_language', $original_language );
		}

		return true;
	}

	/**
	 * @inerhitDoc
	 */
	public function launch_feed_generation( int $page_size ): void {
		foreach ( $this->get_languages() as $language ) {
			$this->clean_feed_parts_directory( $language );
			self::schedule_generation_part( $language, 1, $page_size );
		}
	}

	/**
	 * @inerhitDoc
	 */
	public function get_feed_urls(): array {
		$endpoint      = ShoppingFeedHelper::get_public_feed_endpoint();
		$use_permalink = ! empty( get_option( 'permalink_structure' ) );

		$urls = [];
		foreach ( $this->get_languages() as $language ) {
			$feed_endpoint = $endpoint . '-' . $language;
			$urls[]        = $use_permalink ? sprintf( '%s/%s/', get_home_url(), $feed_endpoint ) : sprintf( '%s?%s', get_home_url(), $feed_endpoint );
		}

		return $urls;
	}

	/**
	 * @inerhitDoc
	 */
	public function support_multilingual(): bool {
		return true;
	}

	/**
	 * @inerhitDoc
	 */
	public function get_languages(): array {
		$languages     = [];
		$icl_languages = apply_filters( 'wpml_active_languages', null );
		if ( is_array( $icl_languages ) ) {
			$languages = wp_list_pluck( $icl_languages, 'language_code' );
		}

		return $languages;
	}

	/**
	 * @inerhitDoc
	 */
	public function current_languages(): string {
		return wpml_get_current_language();
	}

	/**
	 * @param string $lang
	 * @param int $page
	 * @param int $post_per_page
	 * @param int $last_processed_id
	 *
	 * @return void
	 */
	public static function schedule_generation_part( string $lang, int $page = 1, int $post_per_page = 20, int $last_processed_id = 0 ): void {
		// $page can't be less than 1
		if ( $page < 1 ) {
			$page = 1;
		}

		// $last_processed_id can't be less than 0
		if ( $last_processed_id < 0 ) {
			$last_processed_id = 0;
		}

		// $post_per_page can't be less than 1
		if ( $post_per_page < 1 ) {
			$post_per_page = 20;
		}

		as_schedule_single_action(
			time() + 15,
			'sf_feed_generation_part_wpml',
			array(
				$lang,
				$page,
				$post_per_page,
				$last_processed_id,
			),
			'sf_feed_generation_process'
		);
	}

	/**
	 * @return void
	 */
	public static function schedule_combine_parts( string $lang ): void {
		as_schedule_single_action(
			time() + 15,
			'sf_feed_generation_combine_feed_parts_wpml',
			array( $lang ),
			'sf_feed_generation_process'
		);
	}

	/**
	 * @return void
	 */
	public function register(): void {
		add_action( 'sf_feed_generation_part_wpml', [ $this, 'generate_part' ], 10, 4 );
		add_action( 'sf_feed_generation_combine_feed_parts_wpml', [ $this, 'combine_parts' ] );
	}

	/**
	 * @param string $lang
	 * @param int $page
	 * @param int $post_per_page
	 *
	 * @return bool|\WP_Error true for success otherwise \WP_Error.
	 */
	public function generate_part( string $lang, int $page = 1, int $post_per_page = 20, int $last_processed_id = 0 ) {
		if ( ! is_dir( self::get_feed_parts_folder_path( $lang ) ) ) {
			wp_mkdir_p( self::get_feed_parts_folder_path( $lang ) );
		}

		$args = array(
			'limit'   => $post_per_page,
			'orderby' => 'ID',
			'order'   => 'DESC',
			'return'  => 'ids',
		);

		$where_clause = static function ( $posts_clauses ) use ( $last_processed_id ) {
			global $wpdb;
			$posts_clauses['where'] .= $wpdb->prepare( " AND $wpdb->posts.ID < %d", $last_processed_id );

			return $posts_clauses;
		};

		$original_language = apply_filters( 'wpml_current_language', null );
		do_action( 'wpml_switch_language', $lang );

		if ( $last_processed_id > 0 ) {
			add_filter( 'posts_clauses', $where_clause );
		}
		$products = Products::get_instance()->get_products( $args );
		if ( $last_processed_id > 0 ) {
			remove_filter( 'posts_clauses', $where_clause );
		}

		// If the query doesn't return any products, schedule the combine action and stop the current action.
		if ( empty( $products ) ) {
			do_action( 'wpml_switch_language', $original_language );
			self::schedule_combine_parts( $lang );

			return true;
		}

		// Process products returned by the query and reschedule the action for the next page.
		$result = $this->write_products_feed(
			self::get_feed_parts_file_path( $lang, $page ),
			$products
		);

		do_action( 'wpml_switch_language', $original_language );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$page ++;
		$last_product_id = end( $products );
		self::schedule_generation_part( $lang, $page, $post_per_page, $last_product_id );

		return true;
	}

	/**
	 * @return bool|\WP_Error true for success otherwise \WP_Error.
	 */
	public function combine_parts( string $lang ) {
		$dir_parts = self::get_feed_parts_folder_path( $lang );
		$files     = glob( $dir_parts . '/part_*.xml' ); // @codingStandardsIgnoreLine.

		/**
		 * Save products tag to a temporary file
		 * Read and get the xml content from the file and remove the xml header
		 * Delete the temporary file
		 */
		return $this->combine_and_write_product_feed(
			$files,
			self::get_feed_file_path( $lang, false, true ),
			self::get_feed_file_path( $lang, false )
		);
	}
}
