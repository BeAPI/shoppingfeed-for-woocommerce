<?php

namespace ShoppingFeed\ShoppingFeedWC\ShipmentTracking;

interface ShipmentTrackingProvider {

	/**
	 * Get provider id.
	 *
	 * @return string
	 */
	public function id(): string;

	/**
	 * Get provider display name.
	 *
	 * @return string
	 */
	public function name(): string;

	/**
	 * Check if the provider is available.
	 *
	 * @return bool
	 */
	public function is_available(): bool;

	/**
	 * Return tracking data from the provider for an order.
	 *
	 * @param \WC_Order $order
	 *
	 * @return ShipmentTrackingData
	 */
	public function get_tracking_data( \WC_Order $order ): ShipmentTrackingData;
}
