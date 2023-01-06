<?php

namespace ShoppingFeed\ShoppingFeedWC\Cli;

if ( ! defined( 'WP_CLI' ) ) {
	exit;
}

use WC_Data_Exception;
use WP_CLI;
use WP_CLI_Command;

class TestProductsGeneration extends WP_CLI_Command {

	private $json_file;

	private $website_url;

	private $wc_endpoint;

	private $attribute_name;

	private $attribute_slug;

	public function __construct() {
		$this->json_file      = @file_get_contents( SF_PLUGIN_DIR . 'src/Cli/assets/json/generate-products.json' );
		$this->website_url    = str_replace( '/wp', '', get_site_url() );
		$this->wc_endpoint    = '/wp-json/wc/v3/products';
		$this->attribute_name = 'Test Color';
		$this->attribute_slug = 'pa_test_color';
	}

	/**
	 * Generate WC products for each test case
	 *
	 * @subcommand generate
	 *
	 *
	 * @throws WC_Data_Exception
	 */
	public function generate() {
		if ( false === $this->json_file ) {
			WP_CLI::error( 'JSON file not found, exiting.' );
		}

		$products = json_decode( $this->json_file, true );

		if ( empty( $products ) ) {
			WP_CLI::error( 'JSON file empty, exiting.' );
		}

		$progress = WP_CLI\Utils\make_progress_bar( 'Generating test products', count( $products ) );

		$products_created = 0;

		foreach ( $products as $product ) {
			$data = $this->create_data_from_product( $product );

			$data = $this->maybe_create_attributes( $data );

			$args = $this->generate_args( $data );

			$create_product = wp_remote_post( $this->website_url . $this->wc_endpoint, $args );

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

	/**
	 * Create default data from JSON product
	 *
	 * @param $product
	 *
	 * @return array
	 */
	private function create_data_from_product( $product ) {
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

		return [
			'name'              => $product['name'],
			'type'              => $product['type'],
			'regular_price'     => $product['regular_price'],
			'description'       => $product['description'],
			'short_description' => $product['short_description'],
			'sku'               => (string) $product['id'],
			'categories'        => $product_categories,
			'images'            => $product_images,
		];
	}

	/**
	 * Add attributes to data for variable products
	 *
	 * @param $data
	 *
	 * @return array
	 */
	private function maybe_create_attributes( $data ) {
		if ( 'variable' !== $data['type'] ) {
			return $data;
		}

		$data['attributes'] = [
			[
				'name'      => 'Test',
				'position'  => 1,
				'visible'   => true,
				'variation' => true,
				'options'   => [ 'Yes', 'No' ],
			],
		];

		return $data;
	}

	/**
	 * Set wp_remote_post args to create a product through the WC API
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
}
