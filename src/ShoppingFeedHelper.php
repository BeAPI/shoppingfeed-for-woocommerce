<?php

namespace ShoppingFeed\ShoppingFeedWC;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

use ShoppingFeed\ShoppingFeedWC\Admin\Options;
use ShoppingFeed\ShoppingFeedWC\ShipmentTracking\ShipmentTrackingManager;
use ShoppingFeed\ShoppingFeedWC\ShipmentTracking\ShipmentTrackingProvider;
use ShoppingFeed\ShoppingFeedWC\Feed\Uri;
use ShoppingFeed\ShoppingFeedWC\Url\Rewrite;
use WC_Logger;


/**
 * Define All needed methods
 * Helper class.
 *
 * @package ShoppingFeed
 */
class ShoppingFeedHelper {

	/**
	 * @var ShoppingFeedHelper
	 */
	private static $instance;

	/**
	 * Get the singleton instance.
	 *
	 * @return ShoppingFeedHelper
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Check if current WooCommerce version is below 3.8.0
	 *
	 * @return bool
	 */
	public static function is_pre_38() {
		return version_compare( self::get_wc_version(), '3.8.0', '<' );
	}

	/**
	 * Return the WC version
	 * @return string
	 */
	public static function get_wc_version() {
		return WC()->version;
	}

	/**
	 * Return the feed's directory
	 * @return string
	 */
	public static function get_feed_directory() {
		return SF_FEED_DIR;
	}

	/**
	 * Return the feed's parts directory
	 * @return string
	 */
	public static function get_feed_parts_directory() {
		return SF_FEED_PARTS_DIR;
	}

	/**
	 * @return string
	 */
	public static function get_feed_skeleton() {
		return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<catalog>
    <metadata>
        <platform>WooCommerce:5.1.0</platform>
        <agent>shopping-feed-generator:1.0.0</agent>
        <startedAt/>
        <finishedAt/>
        <invalid/>
        <ignored/>
        <written/>
    </metadata>
</catalog>
XML;
	}

	/**
	 * Return the feed's file name
	 * @return string
	 */
	public static function get_feed_filename() {
		return 'products';
	}

	/**
	 * Return the feed's public url
	 * @return string
	 */
	public static function get_public_feed_url() {
		if ( ! empty( get_option( 'permalink_structure' ) ) ) {
			return sprintf( '%s/%s/', get_home_url(), self::get_public_feed_endpoint() );
		}

		return sprintf( '%s?%s', get_home_url(), self::get_public_feed_endpoint() );
	}

	/**
	 * Return the feed's public endpoint
	 * @return string
	 */
	public static function get_public_feed_endpoint() {
		global $wp_rewrite;

		return $wp_rewrite->root . Rewrite::FEED_PARAM;
	}

	/**
	 * Return the feed's public url with new generation
	 * @return string
	 */
	public static function get_public_feed_url_with_generation() {
		if ( ! empty( get_option( 'permalink_structure' ) ) ) {
			return sprintf( '%1$s/%2$s/?version=%3$s', get_home_url(), self::get_public_feed_endpoint(), time() );
		}

		return sprintf( '%1$s?%2$s&version=%3$s', get_home_url(), self::get_public_feed_endpoint(), time() );
	}

	/**
	 * @param $uri
	 */
	public static function get_tmp_uri( $uri ) {
		return str_replace( '.xml', '_tmp.xml', $uri );
	}

	/**
	 * Return SF Configuration for Account
	 * @return array
	 */
	public static function get_sf_account_options() {
		return get_option( Options::SF_ACCOUNT_OPTIONS, array() );
	}

	/**
	 * Return SF Configuration for Yoast
	 * @return array
	 */
	public static function get_sf_yoast_options() {

		$yoast_options = get_option( Options::SF_YOAST_OPTIONS, [] );

		return wp_parse_args(
			$yoast_options,
			[
				'use_principal_categories' => '1',
			]
		);
	}

	/**
	 * Set SF Configuration for Account
	 * @return bool
	 */
	public static function set_sf_account_options( $option ) {
		return update_option( Options::SF_ACCOUNT_OPTIONS, $option );
	}

