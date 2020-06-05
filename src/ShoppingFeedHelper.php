<?php
namespace ShoppingFeed\ShoppingFeedWC;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

use ShoppingFeed\ShoppingFeedWC\Admin\Options;
use ShoppingFeed\ShoppingFeedWC\Sdk\Sdk;
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
	 * Return WC Logger
	 * @return WC_Logger
	 */
	public static function get_logger() {
		return new WC_Logger();
	}

	/**
	 * Return the settings link for plugin
	 * @return string
	 */
	public static function get_setting_link() {
		return admin_url( 'admin.php?page=shopping-feed' );
	}

	/**
	 * Return the feed's directory
	 * @return string
	 */
	public static function get_feed_directory() {
		$directory = wp_upload_dir()['basedir'] . '/shopping-feed/';
		if ( ! is_dir( $directory ) ) {
			wp_mkdir_p( $directory );
		}

		return $directory;
	}

	/**
	 * Return the feed's file name
	 * @return string
	 */
	public static function get_feed_filename() {
		return 'products';
	}

	/**
	 * Return the feed's public endpoint
	 * @return string
	 */
	public static function get_public_feed_endpoint() {
		return Rewrite::FEED_PARAM;
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
	 * Set SF token after connection
	 *
	 * @param $token
	 *
	 */
	public static function set_sf_token( $token ) {
		$options          = self::get_sf_account_options();
		$options['token'] = $token;

		update_option( Options::SF_ACCOUNT_OPTIONS, $options );
	}

	/**
	 * Return SF Configuration for Account
	 * @return mixed|void
	 */
	public static function get_sf_account_options() {
		return get_option( Options::SF_ACCOUNT_OPTIONS );
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
	 * Reset password if bad
	 *
	 * @param $token
	 *
	 */
	public static function clean_password() {
		$options = self::get_sf_account_options();
		if ( isset( $options ) && isset( $options['password'] ) ) {
			unset( $options['password'] );
		}

		update_option( Options::SF_ACCOUNT_OPTIONS, $options );
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
	 * Return SF default shipping zone id if exist
	 * @return bool|int
	 */
	public static function get_sf_default_shipping_zone() {
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
	 * Return SF default shipping fees if exist
	 * @return float
	 */
	public static function get_sf_default_shipping_fees() {
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
	 * Return SF orders configuration
	 * @return mixed|void
	 */
	public static function get_sf_orders_options() {
		return get_option( Options::SF_ORDERS_OPTIONS );
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
	 * Get all shipping methods for default zone
	 * @return array
	 */
	public static function get_shipping_methods() {
		$default_shipping_zone_id = self::get_sf_default_shipping_zone();
		if ( ! is_int( $default_shipping_zone_id ) ) {
			return array();
		}

		return self::get_shipping_methods_by_zone( $default_shipping_zone_id );
	}

	/**
	 * Return shipping method by zone id
	 * @param $zone_id
	 *
	 * @return array
	 */
	public static function get_shipping_methods_by_zone( $zone_id ) {
		if ( empty( $zone_id ) ) {
			return array();
		}
		$shipping_zone    = new \WC_Shipping_Zone( $zone_id );
		$shipping_methods = $shipping_zone->get_shipping_methods();

		if ( empty( $shipping_methods ) ) {
			return array();
		}

		$_shipping_methods = array();
		foreach ( $shipping_methods as $shipping_method ) {
			$_shipping_methods[] = array(
				'method_rate_id' => $shipping_method->id,
				'method_id'      => $shipping_method->instance_id,
				'method_title'   => $shipping_method->title,
			);
		}

		return $_shipping_methods;
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

		return json_decode( $shipping_options['default_shipping_method'], true );
	}

	/**
	 * Add new matching for sf carrier
	 *
	 * @param $carrier_id
	 * @param $method
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
	 * Get SF Carrier from WC Shipping
	 *
	 * @param $wc_order \WC_Order
	 *
	 * @return string
	 */
	public static function get_sf_carrier_from_wc_shipping( $wc_order ) {
		$sf_shipping = json_decode( $wc_order->get_meta( 'sf_shipping' ), true );
		if ( empty( $sf_shipping['sf_shipping'] ) ) {
			return $wc_order->get_shipping_method();
		}

		$sf_carrier_id = $sf_shipping['sf_shipping'];

		$sf_carriers = self::get_sf_carriers();
		if ( empty( $sf_carriers[ $sf_carrier_id ] ) ) {
			return $wc_order->get_shipping_method();
		}

		return $sf_carriers[ $sf_carrier_id ];
	}

	/**
	 * @return bool
	 */
	public static function is_authenticated() {
		$sdk = Sdk::get_instance();
		if ( empty( $sdk ) ) {
			return false;
		}

		return $sdk->authenticate();
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
	 * Add filter for product ean field
	 * @return string
	 */
	public static function wc_product_ean() {
		return apply_filters( 'shopping_feed_custom_ean', '' );
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
		return apply_filters( 'shopping_feed_orders_to_import', array( 'waiting_shipment' ) );
	}

	/**
	 * Default quantity if product quantity is unset
	 * @return int
	 */
	public static function get_default_product_quantity() {
		return 100;
	}
}
