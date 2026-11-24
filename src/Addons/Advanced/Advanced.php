<?php

namespace ShoppingFeed\ShoppingFeedWC\Addons\Advanced;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

/**
 * Bootstrap for built-in Advanced features (EAN, brand taxonomy for feed).
 */
class Advanced {

	public function __construct() {
		new Taxonomies();
		new Fields();
		new LegacySettingsPage();
	}
}
