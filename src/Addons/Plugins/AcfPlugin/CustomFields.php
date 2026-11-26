<?php

namespace ShoppingFeed\ShoppingFeedWC\Addons\Plugins\AcfPlugin;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

/**
 * Bootstrap for ACF product fields in the feed.
 */
class CustomFields {

	public function __construct() {
		new LegacySettingsPage();

		if ( ! defined( 'ACF_VERSION' ) ) {
			return;
		}

		new Filters();
	}
}