	/**
	 * Get SF account by store id.
	 *
	 * @param int|string $account_id
	 *
	 * @return array
	 */
	public static function get_sf_account_credentials( $account_id ) {
		$account_options = self::get_sf_account_options();
		$index           = array_search( (int) $account_id, array_column( $account_options, 'sf_store_id' ), true );
		if ( false === $index || empty( $account_options[ $index ] ) ) {
			return array();
		}

		return $account_options[ $index ];
	}

	/**
	 * Get SF account by username.
	 *
	 * @param string $username
	 *
	 * @return array
	 */
	public static function get_sf_account_credentials_by_username( $username ) {
		$account_options = self::get_sf_account_options();
		$index           = array_search( $username, array_column( $account_options, 'username' ), true );
		if ( false === $index || empty( $account_options[ $index ] ) ) {
			return array();
		}

		return $account_options[ $index ];
	}


	/**
	 * Return SF categories to export
	 * @return array
	 */
	public static function get_sf_feed_export_categories() {
		$categories = self::get_sf_feed_options( 'categories' );
		if ( empty( $categories ) ) {
			return array();
		}

		return $categories;
	}

	/**
	 * Return SF Configuration for Feed Generation
	 *
	 * @param string $param
	 *
	 * @return mixed|void
	 */
	public static function get_sf_feed_options( $param = '' ) {
		$feed_options = get_option( Options::SF_FEED_OPTIONS );
		if ( empty( $param ) ) {
			return $feed_options;
		}

		if ( ! isset( $feed_options[ $param ] ) ) {
			return false;
		}

		return $feed_options[ $param ];
	}

	/**
	 * Return identifier for product : id|sku
	 * @return string
	 */
	public static function get_sf_feed_product_identifier() {
		$product_identifier = self::get_sf_feed_options( 'product_identifier' );
		if ( empty( $product_identifier ) ) {
			return 'id';
		}

		return $product_identifier;
	}

	/**
	 * Should out of stock products be included in the feed ?
	 *
	 * @return bool true if the products should be in the feed, false otherwise.
	 */
	public static function show_out_of_stock_products_in_feed() {
		return 'on' === self::get_sf_feed_options( 'out_of_stock_products_in_feed' );
	}

	/**
	 * Return display mode for category
	 * @return string
	 */
	public static function get_sf_feed_category_display_mode() {
		$display_mode = self::get_sf_feed_options( 'category_display_mode' );
		if ( empty( $display_mode ) ) {
			return 'normal';
		}

		return $display_mode;
	}

	/**
	 * Return SF default shipping zone id if exist
	 * @return bool|int
	 */
	public static function get_sf_default_shipping_zone() {
		// Ensure retro compatibility
		$shipping_configuration = self::get_sf_feed_options();
		if ( is_array( $shipping_configuration ) && isset( $shipping_configuration['zone'] ) && ! empty( $shipping_configuration['zone'] ) ) {
			return (int) $shipping_configuration['zone'];
		}

		$shipping_configuration = self::get_sf_shipping_options();
		if ( ! is_array( $shipping_configuration ) || ! isset( $shipping_configuration['zone'] ) ) {
			return false;
		}

		//if none option is selected
		if ( empty( $shipping_configuration['zone'] ) ) {
			return false;
		}

		return (int) $shipping_configuration['zone'];
	}

	/**
	 * Return SF Configuration for Shipping
	 *
	 * @param string $param
	 *
	 * @return mixed|void
	 */
	public static function get_sf_shipping_options( $param = '' ) {
		$shipping_options = get_option( Options::SF_SHIPPING_OPTIONS );
		if ( empty( $param ) ) {
			return $shipping_options;
		}

		if ( ! isset( $shipping_options[ $param ] ) ) {
			return false;
		}

		return $shipping_options[ $param ];
	}

