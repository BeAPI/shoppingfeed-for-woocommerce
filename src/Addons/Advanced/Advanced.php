<?php

namespace ShoppingFeed\ShoppingFeedWC\Addons\Advanced;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

/**
 * Bootstrap for built-in Advanced features (EAN, Brand).
 */
class Advanced {

	public function __construct() {
		if ( ! defined( 'EAN_FIELD_SLUG' ) ) {
			define( 'EAN_FIELD_SLUG', 'sf_advanced_ean_field' );
		}
		if ( ! defined( 'BRAND_FIELD_SLUG' ) ) {
			define( 'BRAND_FIELD_SLUG', 'sf_advanced_brand_field' );
		}

		new Taxonomies();
		new Fields();
		new Filters();
		new LegacySettingsPage();
	}
}
