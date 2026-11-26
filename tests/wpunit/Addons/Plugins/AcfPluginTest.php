<?php

namespace ShoppingFeed\ShoppingFeedWC\Tests\wpunit\Addons\Plugins;

use ShoppingFeed\ShoppingFeedWC\Addons\Plugins\AcfPlugin\CustomFieldsHelper;
use ShoppingFeed\ShoppingFeedWC\Admin\Options;

class AcfPluginTest extends \Codeception\TestCase\WPTestCase {

	public function test_get_acf_options_fallback_to_legacy_option() {
		delete_option( Options::SF_FEED_OPTIONS );
		update_option(
			CustomFieldsHelper::LEGACY_OPTIONS,
			[
				'acf' => [
					wp_json_encode(
						[
							'type'  => 'text',
							'name'  => 'color',
							'key'   => 'field_color',
							'label' => 'Color',
						]
					),
				],
			]
		);

		$options = CustomFieldsHelper::get_acf_options();
		$this->assertCount( 1, $options );
		$this->assertSame( 'field_color', $options[0]['key'] );
	}

	public function test_get_acf_options_ignores_legacy_when_feed_acf_key_is_empty_array() {
		update_option(
			Options::SF_FEED_OPTIONS,
			[
				'acf' => [],
			]
		);
		update_option(
			CustomFieldsHelper::LEGACY_OPTIONS,
			[
				'acf' => [
					wp_json_encode(
						[
							'type'  => 'text',
							'name'  => 'color',
							'key'   => 'field_color',
							'label' => 'Color',
						]
					),
				],
			]
		);

		$options = CustomFieldsHelper::get_acf_options();
		$this->assertSame( [], $options );
	}

	public function test_acf_is_selected_helper() {
		$options = [
			[
				'key' => 'field_one',
			],
		];

		$this->assertTrue( CustomFieldsHelper::acf_is_selected( 'field_one', $options ) );
		$this->assertFalse( CustomFieldsHelper::acf_is_selected( 'field_two', $options ) );
	}

	public function test_sanitize_sf_feed_options_preserves_acf_when_acf_inactive() {
		if ( defined( 'ACF_VERSION' ) ) {
			$this->markTestSkipped( 'ACF is active in this environment.' );
		}

		$stored = [
			wp_json_encode(
				[
					'key' => 'field_one',
				]
			),
		];
		update_option(
			Options::SF_FEED_OPTIONS,
			[
				'acf' => $stored,
			]
		);

		$options = new Options();
		$result  = $options->sanitize_sf_feed_options( [] );

		$this->assertSame( $stored, $result['acf'] );
	}

	public function test_sanitize_sf_feed_options_clears_acf_when_acf_active_and_key_missing() {
		if ( ! defined( 'ACF_VERSION' ) ) {
			define( 'ACF_VERSION', '6.0.0' );
		}

		update_option(
			Options::SF_FEED_OPTIONS,
			[
				'acf' => [
					wp_json_encode(
						[
							'key' => 'field_one',
						]
					),
				],
			]
		);

		$options = new Options();
		$result  = $options->sanitize_sf_feed_options( [] );

		$this->assertSame( [], $result['acf'] );
	}
}