	/**
	 * Return SF default shipping fees if exist
	 * @return float
	 */
	public static function get_sf_default_shipping_fees() {
		$shipping_configuration = self::get_sf_feed_options();
		if ( is_array( $shipping_configuration ) && ! empty( $shipping_configuration['fees'] ) && is_numeric( $shipping_configuration['fees'] ) ) {
			return (float) $shipping_configuration['fees'];
		}

		$shipping_configuration = self::get_sf_feed_options( 'shipping' );
		if ( ! is_array( $shipping_configuration ) || empty( $shipping_configuration['fees'] ) || ! is_numeric( $shipping_configuration['fees'] ) ) {
			return 0;
		}

		return (float) $shipping_configuration['fees'];
	}

	/**
	 * Return SF feed generation frequency
	 * default: 6 hours
	 * @return int
	 */
	public static function get_sf_feed_generation_frequency() {
		$frequency = self::get_sf_feed_options( 'frequency' );
		if ( empty( $frequency ) ) {
			return 6 * HOUR_IN_SECONDS;
		}

		return $frequency;
	}

	/**
	 * Return SF feed part size
	 * default: 200 product per file
	 * @return int
	 */
	public static function get_sf_part_size() {
		$part_size = self::get_sf_feed_options( 'part_size' );
		if ( empty( $part_size ) ) {
			return 200;
		}

		return (int) $part_size;
	}

	/**
	 * Return action of each wc status
	 * @return mixed|void
	 */
	public static function get_sf_statuses_actions() {
		$orders_options = self::get_sf_orders_options();
		if ( ! is_array( $orders_options ) ) {
			return array();
		}
		if ( empty( $orders_options['statuses_actions'] ) ) {
			return array();
		}

		return $orders_options['statuses_actions'];
	}

	/**
	 * Return SF orders configuration
	 * @return mixed|void
	 */
	public static function get_sf_orders_options() {
		return get_option( Options::SF_ORDERS_OPTIONS );
	}

	/**
	 * Return SF default order status
	 * @return string
	 */
	public static function get_sf_default_order_status() {
		$orders_options = self::get_sf_orders_options();
		if ( ! is_array( $orders_options ) ) {
			return 'wc-on-hold';
		}
		if ( empty( $orders_options['default_status'] ) ) {
			return 'wc-on-hold';
		}

		return $orders_options['default_status'];
	}

	/**
	 * Return SF fulfilled by marketplace order status.
	 * @return string
	 */
	public static function get_sf_fulfilled_by_channel_order_status() {
		$orders_options = self::get_sf_orders_options();
		if ( ! is_array( $orders_options ) ) {
			return 'wc-completed';
		}

		return ! empty( $orders_options['fulfilled_by_marketplace_order_status'] )
			? $orders_options['fulfilled_by_marketplace_order_status']
			: 'wc-completed';
	}

	/**
	 * Return SF orders import
	 * default: 15 MINUTES
	 * @return int
	 */
	public static function get_sf_orders_import_frequency() {
		$orders_options = self::get_sf_orders_options();
		if ( ! is_array( $orders_options ) ) {
			return 15 * MINUTE_IN_SECONDS;
		}

		if ( empty( $orders_options['import_frequency'] ) ) {
			return 15 * MINUTE_IN_SECONDS;
		}

		return $orders_options['import_frequency'];
	}

	/**
	 * Return zones with related shipping methods
	 * @return array
	 */
	public static function get_zones_with_shipping_methods() {
		$shipping_methods = array();
		$shipping_zones   = \WC_Shipping_Zones::get_zones();
		if ( empty( $shipping_zones ) ) {
			return $shipping_methods;
		}
		$all_shipping_methods = array();
		foreach ( $shipping_zones as $shipping_zone ) {
			$shipping_zone    = new \WC_Shipping_Zone( $shipping_zone['id'] );
			$shipping_methods = $shipping_zone->get_shipping_methods();

			if ( empty( $shipping_methods ) ) {
				continue;
			}

			$_shipping_methods = array();
			foreach ( $shipping_methods as $shipping_method ) {
				$_shipping_methods[] = array(
					'method_rate_id' => $shipping_method->id,
					'method_id'      => $shipping_method->instance_id,
					'method_title'   => $shipping_method->title,
				);
			}

			$all_shipping_methods[] = array(
				'zone_id'   => $shipping_zone->get_id(),
				'zone_name' => $shipping_zone->get_zone_name(),
				'methods'   => $_shipping_methods,
			);
		}

		return $all_shipping_methods;
	}

