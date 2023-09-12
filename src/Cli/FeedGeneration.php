<?php

namespace ShoppingFeed\ShoppingFeedWC\Cli;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

use ShoppingFeed\ShoppingFeedWC\Feed\Generator;

/**
 * Class to add CLI command for feed generation
 * @example wp shopping-feed feed-generation
 */
class FeedGeneration {

	public function __invoke() {
		$generator = Generator::get_instance();
		$return = $generator->generate();
		if ( is_wp_error( $return ) ) {
			\WP_CLI::error(
				sprintf(
				/* translators: %s: Error message */
					__( 'Error during feed generation : %s', 'shopping-feed' ),
					$return->get_error_message()
				)
			);
		}
	}
}
