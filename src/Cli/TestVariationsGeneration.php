<?php

namespace ShoppingFeed\ShoppingFeedWC\Cli;

if ( ! defined( 'WP_CLI' ) ) {
	exit;
}

use WP_CLI;
use WP_CLI_Command;

class TestVariationsGeneration extends WP_CLI_Command {
	private $json_file;

	private $website_url;

	private $wc_endpoint;

	public function __construct() {
		$this->json_file   = @file_get_contents( SF_PLUGIN_DIR . 'src/Cli/assets/json/generate-variations.json' );
		$this->website_url = str_replace( '/wp', '', get_site_url() );
		$this->wc_endpoint = '/wp-json/wc/v3/products/%d/variations';
	}

	/**
	 * Generate WC variations for each test case
	 *
	 * @subcommand generate
	 *
	 **/
	public function generate() {
		if ( false === $this->json_file ) {
			WP_CLI::error( 'JSON file not found, exiting.' );
		}

		$variations = json_decode( $this->json_file, true );

		if ( empty( $variations ) ) {
			WP_CLI::error( 'JSON file empty, exiting.' );
		}

		$progress = WP_CLI\Utils\make_progress_bar( 'Generating test variations', count( $variations ) );

		$variations_created = 0;

		foreach ( $variations as $variation ) {
			$data = $this->create_data_from_variation( $variation );
			$args = $this->generate_args( $data );

			// Map variations to variable products
			$variable_products_ids = $this->match_variations_to_variable_product( $variation['id'] );

			foreach ( $variable_products_ids as $vpid ) {
				$create_variation = wp_remote_post( $this->website_url . sprintf( $this->wc_endpoint, $vpid ), $args );

				if ( ! in_array( wp_remote_retrieve_response_code( $create_variation ), [ 200, 201 ] ) ) {
					WP_CLI::warning( 'HTTP code not 200 or 201 for variation ' . $variation['id'] . ', skipping.' );
					continue;
				}

				if ( is_wp_error( $create_variation ) ) {
					WP_CLI::error( 'Could not create variation ' . $variation['name'] . ' , WP_Error thrown.' );
				}
			}

			$variations_created ++;
			$progress->tick();
		}

		$progress->finish();

		WP_CLI::success( $variations_created . ' variations created.' );
	}

	/**
	 * Create default data from JSON variation
	 *
	 * @param $variation
	 *
	 * @return array
	 */
	private function create_data_from_variation( $variation ) {
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

		return [
			'name'              => $variation['name'],
			'type'              => $variation['type'],
			'regular_price'     => $variation['regular_price'],
			'description'       => $variation['description'],
			'short_description' => $variation['short_description'],
			'sku'               => (string) $variation['id'],
			'categories'        => $variation_categories,
			'images'            => $variation_images,
		];
	}

	/**
	 * Set wp_remote_post args to create a variation through the WC API
	 *
	 * @param $data
	 *
	 * @return array
	 */
	private function generate_args( $data ) {
		return [
			'sslverify' => false,
			'blocking'  => true,
			'headers'   => [
				'Authorization' => 'Basic ' . base64_encode( 'beapi:beapi' ),
				'Content-Type'  => 'application/json',
				'Accept'        => 'application/json',
			],
			'body'      => json_encode( $data ),
		];
	}

	/**
	 * Match each variation to variable product(s)
	 *
	 * @param int $variation_id
	 *
	 * @return array|int[]
	 */
	private function match_variations_to_variable_product( $variation_id ) {
		switch ( $variation_id ) {
			case 999101:
				$variable_products_ids = [ 999010, 999011, 999012, 999013 ];
				break;
			default:
				$variable_products_ids = [];
				break;
		}

		return $variable_products_ids;
	}
}
