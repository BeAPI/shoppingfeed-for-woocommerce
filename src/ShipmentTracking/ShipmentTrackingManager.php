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

	/**
	 * Instantiate an `ShipmentTrackingManager`.
	 *
	 * @param array $shipping_options
	 *
	 * @return self
	 */
	public static function create( array $shipping_options = [] ): self {
		$manager = new self();

		$providers = [
			new ShoppingfeedAdvanced(),
			new AdvancedShipmentTracking(),
			new PhWoocommerceShipmentTracking(),
		];

		/**
		 * Filter list of available shipment tracking providers.
		 *
		 * @since 6.8
		 *
		 * @param ShipmentTrackingProvider[] $providers list of providers
		 */
		$providers = apply_filters( 'sf_shipment_tracking_providers', $providers );
		array_map( [ $manager, 'register_provider' ], $providers );

		if ( isset( $shipping_options['tracking_provider'] ) ) {
			$manager->selected_provider = $shipping_options['tracking_provider'];
		}

		return $manager;
	}

	/**
	 * Register provider.
	 *
	 * @param ShipmentTrackingProvider $provider
	 *
	 * @return void
	 */
	public function register_provider( $provider ): void {
		if ( is_a( $provider, ShipmentTrackingProvider::class ) ) {
			$this->providers[ $provider->id() ] = $provider;
		}
	}

	/**
	 * Get a list of providers.
	 *
	 * @param bool $available_only only include available providers.
	 *
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

	/**
	 * Get the chosen provider.
	 *
	 * @param bool $check_availability ensure selected provider is available, use default one if not.
	 *
	 * @return ShipmentTrackingProvider
	 */
	public function get_selected_provider( bool $check_availability = true ): ShipmentTrackingProvider {
		$provider = isset( $this->providers[ $this->selected_provider ] )
			? $this->providers[ $this->selected_provider ]
			: new NullShipmentTracking();

		if ( $check_availability ) {
			$provider = $provider->is_available() ? $provider : new NullShipmentTracking();
		}

		return $provider;
	}

	/**
	 * Get tracking data for an order.
	 *
	 * @param \WC_Order $order
	 *
	 * @return ShipmentTrackingData
	 */
	public function get_tracking_data( \WC_Order $order ): ShipmentTrackingData {
		return $this->get_selected_provider()->get_tracking_data( $order );
	}
}
