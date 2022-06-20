<?php

namespace ShoppingFeed\ShoppingFeedWC\Addons;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

use ShoppingFeed\ShoppingFeedWC\Addons\Inventory\Inventory;
use ShoppingFeed\ShoppingFeedWC\Addons\Marketplaces\Marketplaces;
use ShoppingFeed\ShoppingFeedWC\Addons\Plugins\ASTPlugin\ASTPlugin;
use ShoppingFeed\ShoppingFeedWC\Addons\Plugins\ChainedProductsPlugin\ChainedProducts;
use ShoppingFeed\ShoppingFeedWC\Addons\Plugins\PhWoocommerceShipmentTrackingProPlugin\PhWoocommerceShipmentTrackingProPlugin;
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
	 * @var Marketplaces
	 */
	private $marketplaces;

	/**
	 * @var ChainedProducts
	 */
	private $chained_products_plugin;

	/**
	 * @var ASTPlugin
	 */
	private $ast_plugin;

	/**
	 * @var PhWoocommerceShipmentTrackingProPlugin
	 */
	private $woocommerce_shipment_tracking_pro;

	public function __construct() {
		$this->shipping                          = new Shipping();
		$this->inventory                         = new Inventory();
		$this->marketplaces                      = new Marketplaces();
		$this->chained_products_plugin           = new ChainedProducts();
		$this->ast_plugin                        = new ASTPlugin();
		$this->woocommerce_shipment_tracking_pro = new PhWoocommerceShipmentTrackingProPlugin();
	}
}
