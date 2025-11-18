<?php

namespace ShoppingFeed\ShoppingFeedWC\Tests\wpunit\Feed;

use ShoppingFeed\ShoppingFeedWC\ShoppingFeedHelper;

class HelperFunctionsTest extends \Codeception\TestCase\WPTestCase {

	private static $upload_dir;

	public static function setUpBeforeClass(): void {
		self::$upload_dir = wp_upload_dir();
	}

	public function test_get_feed_directory() {
		$this->assertEquals(
			self::$upload_dir['basedir'] . '/shopping-feed',
			ShoppingFeedHelper::get_feed_directory()
		);
	}

	public function test_get_feed_directory_filter() {
		$custom_feed_directory = '/tmp/shopping-feed';

		add_filter(
			'shopping_feed_feed_directory_path',
			function ( $dir ) use ( $custom_feed_directory ) {
				return $custom_feed_directory;
			}
		);

		$this->assertEquals(
			$custom_feed_directory,
			ShoppingFeedHelper::get_feed_directory()
		);
	}

	public function test_get_feed_part_directory() {
		$this->assertEquals(
			self::$upload_dir['basedir'] . '/shopping-feed/parts',
			ShoppingFeedHelper::get_feed_parts_directory()
		);
	}

	public function test_get_feed_part_directory_filter() {
		$custom_feed_directory = '/tmp/shopping-feed-parts';

		add_filter(
			'shopping_feed_feed_parts_directory_path',
			function ( $dir ) use ( $custom_feed_directory ) {
				return $custom_feed_directory;
			}
		);

		$this->assertEquals(
			$custom_feed_directory,
			ShoppingFeedHelper::get_feed_parts_directory()
		);
	}

	public function test_get_feed_filename() {
		$this->assertEquals(
			'products',
			ShoppingFeedHelper::get_feed_filename()
		);
	}

	public function test_get_feed_filename_filter() {
		$custom_feed_filename = 'custom-products';

		add_filter(
			'shopping_feed_feed_filename',
			function ( $filename ) use ( $custom_feed_filename ) {
				return $custom_feed_filename;
			}
		);

		$this->assertEquals(
			$custom_feed_filename,
			ShoppingFeedHelper::get_feed_filename()
		);
	}
}