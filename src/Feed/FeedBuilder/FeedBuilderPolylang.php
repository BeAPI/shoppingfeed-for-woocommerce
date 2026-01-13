<?php

namespace ShoppingFeed\ShoppingFeedWC\Feed\FeedBuilder;

use ShoppingFeed\ShoppingFeedWC\Products\Products;
use ShoppingFeed\ShoppingFeedWC\ShoppingFeedHelper;

class FeedBuilderPolylang extends FeedBuilder {

	/**
	 * @inerhitDoc
	 */
	public function is_available(): bool {
		return function_exists( '\pll_languages_list' ) && ! empty( $this->get_languages() );
	}

	/**
	 * @inerhitDoc
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

			$args     = [
				'lang' => $language,
			];
			$products = Products::get_instance()->get_products( $args, $lang );

			$this->write_products_feed(
				self::get_feed_file_path( $language ),
				$products
			);
		}

		return true;
	}

	/**
	 * @inerhitDoc
	 */
	public function launch_feed_generation( int $page_size ): void {
		$batch_id = wp_generate_uuid4();

		foreach ( $this->get_languages() as $language ) {
			$this->log(
				\WC_Log_Levels::INFO,
				sprintf( 'Starting new feed generation for language %s.', $language ),
				[
					'batch_id' => $batch_id,
				]
			);

			$this->clean_feed_parts_directory( $language );
			self::schedule_generation_part( $language, 1, $page_size, $batch_id );
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
		return pll_languages_list(
			[
				'hide_empty' => false,
			]
		);
	}

	/**
	 * @inerhitDoc
	 */
	public function current_languages(): string {
		return pll_current_language();
	}

	/**
	 * @param string $lang
	 * @param int $page
	 * @param int $post_per_page
	 * @param string $batch_id
	 *
	 * @return void
	 */
	public static function schedule_generation_part( string $lang, int $page = 1, int $post_per_page = 20, string $batch_id = '' ): void {
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
			'sf_feed_generation_part_pll',
			array(
				$lang,
				$page,
				$post_per_page,
				$batch_id,
			),
			'sf_feed_generation_process'
		);
	}

	/**
	 * @param string $lang
	 * @param string $batch_id
	 *
	 * @return void
	 */
	public static function schedule_combine_parts( string $lang, string $batch_id = '' ): void {
		as_schedule_single_action(
			time() + 15,
			'sf_feed_generation_combine_feed_parts_pll',
			array(
				$lang,
				$batch_id
			),
			'sf_feed_generation_process'
		);
	}

	/**
	 * @return void
	 */
	public function register(): void {
		add_action( 'sf_feed_generation_part_pll', [ $this, 'generate_part' ], 10, 4 );
		add_action( 'sf_feed_generation_combine_feed_parts_pll', [ $this, 'combine_parts' ], 10, 2 );
	}

	/**
	 * @param string $lang
	 * @param int $page
	 * @param int $post_per_page
	 * @param string $batch_id
	 *
	 * @return bool|\WP_Error true for success otherwise \WP_Error.
	 */
	public function generate_part( string $lang, int $page = 1, int $post_per_page = 20, string $batch_id = '' ) {
		if ( ! is_dir( self::get_feed_parts_folder_path( $lang ) ) ) {
			wp_mkdir_p( self::get_feed_parts_folder_path( $lang ) );
		}

		$context = [
			'batch_id'  => $batch_id,
			'step'      => 'feed_part_generation',
			'lang'      => $lang,
			'feed_part' => $page,
		];

		$args                = array(
			'page'   => $page,
			'limit'  => $post_per_page,
			'return' => 'ids',
			'lang'   => $lang,
		);
		$products            = Products::get_instance()->get_products( $args, $lang );
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

			self::schedule_combine_parts( $lang, $batch_id );

			return true;
		}

		// Process products returned by the query and reschedule the action for the next page.
		$result = $this->write_products_feed(
			self::get_feed_parts_file_path( $lang, $page ),
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
		self::schedule_generation_part( $lang, $page, $post_per_page, $batch_id );

		return true;
	}

	/**
	 * @param string $lang
	 * @param string $batch_id
	 *
	 * @return bool|\WP_Error true for success otherwise \WP_Error.
	 */
	public function combine_parts( string $lang, string $batch_id = '' ) {
		$context = [
			'batch_id' => $batch_id,
			'step'     => 'feed_combination',
			'lang'     => $lang,
		];

		$dir_parts = self::get_feed_parts_folder_path( $lang );
		$files     = glob( $dir_parts . '/part_*.xml' ); // @codingStandardsIgnoreLine.

		/**
		 * Save products tag to a temporary file
		 * Read and get the xml content from the file and remove the xml header
		 * Delete the temporary file
		 */
		$result = $this->combine_and_write_product_feed(
			$files,
			self::get_feed_file_path( $lang, false, true ),
			self::get_feed_file_path( $lang, false )
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
