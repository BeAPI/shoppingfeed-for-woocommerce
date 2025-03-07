<?php

namespace ShoppingFeed\ShoppingFeedWC\Url;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

use ShoppingFeed\ShoppingFeedWC\ShoppingFeedHelper;

/**
 * Class Rewrite to rewrite url for generated feed
 * @package ShoppingFeed\Url
 */
class Rewrite {

	const FEED_PARAM = 'shopping-feed';
	const FEED_QUERY_VAR = 'shopping_feed';
	const FEED_LANG_QUERY_VAR = 'feed_language';

	/**
	 * Rewrite constructor.
	 */
	public function __construct() {

		//Add custom rewrite rule
		add_action( 'init', array( $this, 'sf_add_custom_rewrite_rule' ) );

		//add custom args
		add_filter( 'query_vars', array( $this, 'sf_add_custom_query_vars' ), 1 );

		//parse the request to check if we had parameters to get the feed
		add_action( 'parse_request', array( $this, 'sf_parse_request' ) );
	}

	/**
	 * Add new pretty url to getting the feed
	 */
	public function sf_add_custom_rewrite_rule() {
		// regex match either the base endpoint or the localized endpoints.
		$regex = sprintf(
			'^%s(?:-([a-z]{2,3}))?/?$',
			ShoppingFeedHelper::get_public_feed_endpoint()
		);
		$query = sprintf(
			'index.php?%s=1&%s=$matches[1]',
			self::FEED_QUERY_VAR,
			self::FEED_LANG_QUERY_VAR
		);
		add_rewrite_rule(
			$regex,
			$query,
			'top'
		);
	}

	/**
	 * Add custom query vars
	 *
	 * @param $vars
	 *
	 * @return mixed
	 */
	public function sf_add_custom_query_vars( $vars ) {
		$vars[] = self::FEED_QUERY_VAR;
		$vars[] = self::FEED_LANG_QUERY_VAR;

		return $vars;
	}

	/**
	 * Parse request and check if we have required params to render the feed
	 */
	public function sf_parse_request() {
		global $wp;
		if ( isset( $wp->query_vars[ self::FEED_QUERY_VAR ] ) ) {
			$lang = $wp->query_vars[ self::FEED_LANG_QUERY_VAR ] ?? null;
			ShoppingFeedHelper::get_feedbuilder_manager()->render_feed( $lang );
		}
	}
}
