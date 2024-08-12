<?php

namespace ShoppingFeed\ShoppingFeedWC\ShipmentTracking;

use ShoppingFeed\ShoppingFeedWC\ShipmentTracking\Provider\AdvancedShipmentTracking;
use ShoppingFeed\ShoppingFeedWC\ShipmentTracking\Provider\NullShipmentTracking;
use ShoppingFeed\ShoppingFeedWC\ShipmentTracking\Provider\PhWoocommerceShipmentTracking;
use ShoppingFeed\ShoppingFeedWC\ShipmentTracking\Provider\ShoppingfeedAdvanced;

class ShipmentTrackingManager {

	/**
	 * @var ShipmentTrackingProvider[] $providers
	 */
	private $providers = [];

	/**
	 * @var string
	 */
	private $selected_provider;

	public static function create( array $shipping_options = [] ): self {
		$manager = new self();

		$shipment_tracking_providers = [
			new ShoppingfeedAdvanced(),
			new AdvancedShipmentTracking(),
			new PhWoocommerceShipmentTracking(),
		];
		$shipment_tracking_providers = apply_filters( 'sf_shipment_tracking_providers', $shipment_tracking_providers );
		array_map( [ $manager, 'add_provider' ], $shipment_tracking_providers );

		if ( isset( $shipping_options['tracking_provider'] ) ) {
			$manager->selected_provider = $shipping_options['tracking_provider'];
		}

		return $manager;
	}

	/**
	 * @param ShipmentTrackingProvider $provider
	 *
	 * @return void
	 */
	public function add_provider( ShipmentTrackingProvider $provider ): void {
		$this->providers[ $provider->id() ] = $provider;
	}

	/**
	 * @return ShipmentTrackingProvider[]
	 */
	public function get_providers( bool $available_only = false ): array {
		$providers = $this->providers;
		if ( $available_only ) {
			$providers = array_filter(
				$providers,
				function ( $provider ) {
					return $provider->is_available();
				}
			);
		}

		return $providers;
	}

	public function get_selected_provider(): ShipmentTrackingProvider {
		return isset( $this->providers[ $this->selected_provider ] )
			? $this->providers[ $this->selected_provider ]
			: new NullShipmentTracking();
	}

	public function get_tracking_data( \WC_Order $order ): ShipmentTrackingData {
		return $this->get_selected_provider()->get_tracking_data( $order );
	}
}
