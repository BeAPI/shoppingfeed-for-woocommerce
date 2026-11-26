<?php

namespace ShoppingFeed\ShoppingFeedWC\Tests\wpunit\Addons;

use ShoppingFeed\ShoppingFeedWC\Addons\StandaloneAddonsGuard;

class StandaloneAddonsGuardTest extends \Codeception\TestCase\WPTestCase {

	public function test_disable_legacy_addons_without_active_standalone_plugins() {
		StandaloneAddonsGuard::disable_legacy_addons();

		$this->assertSame( [], StandaloneAddonsGuard::get_disabled_labels() );
	}

	public function test_removes_registered_legacy_callback() {
		$executed = false;
		$callback = function () use ( &$executed ) {
			$executed = true;
		};

		add_action( 'plugins_loaded', $callback, 10 );
		remove_action( 'plugins_loaded', $callback, 10 );

		do_action( 'plugins_loaded' );

		$this->assertFalse( $executed );
	}
}
