<?php

namespace ShoppingFeed\ShoppingFeedWC\Cli;

if ( ! defined( 'WP_CLI' ) ) {
	exit;
}

use WP_CLI;
use WP_CLI_Command;

class TestVariationsGeneration extends WP_CLI_Command {
	public function __construct() {}

	/**
	 * Generate WC variations for each test case
	 *
	 * @subcommand generate
	 *
	 **/
	public function generate_test_variations() {
		$variations_json = @file_get_contents( SF_PLUGIN_DIR . 'src/Cli/assets/json/generate-variations.json' );

		if ( false === $variations_json ) {
			WP_CLI::error( 'JSON file not found, exiting.' );
		}

		$variations = json_decode( $variations_json, true );

		if ( empty( $variations ) ) {
			WP_CLI::error( 'JSON file empty, exiting.' );
		}

		$progress = WP_CLI\Utils\make_progress_bar( 'Generating test variations', count( $variations ) );

		$variations_created = 0;

		foreach ( $variations as $variation ) {
			$variation_categories = [];

			// Check if categories are defined in JSON
			if ( isset( $variation['categories'] ) ) {
				foreach ( $variation['categories'] as $category ) {
					$variation_categories[] = [
						'id' => $category['id'],
					];
				}
			}

			$variation_images = [];

			// Check if images are defined in JSON
			if ( isset( $variation['images'] ) ) {
				foreach ( $variation['images'] as $image ) {
					$variation_images[] = [
						'src' => $image['src'],
					];
				}
			}

			$data = [
				'name'              => $variation['name'],
				'type'              => $variation['type'],
				'regular_price'     => $variation['regular_price'],
				'description'       => $variation['description'],
				'short_description' => $variation['short_description'],
				'sku'               => (string) $variation['id'],
				'categories'        => $variation_categories,
				'images'            => $variation_images,
			];

			$args = [
				'sslverify' => false,
				'blocking'  => true,
				'headers'   => [
					'Authorization' => 'Basic ' . base64_encode( 'beapi:beapi' ),
					'Content-Type'  => 'application/json',
					'Accept'        => 'application/json',
				],
				'body'      => json_encode( $data ),
			];

			// TODO: retrieve product ID to set variations to
			$create_variation = wp_remote_post( str_replace( '/wp', '', get_site_url() ) . '/wp-json/wc/v3/products/{PRODUCT_ID}/variations', $args );

			$progress->tick();

			if ( ! in_array( wp_remote_retrieve_response_code( $create_variation ), [ 200, 201 ] ) ) {
				WP_CLI::warning( 'HTTP code not 200 or 201 for variation ' . $variation['id'] . ', skipping.' );
				continue;
			}

			if ( is_wp_error( $create_variation ) ) {
				WP_CLI::error( 'Could not create variation ' . $variation['name'] . ' , WP_Error thrown.' );
			}

			$variations_created ++;
		}

		$progress->finish();

		WP_CLI::success( $variations_created . ' variations created.' );
	}
}
