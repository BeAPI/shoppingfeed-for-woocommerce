<?php

namespace ShoppingFeed\ShoppingFeedWC\Admin;

class MarketingBord {

	/**
	 * @var MarketingBord
	 */
	private static $instance;

	/**
	 * Get the singleton instance.
	 *
	 * @return MarketingBord
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new static();
		}

		return self::$instance;
	}

	/**
	 * Singleton instance can't be cloned.
	 */
	private function __clone() {
	}

	/**
	 * Singleton instance can't be serialized.
	 */
	private function __wakeup() {
	}

	public function display_marketing_bord() {
		?>
		<h3 class="sf__marketing--title"><?php esc_html_e( 'Welcome on Shoppingfeed', 'shopping-feed' ); ?></h3>
		<div class="sf__marketing--subtitle"><?php esc_html_e( 'Fix your syndication struggle', 'shopping-feed' ); ?></div>
		<p class="sf__marketing--text"><?php esc_html_e( 'To go further and discover how to eliminate order fulfilment friction and enrich your products listings', 'shopping-feed' ); ?>, <b><?php esc_html_e( 'please download our white papers', 'shopping-feed' ); ?></b></p>
		<ul class="sf__marketing--download">
			<li><a href="https://content.shopping-feed.com/migrer-vers-la-technologie-shopping-feed" target="_blank"><?php esc_html_e( 'Migration to Shoppingfeed', 'shopping-feed' ); ?></a></li>
			<li><a href="https://content.shopping-feed.com/les-techniques-pour-vendre-sur-google-shopping-ads" target="_blank"><?php esc_html_e( 'Google Shopping Ads', 'shopping-feed' ); ?></a></li>
			<li><a href="https://content.shopping-feed.com/lancez-vous-sur-google-shopping-actions" target="_blank"><?php esc_html_e( 'Google Shopping Action', 'shopping-feed' ); ?></a></li>
			<li><a href="https://content.shopping-feed.com/product-graph" target="_blank"><?php esc_html_e( 'Product Graph', 'shopping-feed' ); ?></a></li>
		</ul>
		<div class="sf__hero">
			<div class="sf__hero--title"><?php esc_html_e( 'Join more than 2000+ sellers worldwide', 'shopping-feed' ); ?></div>
			<a href="https://www.shopping-feed.com/fr/" target="_blank" class="button sf__button"><?php esc_html_e( 'Discover our offers', 'shopping-feed' ); ?></a>
		</div>
		<?php
	}
}
