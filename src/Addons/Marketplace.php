<?php

namespace ShoppingFeed\ShoppingFeedWC\Addons;

use ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Api\Order\OrderResource;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

/**
 * Trait Marketplace
 * @package ShoppingFeed\ShoppingFeedWC\Addons
 */
trait Marketplace {
	/**
	 * @param OrderResource $sf_order
	 *
	 * @return bool
	 */
	private function is_cdiscount( $sf_order ) {
		return (
			strtoupper( $sf_order->getChannel()->getName() ) === 'CDISCOUNT' ||
			$sf_order->getChannel()->getId() === 111
		);
	}

	/**
	 * @param OrderResource $sf_order
	 *
	 * @return bool
	 */
	private function is_rdc( $sf_order ) {
		return (
			strtoupper( $sf_order->getChannel()->getName() ) === 'RDC' ||
			$sf_order->getChannel()->getId() === 51
		);
	}

	/**
	 * @param OrderResource $sf_order
	 *
	 * @return bool
	 */
	private function is_amazon( $sf_order ) {
		return (
			strtoupper( $sf_order->getChannel()->getName() ) === 'AMAZON' ||
			$sf_order->getChannel()->getId() === 66
		);
	}

	/**
	 * @param OrderResource $sf_order
	 *
	 * @return bool
	 */
	private function is_mano_mano( $sf_order ) {
		return (
			strtoupper( $sf_order->getChannel()->getName() ) === 'MANOMANO' ||
			$sf_order->getChannel()->getId() === 259
		);
	}

	/**
	 * Check if the current SF order is from the Zalando marketplace
	 *
	 * @param OrderResource $sf_order
	 *
	 * @return bool
	 */
	private function is_zalando( $sf_order ) {

		$name = $sf_order->getChannel()->getName();
		$id   = $sf_order->getChannel()->getId();

		if ( empty( $name ) || empty( $id ) ) {
			return false;
		}

		return (
			( 'ZALANDO' === strtoupper( substr( $name, 0, 7 ) ) ) &&
			( substr( $name, 0, 7 ) . $id === $name )
		);
	}

	/**
	 * Check if the order is fulfilled by Amazon.
	 *
	 * @param OrderResource $sf_order
	 *
	 * @return bool
	 */
	private function is_fulfilled_by_amazon( $sf_order ) {
		return $this->is_amazon( $sf_order ) && 'afn' === strtolower( $sf_order->getPaymentInformation()['method'] );
	}

	/**
	 * Check if the order is fulfilled by CDiscount.
	 *
	 * @param OrderResource $sf_order
	 *
	 * @return bool
	 */
	private function is_fulfilled_by_cdiscount( $sf_order ) {
		return $this->is_cdiscount( $sf_order ) && 'clogistique' === strtolower( $sf_order->getPaymentInformation()['method'] );
	}

	/**
	 * Check if the order is fulfilled by ManoMano.
	 *
	 * @param OrderResource $sf_order
	 *
	 * @return bool
	 */
	private function is_fulfilled_by_manomano( $sf_order ) {
		return $this->is_mano_mano( $sf_order ) && 'epmm' === strtolower( $sf_order->toArray()['additionalFields']['env'] );
	}

	/**
	 * Check if the order is fulfilled by the channel.
	 *
	 * @param OrderResource $sf_order
	 *
	 * @return bool
	 */
	private function is_fulfilled_by_channel( $sf_order ) {
		return ! empty( $sf_order->toArray()['fulfilledBy'] ) && 'channel' === strtolower( $sf_order->toArray()['fulfilledBy'] );
	}

	/**
	 * Check if the order is fulfilled by a marketplace.
	 *
	 * @param OrderResource $sf_order
	 *
	 * @return bool
	 */
	private function is_fulfilled_by_marketplace( $sf_order ) {

		if ( $this->is_fulfilled_by_amazon( $sf_order ) ) {
			return true;
		}

		if ( $this->is_fulfilled_by_cdiscount( $sf_order ) ) {
			return true;
		}

		if ( $this->is_fulfilled_by_manomano( $sf_order ) ) {
			return true;
		}

		if ( $this->is_fulfilled_by_channel( $sf_order ) ) {
			return true;
		}

		return false;
	}
}
