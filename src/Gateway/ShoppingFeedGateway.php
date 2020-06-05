<?php

namespace ShoppingFeed\ShoppingFeedWC\Gateway;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

use WC_Payment_Gateway_CC;


/**
 * ShoppingFeed WooCommerce Gateway.
 *
 * @package ShoppingFeed\Gateway
 */
class ShoppingFeedGateway extends WC_Payment_Gateway_CC {

	public function __construct() {
		$this->id                 = 'shopping-feed';
		$this->icon               = '';
		$this->has_fields         = false;
		$this->method_title       = 'ShoppingFeed Gateway';
		$this->method_description = 'ShoppingFeed Gateway';

		$this->supports = array(
			'products',
		);
	}

	/**
	 * Check if this gateway is enabled
	 */
	public function is_available() {
		//only available on BO
		return is_admin();
	}
}
