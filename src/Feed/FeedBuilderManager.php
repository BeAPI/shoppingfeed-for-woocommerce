<?php

namespace ShoppingFeed\ShoppingFeedWC\Feed;

use ShoppingFeed\ShoppingFeedWC\Feed\FeedBuilder\FeedBuilder;
use ShoppingFeed\ShoppingFeedWC\Feed\FeedBuilder\FeedBuilderBase;
use ShoppingFeed\ShoppingFeedWC\Feed\FeedBuilder\FeedBuilderPolylang;
use ShoppingFeed\ShoppingFeedWC\Feed\FeedBuilder\FeedBuilderWpml;
use ShoppingFeed\ShoppingFeedWC\ShoppingFeed;

class FeedBuilderManager {

	/**
	 * @var FeedBuilder[]
	 */
	private $custom_builders = [];

	/**
	 * @var FeedBuilderBase
	 */
	private $base_builder;

	public function register() {
		$this->base_builder = new FeedBuilderBase();
		$this->base_builder->register();

		$this->custom_builders['pll'] = new FeedBuilderPolylang();
		$this->custom_builders['pll']->register();

		$this->custom_builders['wpml'] = new FeedBuilderWpml();
		$this->custom_builders['wpml']->register();
	}

	public function generate_feed( $lang = null ) {
		$this->get_builder()->generate_feed( $lang );

		return true;
	}

	public function launch_feed_generation( int $part_size = 20 ) {
		// Ensure all necessary directories have been created.
		ShoppingFeed::add_sf_directory();

		$this->get_builder()->launch_feed_generation( $part_size );

		return true;
	}

	/**
	 * @return array
	 */
	public function get_feed_urls(): array {
		return $this->get_builder()->get_feed_urls();
	}

	/**
	 * @param string $lang
	 *
	 * @return void
	 */
	public function render_feed( $lang = null ): void {
		$this->get_builder()->render_feed( $lang );
	}

	/**
	 * @return FeedBuilder
	 */
	protected function get_builder(): FeedBuilder {
		foreach ( $this->custom_builders as $builder ) {
			if ( $builder->is_available() ) {
				return $builder;
			}
		}

		return $this->base_builder;
	}
}
