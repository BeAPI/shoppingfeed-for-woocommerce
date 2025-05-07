<?php

namespace ShoppingFeed\ShoppingFeedWC\Addons\Plugins\WoocommerceGlsPlugin;

// Exit on direct access
use WC_Shipping_Zones;

defined( 'ABSPATH' ) || exit;

class WoocommerceGls {
	public function __construct() {
		if ( ! class_exists( '\WC_Gls' ) ) {
			return;
		}

		add_filter( 'sf_all_shipping_methods', [ $this, 'add_woocommerce_gls_shipping_methods' ] );
	}

	public function add_woocommerce_gls_shipping_methods( $shipping_methods ): array {

		// récupérer les méthodes actives et les pays


		$gls_shipping_methods_slugs = array_keys( \WC_Gls::$carrier_definition );


		/* @var \WC_Gls_Table_Rate_Shipping[] $gls_shipping_methods */
		$gls_shipping_methods = array_filter( WC()->shipping()->get_shipping_methods(), function ( $item ) use ( $gls_shipping_methods_slugs ) {

			return in_array( $item->id, $gls_shipping_methods_slugs );
		} );

		$gls_shipping_methods_index = $this->build_gls_shipping_methods_index( $gls_shipping_methods );

		foreach ( $shipping_methods as &$shipping_method ) {
			$shipping_zone = new \WC_Shipping_Zone( $shipping_method['zone_id'] );

			foreach ( $shipping_zone->get_zone_locations() as $zone_location ) {

				if ( $zone_location->type !== 'country' ) {
					continue;
				}

				if ( ! isset( $gls_shipping_methods_index[ $zone_location->code ] ) ) {
					continue;
				}

				// TODO find a solution for the duplicates
				foreach( $gls_shipping_methods_index[ $zone_location->code ] as $shipping_method_slug ) {

					$instance = $gls_shipping_methods[ $shipping_method_slug ];

					$shipping_method['methods'][] = [
						'method_rate_id' => $instance->id,
						'method_id'      => $instance->instance_id,
						'method_title'   => $instance->title,
					];
				}

			}
		}

		return $shipping_methods;
	}

	private function build_gls_shipping_methods_index( $gls_shipping_methods ) {
		$index = [];

		foreach ( $gls_shipping_methods as $shipping_method_slug => $shipping_method ) {

			if ( ! is_array( $shipping_method->zones ) ) {
				continue;
			}

			foreach ( $shipping_method->zones as $zone ) {
				if ( $zone['type'] !== 'country' ) {
					continue;
				}

				foreach ( $zone['country'] as $country ) {
					if ( ! isset( $index[ $country ] ) ) {
						$index[ $country ] = [];
					}
					$index[ $country ][] = $shipping_method_slug;
				}

			}


		}

		return $index;
	}
}