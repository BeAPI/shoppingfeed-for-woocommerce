<?php

namespace ShoppingFeed\ShoppingFeedWC\Feed;

use ShoppingFeed\ShoppingFeedWC\Products\Products;

class Feed {

	/**
	 * @var Generator
	 */
	private $generator;

	public function __construct(string $feed_folder, Generator $generator) {}

	public function get_url() {

	}

	public function get_refresh_url() {

	}

	/**
	 * @return string
	 */
	public function get_filepath() {

	}

	/**
	 * @return bool
	 */
	public function is_ready() {
		return is_readable( $this->get_filepath() );
	}

	/**
	 * @param bool $async
	 *
	 * @return bool|\WP_Error
	 */
	public function generate( $async = true ) {
		if ( $async ) {
			// cron generation
			return new \WP_Error( 'sf-feed-not-ready', 'Feed generation in progress.' );
		}

		$result = $this->generator->generate();
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return true;
	}

	public function get_products( $args = [] ) {
		return Products::get_instance()->get_products( $args );
	}
}
