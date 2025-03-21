<?php

namespace ShoppingFeed\ShoppingFeedWC\Cli;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

use ShoppingFeed\ShoppingFeedWC\Feed\Generator;
use ShoppingFeed\ShoppingFeedWC\ShoppingFeedHelper;
use function WP_CLI\Utils\get_flag_value;

/**
 * Class to add CLI command for feed generation
 * @example wp shopping-feed feed-generation
 */
class FeedGeneration {

	/**
	 * Generate products' feeds.
	 *
	 * ## OPTIONS
	 *
	 * [--lang=<lang>]
	 * : Generate feed for a specific language.
	 *
	 * ## EXAMPLES
	 *
	 *     wp example hello Newman
	 */
	public function __invoke( $args, $assoc_args ) {
		$lang   = get_flag_value( $assoc_args, 'lang' );
		$return = ShoppingFeedHelper::get_feedbuilder_manager()->generate_feed( $lang );
		if ( is_wp_error( $return ) ) {
			\WP_CLI::error(
				sprintf(
				/* translators: %s: Error message */
					__( 'Error during feed generation : %s', 'shopping-feed' ),
					$return->get_error_message()
				)
			);
		}
		\WP_CLI::success( __( 'Product feed generation complete.', 'shopping-feed' ) );
	}
}