	/**
	 * Get WC Shipping from SF carrier
	 *
	 * @param $name
	 *
	 * @return array
	 */
	public static function get_wc_shipping_from_sf_carrier( $sf_carrier ) {
		$sf_carrier_id = self::get_sf_carrier_id( $sf_carrier );
		if ( empty( $sf_carrier_id ) && ! empty( $sf_carrier ) ) {
			$sf_carrier_id = self::add_sf_carrier( $sf_carrier );
		}

		$matching_shipping_method_list = self::get_matching_shipping_method_list();
		if ( empty( $matching_shipping_method_list[ $sf_carrier_id ] ) ) {
			if ( is_array( self::get_default_shipping_method() ) ) {
				self::add_matching_shipping_method( $sf_carrier_id, self::get_default_shipping_method() );

				return self::get_default_shipping_method();
			}

			return array();
		}

		return json_decode( $matching_shipping_method_list[ $sf_carrier_id ], true );
	}

	/**
	 * Get SF carrier id
	 *
	 * @param $name
	 *
	 * @return int
	 */
	public static function get_sf_carrier_id( $name ) {
		$sf_carriers = self::get_sf_carriers();
		if ( empty( $sf_carriers ) ) {
			return 0;
		}

		$sf_carrier_id = array_search( $name, $sf_carriers, true );

		if ( ! is_int( $sf_carrier_id ) ) {
			return 0;
		}

		return $sf_carrier_id;
	}

	/**
	 * Return sf carriers
	 * @return array
	 */
	public static function get_sf_carriers() {
		$sf_carriers = get_option( Options::SF_CARRIERS );
		if ( empty( $sf_carriers ) || ! is_array( $sf_carriers ) ) {
			return array();
		}

		return $sf_carriers;
	}

	/**
	 * Add SF carrier
	 *
	 * @param $sf_carrier
	 */
	public static function add_sf_carrier( $sf_carrier ) {
		$sf_carriers = self::get_sf_carriers();
		$index       = count( $sf_carriers );
		++ $index;
		$sf_carriers[ (int) $index ] = $sf_carrier;
		update_option( Options::SF_CARRIERS, $sf_carriers );

		return $index;
	}

	/**
	 * Return matching shipping list
	 * @return array
	 */
	public static function get_matching_shipping_method_list() {
		$orders_options = self::get_sf_shipping_options();
		if ( empty( $orders_options ) || empty( $orders_options['matching_shipping_method'] ) ) {
			return array();
		}

		return (array) $orders_options['matching_shipping_method'];
	}

	/**
	 * Return Default shipping method
	 * @return array
	 */
	public static function get_default_shipping_method() {
		$shipping_options = self::get_sf_shipping_options();
		if ( empty( $shipping_options ) || empty( $shipping_options['default_shipping_method'] ) ) {
			return array();
		}

		return (array) json_decode( $shipping_options['default_shipping_method'], true );
	}

	/**
	 * Add new matching for sf carrier
	 *
	 * @param $carrier_id
	 * @param $method
	 *
	 * @return void
	 */
	public static function add_matching_shipping_method( $carrier_id, $method ) {
		if ( empty( $method ) || ! is_int( $carrier_id ) ) {
			return;
		}
		$sf_shipping_option = self::get_sf_shipping_options();
		if ( empty( $sf_shipping_option ) ) {
			return;
		}
		$method['sf_shipping']                                         = $carrier_id;
		$sf_shipping_option['matching_shipping_method'][ $carrier_id ] = wp_json_encode( $method );
		update_option( Options::SF_SHIPPING_OPTIONS, $sf_shipping_option );
	}

