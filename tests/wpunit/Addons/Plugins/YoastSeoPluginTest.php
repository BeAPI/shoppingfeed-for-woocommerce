<?php

namespace ShoppingFeed\ShoppingFeedWC\Tests\wpunit\Addons\Plugins;

use ShoppingFeed\ShoppingFeedWC\Addons\Plugins\YoastSeoPlugin\YoastMetas;
use ShoppingFeed\ShoppingFeedWC\Products\Product;

class YoastSeoPluginTest extends \Codeception\TestCase\WPTestCase {

	public function test_yoast_metas_adds_meta_title_and_description() {
		if ( ! class_exists( '\WPSEO_Meta' ) || ! class_exists( '\WPSEO_Replace_Vars' ) ) {
			$this->markTestSkipped( 'Yoast SEO is not loaded in the test environment.' );
		}

		new YoastMetas();

		$wc_product = wc_get_product( 13 );
		$sf_product = new Product( $wc_product );
		$extra      = $sf_product->get_extra_fields();

		$names = wp_list_pluck( $extra, 'name' );
		$this->assertContains( 'meta-title', $names );
		$this->assertContains( 'meta-description', $names );
	}
}
