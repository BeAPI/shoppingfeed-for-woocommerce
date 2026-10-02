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

	public function test_yoast_metas_resolves_title_replace_var() {
		if ( ! class_exists( '\WPSEO_Meta' ) || ! class_exists( '\WPSEO_Replace_Vars' ) ) {
			$this->markTestSkipped( 'Yoast SEO is not loaded in the test environment.' );
		}

		$wc_product = wc_get_product( 13 );
		if ( ! $wc_product ) {
			$this->markTestSkipped( 'Fixture product 13 is not available.' );
		}

		$post_title = get_post_field( 'post_title', $wc_product->get_id() );
		update_post_meta( $wc_product->get_id(), '_yoast_wpseo_title', '%%title%% coton bio' );
		update_post_meta( $wc_product->get_id(), '_yoast_wpseo_metadesc', 'Ce %%title%% est bio' );

		new YoastMetas();

		$sf_product = new Product( $wc_product );
		$extra      = $sf_product->get_extra_fields();
		$by_name    = array_column( $extra, 'value', 'name' );

		$this->assertArrayHasKey( 'meta-title', $by_name );
		$this->assertArrayHasKey( 'meta-description', $by_name );
		$this->assertStringContainsString( $post_title, $by_name['meta-title'] );
		$this->assertStringNotContainsString( '%%title%%', $by_name['meta-title'] );
		$this->assertStringContainsString( $post_title, $by_name['meta-description'] );
		$this->assertStringNotContainsString( '%%title%%', $by_name['meta-description'] );
	}
}