	/**
	 * Get SF Carrier from WC Shipping
	 *
	 * @param $wc_order \WC_Order
	 *
	 * @return string
	 */
	public static function get_sf_carrier_from_wc_shipping( $wc_order ) {
		/**
		 * Filter the value of the carrier for the order before it is retrieve.
		 *
		 * @param bool|string $pre The value to return instead of the value computed from
		 *                            the `sf_shipping` metadata.
		 * @param \WC_Order $wc_order The order object for the carrier data.
		 */
		$pre = apply_filters( 'pre_sf_carrier_from_wc_shipping', false, $wc_order );
		if ( false !== $pre ) {
			return $pre;
		}

		$sf_shipping = json_decode( $wc_order->get_meta( 'sf_shipping' ), true );
		if ( empty( $sf_shipping['sf_shipping'] ) ) {
			return $wc_order->get_shipping_method();
		}

		$sf_carrier_id = $sf_shipping['sf_shipping'];

		$default_shipping_method = self::get_default_shipping_method();
		$default_shipping_method = ! is_array( $default_shipping_method ) || empty( $default_shipping_method ) ? '' : $default_shipping_method['method_title'];

		$matching_shipping_method_list = self::get_matching_shipping_method_list();
		if ( empty( $matching_shipping_method_list[ $sf_carrier_id ] ) ) {
			return $default_shipping_method;
		}

		$matching_shipping_method = json_decode( $matching_shipping_method_list[ $sf_carrier_id ], true );
		if ( ! is_array( $matching_shipping_method ) && empty( $matching_shipping_method ) ) {
			return $default_shipping_method;
		}

		return (string) $matching_shipping_method['method_title'];
	}

	/**
	 * Add filter for category taxonomy
	 * default: product_cat
	 * @return string
	 */
	public static function wc_category_taxonomy() {
		return apply_filters( 'shopping_feed_custom_category_taxonomy', 'product_cat' );
	}

	/**
	 * Add filter for brand taxonomy
	 * @return string
	 */
	public static function wc_brand_taxonomy() {
		return apply_filters( 'shopping_feed_custom_brand_taxonomy', '' );
	}

	/**
	 * Add filter for product ean field meta key
	 *
	 * @param \WC_Product|false $wc_product
	 *
	 * @return string
	 */
	public static function wc_product_ean( $wc_product = false ) {
		return apply_filters( 'shopping_feed_custom_ean', '', $wc_product );
	}

	/**
	 * Add filter for products list query
	 * @return array
	 */
	public static function wc_products_custom_query_args() {
		return apply_filters( 'shopping_feed_products_custom_args', array() );
	}

	/**
	 * Add filter for orders statuses to import
	 * @return array
	 */
	public static function sf_order_statuses_to_import() {
		$default_statuses = [ 'waiting_shipment' ];
		$orders_options   = self::get_sf_orders_options();

		/**
		 * Add shipped status if importing fulfilled by marketplace orders
		 * @see https://support.beapi.fr/issues/60658
		 */
		if ( isset( $orders_options['import_order_fulfilled_by_marketplace'] ) && true === (bool) $orders_options['import_order_fulfilled_by_marketplace'] ) {
			$fullfilled_by_marketplace_statuses = [ 'shipped', 'refunded', 'cancelled' ];
			$default_statuses                   = array_merge( $default_statuses, $fullfilled_by_marketplace_statuses );
		}

		return apply_filters( 'shopping_feed_orders_to_import', $default_statuses );
	}

