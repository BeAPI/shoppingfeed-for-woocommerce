<?php

namespace ShoppingFeed\ShoppingFeedWC\Addons;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

use ShoppingFeed\ShoppingFeedWC\Addons\Inventory\Inventory;
use ShoppingFeed\ShoppingFeedWC\Addons\Marketplaces\Marketplaces;
use ShoppingFeed\ShoppingFeedWC\Addons\Shipping\Shipping;

class Addons {

	/**
	 * Shipping class
	 * @var Shipping
	 */
	private $shipping;

	/**
	 * Shipping class
	 * @var Inventory
	 */
	private $inventory;

	/**
	 * Shipping class
	 * @var Marketplaces
	 */
	private $marketplaces;

	public function __construct() {
		$this->shipping     = new Shipping();
		$this->inventory    = new Inventory();
		$this->marketplaces = new Marketplaces();
	}
}
