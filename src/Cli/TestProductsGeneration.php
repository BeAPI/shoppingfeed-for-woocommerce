<?php

namespace ShoppingFeed\ShoppingFeedWC\Cli;

if ( ! defined( 'WP_CLI' ) ) {
	exit;
}

use WP_CLI;
use WP_CLI_Command;

class TestProductsGeneration extends WP_CLI_Command {
	public function __construct() {}

	/**
	 * Generate WC products for each test case
	 *
	 * @subcommand generate
	 *
	 **/
	public function generate_test_products() {
		$products_json = @file_get_contents( SF_PLUGIN_DIR . 'src/Cli/assets/json/generate-products.json' );

		if ( false === $products_json ) {
			WP_CLI::error( 'JSON file not found, exiting.' );
		}

		$products = json_decode( $products_json, true );

		if ( empty( $products ) ) {
			WP_CLI::error( 'JSON file empty, exiting.' );
		}

		$progress = WP_CLI\Utils\make_progress_bar( 'Generating test products', count( $products ) );

		$products_created = 0;

		foreach ( $products as $product ) {
			$product_categories = [];

			// Check if categories are defined in JSON
			if ( isset( $product['categories'] ) ) {
				foreach ( $product['categories'] as $category ) {
					$product_categories[] = [
						'id' => $category['id'],
					];
				}
			}

			$product_images = [];

			// Check if images are defined in JSON
			if ( isset( $product['images'] ) ) {
				foreach ( $product['images'] as $image ) {
					$product_images[] = [
						'src' => $image['src'],
					];
				}
			}

			$data = [
				'name'              => $product['name'],
				'type'              => $product['type'],
				'regular_price'     => $product['regular_price'],
				'description'       => $product['description'],
				'short_description' => $product['short_description'],
				'sku'               => (string) $product['id'],
				'categories'        => $product_categories,
				'images'            => $product_images,
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

			$create_product = wp_remote_post( str_replace( '/wp', '', get_site_url() ) . '/wp-json/wc/v3/products', $args );

			$progress->tick();

			if ( ! in_array( wp_remote_retrieve_response_code( $create_product ), [ 200, 201 ] ) ) {
				WP_CLI::warning( 'HTTP code not 200 or 201 for product ' . $product['id'] . ', skipping.' );
				continue;
			}

			if ( is_wp_error( $create_product ) ) {
				WP_CLI::error( 'Could not create product ' . $product['name'] . ' , WP_Error thrown.' );
			}

			$products_created ++;
		}

		$progress->finish();

		WP_CLI::success( $products_created . ' products created.' );
	}
}
