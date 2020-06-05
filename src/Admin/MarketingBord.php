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
		<h3 class="sf__marketing--title">Bienvenue sur Shoppingfeed</h3>
		<div class="sf__marketing--subtitle">La nouvelle façon de penser les flux</div>
		<p class="sf__marketing--text">Pour aller plus loin dans la performance d’optimisation nous vous proposons de <strong>télécharger gratuitement nos livres blancs.</strong></p>
		<ul class="sf__marketing--download">
			<li><a href="https://content.shopping-feed.com/migrer-vers-la-technologie-shopping-feed" target="_blank">Migration vers ShoppingFeed</a></li>
			<li><a href="https://content.shopping-feed.com/les-techniques-pour-vendre-sur-google-shopping-ads" target="_blank">Google Shopping Ads</a></li>
			<li><a href="https://content.shopping-feed.com/lancez-vous-sur-google-shopping-actions" target="_blank">Google Shopping Action</a></li>
			<li><a href="https://content.shopping-feed.com/product-graph" target="_blank">Product Graph</a></li>
		</ul>
		<div class="sf__hero">
			<div class="sf__hero--title">Envie de passer à la vitesse supérieure ?</div>
			<a href="https://www.shopping-feed.com/fr/" target="_blank" class="button sf__button">Découvrez nos offres</a>
		</div>
		<?php
	}
}
