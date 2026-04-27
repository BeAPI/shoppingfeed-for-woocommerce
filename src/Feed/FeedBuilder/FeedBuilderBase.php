<?php

namespace ShoppingFeed\ShoppingFeedWC\Feed\FeedBuilder;

use ShoppingFeed\ShoppingFeedWC\Products\Products;
use ShoppingFeed\ShoppingFeedWC\ShoppingFeedHelper;

class FeedBuilderBase extends FeedBuilder {

	/**
	 * @inerhitDoc
	 */
	public function is_available(): bool {
		return true;
	}

	/**
	 * @inerhitDoc
	 */
	public function support_multilingual(): bool {
		return false;
	}

	/**
	 * @inerhitDoc
	 */
	public function get_languages(): array {
		return [];
	}

	/**
	 * @inerhitDoc
	 */
	public function current_languages(): string {
		return '';
	}

	/**
	 * Launch products' feed generation.
	 *
	 * @return bool|\WP_Error
	 */
	public function generate_feed( $lang = null ) {
		$products = Products::get_instance()->get_products();

		return $this->write_products_feed(
			self::get_feed_file_path(),
			$products
		);
	}

	/**
	 * @inerhitDoc
	 */
	public function launch_feed_generation( int $page_size ): void {
		$this->clean_feed_parts_directory();
		self::schedule_generation_part( 1, $page_size );
	}

	public function get_feed_urls(): array {
		if ( ! empty( get_option( 'permalink_structure' ) ) ) {
			return [ sprintf( '%s/%s/', get_home_url(), ShoppingFeedHelper::get_public_feed_endpoint() ) ];
		}

		return [ sprintf( '%s?%s', get_home_url(), ShoppingFeedHelper::get_public_feed_endpoint() ) ];
	}

	/**
	 * @param int $page
	 * @param int $post_per_page
	 * @param int $last_processed_id
	 *
	 * @return void
	 */
	public static function schedule_generation_part( int $page = 1, int $post_per_page = 20, int $last_processed_id = 0 ): void {
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
			'sf_feed_generation_part',
			array(
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
	public static function schedule_combine_parts(): void {
		as_schedule_single_action(
			time() + 15,
			'sf_feed_generation_combine_feed_parts',
			array(),
			'sf_feed_generation_process'
		);
	}

	/**
	 * @return void
	 */
	public function register(): void {
		add_action( 'sf_feed_generation_part', [ $this, 'generate_part' ], 10, 3 );
		add_action( 'sf_feed_generation_combine_feed_parts', [ $this, 'combine_parts' ] );
	}

	/**
	 * Generate a feed's part.
	 *
	 * @param int $page Deprecated. The current page of products to generate.
	 *                  Replaced by the `$last_processed_id`. Kept for back-compatibility.
	 * @param int $post_per_page The number of products to include in the part.
	 * @param int $last_processed_id The last product id processed in the previous batch.
	 *                               The new batch will start processing products from this last ID.
	 *
	 * @return bool|\WP_Error true for success otherwise \WP_Error.
	 */
	public function generate_part( int $page = 1, int $post_per_page = 20, int $last_processed_id = 0 ) {
		// Detect old feed generation params and reschedule the process.
		if ( $page > 1 && 0 === $last_processed_id ) {
			$this->clean_feed_parts_directory();
			self::schedule_generation_part( 1, $post_per_page );

			return new \WP_Error(
				'shopping_feed_generation_deprecated_page',
				'Deprecated parameter page use. Rescheduling new feed generation.'
			);
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

		if ( $last_processed_id > 0 ) {
			add_filter( 'posts_clauses', $where_clause );
		}
		$products = Products::get_instance()->get_products( $args );
		if ( $last_processed_id > 0 ) {
			remove_filter( 'posts_clauses', $where_clause );
		}

		// If the query doesn't return any products, schedule the combine action and stop the current action.
		if ( empty( $products ) ) {
			self::schedule_combine_parts();

			return true;
		}

		// Process products returned by the query and reschedule the action for the next page.
		$result = $this->write_products_feed(
			self::get_feed_parts_file_path( null, $page ),
			$products
		);
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$page ++;
		$last_product_id = end( $products );
		self::schedule_generation_part( $page, $post_per_page, $last_product_id );

		return true;
	}

	/**
	 * @return bool|\WP_Error true for success otherwise \WP_Error.
	 */
	public function combine_parts() {
		$dir_parts = self::get_feed_parts_folder_path();
		$files     = glob( $dir_parts . '/part_*.xml' ); // @codingStandardsIgnoreLine.

		/**
		 * Save products tag to a temporary file
		 * Read and get the xml content from the file and remove the xml header
		 * Delete the temporary file
		 */
		return $this->combine_and_write_product_feed(
			$files,
			self::get_feed_file_path( null, false, true ),
			self::get_feed_file_path( null, false )
		);
	}
}
