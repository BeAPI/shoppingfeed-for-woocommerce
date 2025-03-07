<?php

namespace ShoppingFeed\ShoppingFeedWC\Feed\FeedBuilder;

use ShoppingFeed\ShoppingFeedWC\Feed\AsyncGenerator;
use ShoppingFeed\ShoppingFeedWC\Products\Products;
use ShoppingFeed\ShoppingFeedWC\ShoppingFeedHelper;

class FeedBuilderPolylang extends FeedBuilder {

	public function is_available(): bool {
		return function_exists( '\pll_languages_list' );
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

			$args     = [
				'lang' => $language,
			];
			$products = Products::get_instance()->get_products( $args );

			$this->write_products_feed(
				self::get_feed_file_path( $language ),
				$products
			);
		}

		return true;
	}

	public function launch_feed_generation( int $page_size ): void {
		foreach ( $this->get_languages() as $language ) {
			self::schedule_generation_part( $language, 1, $page_size );
		}
	}

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

	public function render_feed( $lang = null ): void {
		$file_path = self::get_feed_file_path( $lang, false );

		if ( ! is_file( $file_path ) ) {
			if ( ShoppingFeedHelper::is_process_running( 'sf_feed_generation_process' ) ) {
				wp_die( 'Feed generation already launched' );
			}
			AsyncGenerator::get_instance()->launch();
			wp_die( 'Feed generation launched' );
		}

		header( 'Content-Type: application/xml; charset=utf-8' );
		header( 'Content-Length: ' . filesize( $file_path ) );
		nocache_headers();
		readfile( $file_path );
		exit;
	}

	/**
	 * @return string[]
	 */
	public function get_languages(): array {
		return pll_languages_list(
			[
				'hide_empty' => false,
			]
		);
	}

	public static function get_feed_folder_path( bool $include_prefix = true ): string {
		$dir = ShoppingFeedHelper::get_feed_directory();

		return $include_prefix ? 'file://' . $dir : $dir;
	}

	public static function get_feed_file_path( string $lang, bool $include_prefix = true, bool $tmp = false ): string {
		$dir  = self::get_feed_folder_path( $include_prefix );
		$file = ShoppingFeedHelper::get_feed_filename() . '_' . $lang;
		if ( $tmp ) {
			$file .= '_tmp';
		}

		return $dir . '/' . $file . '.xml';
	}

	public static function get_feed_parts_folder_path( string $lang, bool $include_prefix = true ): string {
		$dir = ShoppingFeedHelper::get_feed_parts_directory();
		$dir .= '/' . $lang;

		return $include_prefix ? 'file://' . $dir : $dir;
	}

	public static function get_feed_parts_file_path( string $lang, int $page, bool $include_prefix = true ): string {
		$dir  = self::get_feed_parts_folder_path( $lang, $include_prefix );
		$file = sprintf( 'part_%s', zeroise( $page, 2 ) );

		return $dir . '/' . $file . '.xml';
	}

	/**
	 * @param int $page
	 * @param int $post_per_page
	 *
	 * @return void
	 */
	public static function schedule_generation_part( string $lang, int $page = 1, int $post_per_page = 20 ): void {
		// $page can't be less than 1
		if ( $page < 1 ) {
			$page = 1;
		}

		// $post_per_page can't be less than 1
		if ( $post_per_page < 1 ) {
			$post_per_page = 20;
		}

		as_enqueue_async_action(
			'sf_feed_generation_part_pll',
			array(
				$lang,
				$page,
				$post_per_page,
			),
			'sf_feed_generation_process'
		);
	}

	/**
	 * @return void
	 */
	public static function schedule_combine_parts( string $lang ): void {
		as_enqueue_async_action(
			'sf_feed_generation_combine_feed_parts_pll',
			array( $lang ),
			'sf_feed_generation_process'
		);
	}

	/**
	 * @return void
	 */
	public function register(): void {
		add_action( 'sf_feed_generation_part_pll', [ $this, 'generate_part' ], 10, 3 );
		add_action( 'sf_feed_generation_combine_feed_parts_pll', [ $this, 'combine_parts' ] );
	}

	/**
	 * @param string $lang
	 * @param int $page
	 * @param int $post_per_page
	 *
	 * @return bool|\WP_Error true for success otherwise \WP_Error.
	 */
	public function generate_part( string $lang, int $page = 1, int $post_per_page = 20 ) {
		if ( ! is_dir( self::get_feed_parts_folder_path( $lang, false ) ) ) {
			wp_mkdir_p( self::get_feed_parts_folder_path( $lang, false ) );
		}

		$args     = array(
			'page'   => $page,
			'limit'  => $post_per_page,
			'return' => 'ids',
			'lang'   => $lang,
		);
		$products = Products::get_instance()->get_products( $args );

		// If the query doesn't return any products, schedule the combine action and stop the current action.
		if ( empty( $products ) ) {
			self::schedule_combine_parts( $lang );

			return true;
		}

		// Process products returned by the query and reschedule the action for the next page.
		$result = $this->write_products_feed(
			self::get_feed_parts_file_path( $lang, $page ),
			$products
		);
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$page ++;
		self::schedule_generation_part( $lang, $page, $post_per_page );

		return true;
	}

	/**
	 * @return bool|\WP_Error true for success otherwise \WP_Error.
	 */
	public function combine_parts( string $lang ) {
		$dir_parts = self::get_feed_parts_folder_path( $lang, false );
		$pattern   = $dir_parts . '/part_*.xml';
		$files     = glob( $pattern,  ); // @codingStandardsIgnoreLine.

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
