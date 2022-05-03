<?php
namespace ShoppingFeed\ShoppingFeedWC\Addons;

use ShoppingFeed\Sdk\Api\Order\OrderResource;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

/**
 * Trait Marketplace
 * @package ShoppingFeed\ShoppingFeedWC\Addons
 */
trait Marketplace {
	/**
	 * @param $sf_order OrderResource
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
	 * @param $sf_order OrderResource
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
	 * @param $sf_order OrderResource
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
	 * @param $sf_order OrderResource
	 *
	 * @return bool
	 */
	private function is_mano_mano( $sf_order ) {
		return (
			strtoupper( $sf_order->getChannel()->getName() ) === 'ManoMano' ||
			$sf_order->getChannel()->getId() === 259
		);
	}

	/**
	 * Check if the current SF order is from the Zalando marketplace
	 *
	 * @param $sf_order OrderResource
	 *
	 * @return bool
	 * @author Stéphane Gillot
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

}
