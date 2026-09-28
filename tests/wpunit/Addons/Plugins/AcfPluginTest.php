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

	public function test_acf_is_selected_helper() {
		$options = [
			[
				'key' => 'field_one',
			],
		];

		$this->assertTrue( CustomFieldsHelper::acf_is_selected( 'field_one', $options ) );
		$this->assertFalse( CustomFieldsHelper::acf_is_selected( 'field_two', $options ) );
	}
}
