<?php

namespace ShoppingFeed\ShoppingFeedWC\Orders\Order;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

/**
 * Class Address
 * @package ShoppingFeed\Orders\Order
 */
class Address {

	/**
	 * @var array $sf_address
	 */
	private $sf_address;

	/**
	 * Address constructor.
	 *
	 * @param $sf_address array
	 */
	public function __construct( $sf_address ) {
		$this->sf_address = $sf_address;
	}

	public function get_formatted_address() {
		return [
			'first_name' => $this->get_first_name(),
			'last_name'  => $this->get_last_name(),
			'company'    => $this->get_company(),
			'email'      => $this->get_email(),
			'phone'      => $this->get_phone(),
			'address_1'  => $this->get_address1(),
			'address_2'  => $this->get_address2(),
			'city'       => $this->get_city(),
			'state'      => $this->get_state(),
			'postcode'   => $this->get_postcode(),
			'country'    => $this->get_country(),
		];
	}

	private function get_first_name() {
		return ! empty( $this->sf_address['firstName'] ) ? $this->sf_address['firstName'] : '_';
	}

	private function get_last_name() {
		return ! empty( $this->sf_address['lastName'] ) ? $this->sf_address['lastName'] : '_';
	}

	private function get_company() {
		return ! empty( $this->sf_address['company'] ) ? $this->sf_address['company'] : '';
	}

	private function get_email() {
		return ! empty( $this->sf_address['email'] ) ? $this->sf_address['email'] : '';
	}

	private function get_phone() {
		return ! empty( $this->sf_address['mobilePhone'] ) ? $this->sf_address['mobilePhone'] : $this->sf_address['phone'];
	}

	private function get_address1() {
		return ! empty( $this->sf_address['street'] ) ? $this->sf_address['street'] : '';
	}

	private function get_address2() {
		return ! empty( $this->sf_address['street2'] ) ? $this->sf_address['street2'] : '';
	}

	private function get_city() {
		return ! empty( $this->sf_address['city'] ) ? $this->sf_address['city'] : '';
	}

	private function get_state() {
		return ! empty( $this->sf_address['state'] ) ? $this->sf_address['state'] : '';
	}

	private function get_postcode() {
		return ! empty( $this->sf_address['postalCode'] ) ? $this->sf_address['postalCode'] : '';
	}

	private function get_country() {
		return ! empty( $this->sf_address['country'] ) ? $this->sf_address['country'] : '';
	}
}
