<?php

namespace ShoppingFeed\ShoppingFeedWC\Url;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

use ShoppingFeed\ShoppingFeedWC\Feed\Generator;
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
	 * @param string $for_language
	 *
	 * @return string
	 */
	public static function feed_endpoint( $for_language = '' ) {
		global $wp_rewrite;

		$feed_endpoint = $wp_rewrite->root . self::FEED_PARAM;
		if ( ! empty( $for_language ) ) {
			$feed_endpoint .= '-' . $for_language;
		}

		return $feed_endpoint;
	}

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
		$available_languages = ShoppingFeedHelper::get_available_languages();
		if ( ! empty( $available_languages ) ) {
			foreach ( $available_languages as $language ) {
				$endpoint = self::feed_endpoint( $language );
				$regex    = '^' . $endpoint . '$';
				add_rewrite_rule(
					$regex,
					array(
						self::FEED_QUERY_VAR      => true,
						self::FEED_LANG_QUERY_VAR => $language,
					),
					'top'
				);
			}
		} else {
			$endpoint = self::feed_endpoint();
			$regex    = '^' . $endpoint . '$';
			add_rewrite_rule(
				$regex,
				array( self::FEED_QUERY_VAR => true ),
				'top'
			);
		}
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
			Generator::get_instance()->render( isset( $_GET['version'] ), $lang );
		}
	}
}
