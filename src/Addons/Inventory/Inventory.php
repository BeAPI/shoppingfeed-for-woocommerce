<?php

namespace ShoppingFeed\ShoppingFeedWC\Addons\Inventory;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

use ShoppingFeed\ShoppingFeedWC\Addons\Inventory\Marketplaces\Amazon;
use ShoppingFeed\ShoppingFeedWC\Addons\Inventory\Marketplaces\Cdiscount;
use ShoppingFeed\ShoppingFeedWC\Addons\Inventory\Marketplaces\MonoMono;

class Inventory {
	/**
	 * @var Amazon
	 */
	private $amazon;

	/**
	 * @var Cdiscount
	 */
	private $cdiscount;

	/**
	 * @var MonoMono
	 */
	private $mono_mono;

	public function __construct() {
		$this->amazon    = new Amazon();
		$this->cdiscount = new Cdiscount();
		$this->mono_mono = new MonoMono();
	}
}
