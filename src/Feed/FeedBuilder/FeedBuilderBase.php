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
		$batch_id = wp_generate_uuid4();
		$this->log(
			\WC_Log_Levels::INFO,
			'Starting new feed generation.',
			[
				'batch_id' => $batch_id,
			]
		);

		$this->clean_feed_parts_directory();

		self::schedule_generation_part( 1, $page_size, $batch_id );
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
	 * @param string $batch_id
	 *
	 * @return void
	 */
	public static function schedule_generation_part( int $page = 1, int $post_per_page = 20, string $batch_id = '' ): void {
		// $page can't be less than 1
		if ( $page < 1 ) {
			$page = 1;
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
				$batch_id,
			),
			'sf_feed_generation_process'
		);
	}

	/**
	 * @param string $batch_id
	 *
	 * @return void
	 */
	public static function schedule_combine_parts( string $batch_id = '' ): void {
		as_schedule_single_action(
			time() + 15,
			'sf_feed_generation_combine_feed_parts',
			array( $batch_id ),
			'sf_feed_generation_process'
		);
	}

	/**
	 * @return void
	 */
	public function register(): void {
		add_action( 'sf_feed_generation_part', [ $this, 'generate_part' ], 10, 2 );
		add_action( 'sf_feed_generation_combine_feed_parts', [ $this, 'combine_parts' ] );
	}

	/**
	 * @param int $page
	 * @param int $post_per_page
	 * @param string $batch_id
	 *
	 * @return bool|\WP_Error true for success otherwise \WP_Error.
	 */
	public function generate_part( int $page = 1, int $post_per_page = 20, string $batch_id = '' ) {
		$context = [
			'batch_id'  => $batch_id,
			'step'      => 'feed_part_generation',
			'feed_part' => $page,
		];

		$args                = array(
			'page'   => $page,
			'limit'  => $post_per_page,
			'return' => 'ids',
		);
		$products            = Products::get_instance()->get_products( $args );
		$context['products'] = [
			'args'     => $args,
			'found'    => count( $products ),
			'products' => $products,
		];

		// If the query doesn't return any products, schedule the combine action and stop the current action.
		if ( empty( $products ) ) {
			$context['status'] = 'success';
			$this->log(
				\WC_Log_Levels::INFO,
				'Feed part generation completed. Scheduling combine step.',
				$context
			);

			self::schedule_combine_parts( $batch_id );

			return true;
		}

		// Process products returned by the query and reschedule the action for the next page.
		$result = $this->write_products_feed(
			self::get_feed_parts_file_path( null, $page ),
			$products
		);
		if ( is_wp_error( $result ) ) {
			$context['status'] = 'error';
			$context['error']  = $result->get_error_message();
			$this->log(
				\WC_Log_Levels::INFO,
				'Feed part generation error.',
				$context
			);

			return $result;
		}

		$context['status'] = 'success';
		$this->log(
			\WC_Log_Levels::INFO,
			sprintf( 'Feed part generation %d completed.', $page ),
			$context
		);

		$page ++;
		self::schedule_generation_part( $page, $post_per_page, $batch_id );

		return true;
	}

	/**
	 * @param string $batch_id
	 *
	 * @return bool|\WP_Error true for success otherwise \WP_Error.
	 */
	public function combine_parts( string $batch_id = '' ) {
		$context = [
			'batch_id' => $batch_id,
			'step'     => 'feed_combination',
		];

		$dir_parts = self::get_feed_parts_folder_path();
		$files     = glob( $dir_parts . '/part_*.xml' ); // @codingStandardsIgnoreLine.

		/**
		 * Save products tag to a temporary file
		 * Read and get the xml content from the file and remove the xml header
		 * Delete the temporary file
		 */
		$result = $this->combine_and_write_product_feed(
			$files,
			self::get_feed_file_path( null, false, true ),
			self::get_feed_file_path( null, false )
		);
		if ( is_wp_error( $result ) ) {
			$context['status'] = 'error';
			$context['error']  = $result->get_error_message();
			$this->log(
				\WC_Log_Levels::INFO,
				'Feed generation error.',
				$context
			);
		} else {
			$context['status'] = 'success';
			$this->log(
				\WC_Log_Levels::INFO,
				'Feed generation completed.',
				$context
			);
		}

		return $result;
	}
}