	/**
	 * Get tracking number for the order.
	 *
	 *  If the order has multiple tracking numbers, they'll be separated by coma.
	 *
	 * @param \WC_Order $wc_order
	 *
	 * @return string
	 */
	public static function wc_tracking_number( $wc_order ) {
		$manager = self::wc_tracking_provider_manager();

		$tracking_number = '';
		$tracking_data   = $manager->get_selected_provider()->get_tracking_data( $wc_order );
		if ( $tracking_data->has_tracking_data() ) {
			$tracking_number = implode( ',', $tracking_data->get_tracking_numbers() );
		}

		/**
		 * Filter order's tracking number.
		 *
		 * @param string $tracking_number
		 * @param \WC_Order $wc_order
		 */
		$filtered_tracking_number = apply_filters( 'shopping_feed_tracking_number', $tracking_number, $wc_order );

		/*
		 * Back-compat: ignore filtered value if it comes from ShoppingFeed Advanced.
		 *
		 * Previously, the ShoppingFeed Advanced addon used the filter `shopping_feed_tracking_number` to specify
		 * the meta key for retrieving tracking numbers. This functionality is now handled by the shipment tracking manager.
		 */
		if ( defined( 'TRACKING_NUMBER_FIELD_SLUG' ) && TRACKING_NUMBER_FIELD_SLUG === $filtered_tracking_number ) {
			$filtered_tracking_number = $tracking_number;
		}

		// Back-compat: handle case where the filter return a meta key.
		if ( $wc_order->meta_exists( $filtered_tracking_number ) ) {
			$filtered_tracking_number = (string) $wc_order->get_meta( $filtered_tracking_number );
		}

		return $filtered_tracking_number;
	}

	/**
	 * Get tracking link for the order.
	 *
	 * If the order has multiple tracking links, they'll be separated by coma.
	 *
	 * @param \WC_Order $wc_order
	 *
	 * @return string
	 */
	public static function wc_tracking_link( $wc_order ) {
		$manager = self::wc_tracking_provider_manager();

		$tracking_link = '';
		$tracking_data = $manager->get_selected_provider()->get_tracking_data( $wc_order );
		if ( $tracking_data->has_tracking_data() ) {
			$tracking_link = implode( ',', $tracking_data->get_tracking_links() );
		}

		/**
		 * Filter order's tracking link.
		 *
		 * @param string $tracking_link
		 * @param \WC_Order $wc_order
		 */
		$filtered_tracking_link = apply_filters( 'shopping_feed_tracking_link', $tracking_link, $wc_order );

		/*
		 * Back-compat: ignore filtered value if it comes from ShoppingFeed Advanced.
		 *
		 * Previously, the ShoppingFeed Advanced addon used the filter `shopping_feed_tracking_number` to specify
		 * the meta key for retrieving tracking links. This functionality is now handled by the shipment tracking manager.
		 */
		if ( defined( 'TRACKING_LINK_FIELD_SLUG' ) && TRACKING_LINK_FIELD_SLUG === $filtered_tracking_link ) {
			$filtered_tracking_link = $tracking_link;
		}

		// Back-compat: handle case where the filter return a meta key.
		if ( $wc_order->meta_exists( $filtered_tracking_link ) ) {
			$filtered_tracking_link = (string) $wc_order->get_meta( $filtered_tracking_link );
		}

		return $filtered_tracking_link;
	}

	public static function wc_tracking_provider_manager(): ShipmentTrackingManager {
		static $manager;
		if ( ! $manager ) {
			$shipping_options = self::get_sf_shipping_options();
			if ( ! is_array( $shipping_options ) ) {
				$shipping_options = [];
			}
			$manager = ShipmentTrackingManager::create( $shipping_options );
		}

		return $manager;
	}

	/**
	 * Default quantity if product quantity is unset
	 * @return int
	 */
	public static function get_default_product_quantity() {
		return 100;
	}

	/**
	 * Check if a running generation process
	 *
	 * @param $group
	 *
	 * @return bool
	 */
	public static function is_process_running( $group ) {
		$process = self::get_running_process( $group );

		return ! empty( $process );
	}

	/**
	 * Get running process list
	 *
	 * @param string $group
	 *
	 * @return array|int
	 */
	public static function get_running_process( $group ) {
		$action_scheduler = \ActionScheduler::store();

		return $action_scheduler->query_actions(
			array(
				'group'  => $group,
				'status' => [ \ActionScheduler_Store::STATUS_PENDING, \ActionScheduler_Store::STATUS_RUNNING ],
			)
		);
	}

