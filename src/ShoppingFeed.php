<?php

namespace ShoppingFeed\ShoppingFeedWC;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

use ShoppingFeed\ShoppingFeedWC\Actions\Actions;
use ShoppingFeed\ShoppingFeedWC\Addons\Addons;
use ShoppingFeed\ShoppingFeedWC\Admin\Metabox;
use ShoppingFeed\ShoppingFeedWC\Admin\Notices;
use ShoppingFeed\ShoppingFeedWC\Admin\Options;
use ShoppingFeed\ShoppingFeedWC\Admin\WoocommerceActions;
use ShoppingFeed\ShoppingFeedWC\Admin\WoocommerceFilters;
use ShoppingFeed\ShoppingFeedWC\Query\Query;
use ShoppingFeed\ShoppingFeedWC\Url\Rewrite;

/**
 * Class ShoppingFeed to init plugin
 */
class ShoppingFeed {

	/**
	 * Custom Query
	 * @var Query
	 */
	private $query;

	/**
	 * Custom woocommerce actions
	 *
	 * @var WoocommerceActions
	 */
	private $actions;


	/**
	 * Custom woocommerce filters
	 *
	 * @var WoocommerceFilters
	 */
	private $filters;


	/**
	 * SF Admin Notices
	 *
	 * @var Notices
	 */
	private $notices;

	/**
	 * SF Options
	 *
	 * @var Options
	 */
	private $options;

	/**
	 * SF Rewrite
	 *
	 * @var Rewrite
	 */
	private $rewrite;

	/**
	 * SF Addons
	 * @var Addons;
	 */
	private $addons;

	/**
	 * SF Metabox
	 * @var Metabox;
	 */
	private $metabox;

	/**
	 * @var ShoppingFeed
	 */
	private static $instance;

	/**
	 * Get the singleton instance.
	 *
	 * @return ShoppingFeed
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new static();
		}

		return self::$instance;
	}

	/**
	 * Singleton instance can't be cloned.
	 */
	private function __clone() {
	}

	/**
	 * Singleton instance can't be serialized.
	 */
	private function __wakeup() {
	}

	/**
	 * ShoppingFeed constructor.
	 */
	private function __construct() {
		//Check Compatibility
		if ( ! $this->check_compatibility() ) {
			return;
		}

		$this->query   = new Query();
		$this->actions = new WoocommerceActions();
		$this->filters = new WoocommerceFilters();
		$this->notices = new Notices();
		$this->options = new Options();
		$this->addons  = new Addons();
		$this->metabox = new Metabox();
		$this->rewrite = new Rewrite();

		//Init Payment Gateway
		add_action( 'woocommerce_payment_gateways', array( $this, 'register_sf_gateway' ) );

		//Add settings link
		add_filter( 'plugin_action_links_' . SF_PLUGIN_BASENAME, array( $this, 'plugin_action_links' ) );
	}

	/**
	 * Register ShoppingFeed gateway.
	 *
	 * @param $methods
	 *
	 * @return array
	 */
	public function register_sf_gateway( $methods ) {
		$methods[] = __NAMESPACE__ . '\\Gateway\\ShoppingFeedGateway';

		return $methods;
	}

	/**
	 * Check if the plugin is compatible
	 */
	public function check_compatibility() {
		// Bail early if WooCommerce is not activated
		if ( ! defined( 'WC_VERSION' ) ) {
			add_action(
				'admin_notices',
				function () {
					?>
					<div id="message" class="notice notice-error">
						<p><?php esc_html_e( 'ShoppingFeed requires an active version of WooCommerce', 'shopping-feed' ); ?></p>
					</div>
					<?php
				}
			);

			return false;
		}

		// Bail early if WooCommerce version is not compatible
		if ( ShoppingFeedHelper::is_pre_38() ) {
			add_action(
				'admin_notices',
				function () {
					?>
					<div id="message" class="notice notice-error">
						<p><?php esc_html_e( 'ShoppingFeed requires WooCommerce version 3.8 at least', 'shopping-feed' ); ?></p>
					</div>
					<?php
				}
			);

			return false;
		}

		return true;
	}

	/**
	 * Add additional action links.
	 *
	 * @param array $links
	 *
	 * @return array
	 */
	public function plugin_action_links( $links = array() ) {
		$plugin_links = array(
			'<a href="' . esc_url( ShoppingFeedHelper::get_setting_link() ) . '">' . esc_html__( 'Settings', 'shopping-feed' ) . '</a>',
		);

		return array_merge( $plugin_links, $links );
	}

	/**
	 * Activate the plugin
	 * - Register required actions
	 * @return void
	 */
	public static function activate() {
		if ( defined( 'WC_VERSION' ) ) {
			Actions::register_feed_generation();
			Actions::register_get_orders();
		}
	}

	/**
	 * Activate the plugin
	 * - Remove all plugin actions
	 * - Flush rewrite rules
	 * @return void
	 */
	public static function deactivate() {
		if ( defined( 'WC_VERSION' ) ) {
			Actions::clean_feed_generation();
			Actions::clean_get_orders();
		}
		flush_rewrite_rules( true );
	}
}
