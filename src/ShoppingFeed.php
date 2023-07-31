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
use ShoppingFeed\ShoppingFeedWC\Feed\Generator;
use ShoppingFeed\ShoppingFeedWC\Query\Query;
use ShoppingFeed\ShoppingFeedWC\Url\Rewrite;

/**
 * Class ShoppingFeed to init plugin
 * @psalm-consistent-constructor
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
	 * @throws \Exception
	 */
	public function __wakeup() {
		throw new \Exception( 'Cannot serialize singleton' );
	}

	/**
	 * ShoppingFeed constructor.
	 */
	private function __construct() {
		//Check Compatibility
		if ( ! $this->check_compatibility() ) {
			return;
		}

		$this->actions = new WoocommerceActions();

		//Check Upgrade
		add_action( 'init', array( $this, 'do_migration' ), 25 );
		if ( ! $this->check_upgrade() ) {
			return;
		}

		$this->query   = new Query();
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
			self::add_sf_directory();
			Actions::register_feed_generation();
			Actions::register_get_orders();
		}
		flush_rewrite_rules();
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
		flush_rewrite_rules();
	}

	/**
	 * Clean Plugin Options & Dir
	 * - Remove all plugin options
	 * - Remove all plugin files
	 * - Flush rewrite rules
	 * @return void
	 */
	public static function uninstall() {
		delete_option( Options::SF_ACCOUNT_OPTIONS );
		delete_option( Options::SF_FEED_OPTIONS );
		delete_option( Options::SF_SHIPPING_OPTIONS );
		delete_option( Options::SF_ORDERS_OPTIONS );
		delete_option( Options::SF_CARRIERS );
		delete_option( Generator::SF_FEED_LAST_GENERATION_DATE );
		delete_option( SF_DB_VERSION_SLUG );
		delete_option( SF_UPGRADE_RUNNING );
		self::remove_sf_directory();
	}

	public static function remove_sf_directory() {
		rmdir( ShoppingFeedHelper::get_feed_directory() );
	}

	public static function add_sf_directory() {
		$directory = ShoppingFeedHelper::get_feed_directory();
		if ( ! is_dir( $directory ) ) {
			wp_mkdir_p( $directory );
		}
		$part_directory = ShoppingFeedHelper::get_feed_parts_directory();
		if ( ! is_dir( $part_directory ) ) {
			wp_mkdir_p( $part_directory );
		}
	}

	public function check_upgrade() {
		if ( ! ShoppingFeedHelper::sf_has_upgrade() || ShoppingFeedHelper::sf_new_customer() ) {
			return true;
		}

		//check if we have a running migration
		if ( ShoppingFeedHelper::is_upgrade_running() ) {
			add_action(
				'admin_notices',
				function () {
					?>
					<div id="message" class="notice notice-error">
						<p><?php esc_html_e( 'ShoppingFeed migration is running', 'shopping-feed' ); ?></p>
					</div>
					<?php
				}
			);

			return false;
		}

		add_action(
			'admin_notices',
			function () {
				?>
				<div id="message" class="notice notice-error">
					<p><?php esc_html_e( 'ShoppingFeed need to migrate old data', 'shopping-feed' ); ?></p>
					<a href="
					<?php
					echo esc_url(
						wp_nonce_url(
							add_query_arg(
								array(
									'sf_action' => 'sf_migrate_single',
								),
								admin_url()
							),
							'sf_migrate_single'
						)
					)
					?>
					">Migrate</a>
				</div>
				<?php
			}
		);

		return false;
	}

	public function do_migration(): void {
		//check if we need to do migration
		if (
			! empty( $_GET['sf_action'] ) &&
			'sf_migrate_single' === $_GET['sf_action'] &&
			isset( $_GET['_wpnonce'] ) &&
			wp_verify_nonce( wp_unslash( $_GET['_wpnonce'] ), 'sf_migrate_single' ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		) {
			$id_action = as_enqueue_async_action( 'sf_migrate_single_action', array(), 'sf_migrate_single' );
			update_option( SF_UPGRADE_RUNNING, $id_action );
			wp_safe_redirect( admin_url( '/' ), 302 );
			exit;
		}
	}
}