	/**
	 * @param string $group
	 */
	public static function clean_process_running( $group ) {
		try {
			\ActionScheduler::store()->cancel_actions_by_group( $group );
		} catch ( \Exception $exception ) {
			self::get_logger()->error(
				sprintf(
					__( 'Cant remove running process', 'shopping-feed' ),
					$exception->getMessage()
				),
				array(
					'source' => 'shopping-feed',
				)
			);
		}
		wp_safe_redirect( self::get_setting_link(), 302 );
	}

	/**
	 * Return WC Logger
	 * @return WC_Logger
	 */
	public static function get_logger() {
		return wc_get_logger();
	}

	/**
	 * Return the settings link for plugin
	 * @return string
	 */
	public static function get_setting_link() {
		return admin_url( 'admin.php?page=shopping-feed' );
	}

	/**
	 * Check if the Site is compatible with addons
	 * @return bool
	 */
	public static function tracking_is_compatible_with_addons() {
		$shipping_options = self::get_sf_shipping_options();
		if ( empty( $shipping_options ) ) {
			return false;
		}

		return ! empty( $shipping_options['retrieval_mode'] ) && 'ADDONS' === $shipping_options['retrieval_mode'];
	}

	/**
	 * Check if new customer
	 * @return bool
	 */
	public static function sf_new_customer() {
		return empty( get_option( Options::SF_ACCOUNT_OPTIONS ) ) &&
		       empty( get_option( Options::SF_FEED_OPTIONS ) ) &&
		       empty( get_option( Options::SF_SHIPPING_OPTIONS ) ) &&
		       empty( get_option( Options::SF_ORDERS_OPTIONS ) );
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
	 * Check if we have an upgrade
	 * @return bool
	 */
	public static function sf_has_upgrade() {

		$db_version = get_option( SF_DB_VERSION_SLUG );

		return empty( $db_version ) || version_compare( $db_version, SF_DB_VERSION, '<' );
	}

	/**
	 * Check if we have a running upgrade
	 * @return bool
	 */
	public static function is_upgrade_running() {
		return ! empty( get_option( SF_UPGRADE_RUNNING ) );
	}

	/*
	 * End upgrade
	 */
	public static function end_upgrade() {
		delete_option( SF_UPGRADE_RUNNING );
		update_option( SF_DB_VERSION_SLUG, SF_DB_VERSION );
	}

	/**
	 * Get available languages
	 *
	 * @return string[]
	 */
	public static function get_available_languages() {
		$languages = [];
		if ( function_exists( '\pll_languages_list' ) ) {
			$languages = pll_languages_list(
				[
					'hide_empty' => false,
				]
			);
		} elseif ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
			$icl_languages = apply_filters( 'wpml_active_languages', null );
			if ( is_array( $icl_languages ) ) {
				$languages = wp_list_pluck( $icl_languages, 'language_code' );
			}
		}

		return $languages;
	}

	/**
	 * @return array
	 */
	public static function get_feeds_urls() {
		$urls = [];
		if ( ! empty( self::get_available_languages() ) ) {
			foreach ( self::get_available_languages() as $language ) {
				$urls[] = trailingslashit(
					sprintf(
						'%s-%s',
						untrailingslashit( self::get_public_feed_url() ),
						$language,
					)
				);
			}
		} else {
			$urls[] = self::get_public_feed_url();
		}

		return $urls;
	}

	public static function get_feed_data() {
		static $feed_data;

		if ( ! is_array( $feed_data ) ) {
			$feed_data = [];
			$languages = self::get_available_languages();
			if ( ! empty( $languages ) ) {
				foreach ( $languages as $language ) {
					$feed_data[] = [
						'url'              => self::get_public_feed_url(),
						'folder'           => self::get_feed_directory(),
						'folder_parts'     => self::get_feed_parts_directory(),
						'feed_custom_args' => [],
					];
				}
			} else {
				$feed_data[] = [
					'url'              => self::get_public_feed_url(),
					'folder'           => self::get_feed_directory(),
					'folder_parts'     => self::get_feed_parts_directory(),
					'feed_custom_args' => [],
				];
			}
		}

		return $feed_data;
	}
}
