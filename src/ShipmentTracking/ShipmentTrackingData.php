<?php

namespace ShoppingFeed\ShoppingFeedWC\ShipmentTracking;

class ShipmentTrackingData {

	private $tracking_data = [];

	/**
	 * Add tracking data.
	 *
	 * @param string $tracking_number
	 * @param string $tracking_link
	 *
	 * @return self
	 */
	public function add_tracking_data( string $tracking_number, string $tracking_link = '' ): self {
		$this->tracking_data[ $tracking_number ] = [
			'tracking_number' => $tracking_number,
			'tracking_link'   => $tracking_link,
		];

		return $this;
	}

	/**
	 * Check if tracking data are available.
	 *
	 * @return bool
	 */
	public function has_tracking_data(): bool {
		return ! empty( $this->tracking_data );
	}

	/**
	 * Get an array of tracking data.
	 *
	 * @return array{int, string[]}
	 */
	public function get_tracking_data(): array {
		return $this->tracking_data;
	}

	/**
	 * Get a list of tracking numbers.
	 *
	 * @return string[]
	 */
	public function get_tracking_numbers(): array {
		return wp_list_pluck( $this->tracking_data, 'tracking_number' );
	}

	/**
	 * Get a list of tracking links.
	 *
	 * @return string[]
	 */
	public function get_tracking_links(): array {
		return wp_list_pluck( $this->tracking_data, 'tracking_link' );
	}
}
