<?php

namespace ShoppingFeed\ShoppingFeedWC;

// Exit on direct access
use ShoppingFeed\ShoppingFeedWC\Admin\Options;

defined( 'ABSPATH' ) || exit;

class Upgrade {

	/**
	 * Run upgrades after a plugin update.
	 *
	 * @return void
	 */
	public static function do_upgrades() {
		$version = get_option( 'sf_upgrade_version', '' );
		if ( version_compare( $version, SF_VERSION, '=' ) ) {
			return;
		}

		if ( version_compare( $version, '6.10.0', '<' ) ) {
			self::upgrade_log( 'Upgrade "upgrade_6100" started.' );
			self::upgrade_6100();
			self::upgrade_log( 'Upgrade "upgrade_6100" completed.' );
		}

		update_option( 'sf_upgrade_version', SF_VERSION );
	}

	public static function upgrade_6100() {
		// Migrate old shipment tracking option.
		$shipping_options = ShoppingFeedHelper::get_sf_shipping_options();
		if ( ! is_array( $shipping_options ) ) {
			self::upgrade_log( 'Upgrade 6.10.0 : shipping options is not defined.', \WC_Log_Levels::WARNING );
			$shipping_options = [];
		}

		if ( empty( $shipping_options['tracking_provider'] ) ) {
			$legacy_option = $shipping_options['retrieval_mode'];
			if ( empty( $legacy_option ) ) {
				$legacy_option = 'ADDONS';
				self::upgrade_log( 'Upgrade 6.10.0 : retrieval_mode is not defined, defaulting to ADDONS mode.', \WC_Log_Levels::WARNING );
			}

			if ( 'ADDONS' === $legacy_option && defined( 'SHIPMENT_TRACKING_PATH' ) ) {
				self::upgrade_log( 'Upgrade 6.10.0 : shipment tracking set to "ADDONS" and "AST/AST Pro" plugin seem active, updating option accordingly.' );
				$shipping_options['tracking_provider'] = 'advanced_shipment_tracking';
				update_option( Options::SF_SHIPPING_OPTIONS, $shipping_options );
			} elseif ( 'ADDONS' === $legacy_option && defined( 'PH_SHIPMENT_TRACKING_PLUGIN_VERSION' ) ) {
				self::upgrade_log( 'Upgrade 6.10.0 : shipment tracking set to "ADDONS" and "Woocommerce Shipments Tracking" plugin seem active, updating option accordingly.' );
				$shipping_options['tracking_provider'] = 'woo_shipment_tracking';
				update_option( Options::SF_SHIPPING_OPTIONS, $shipping_options );
			} elseif ( 'META' === $legacy_option && defined( 'SFA_PLUGIN_VERSION' ) ) {
				self::upgrade_log( 'Upgrade 6.10.0 : shipment tracking set to "META" and "ShoppingFeed Advanced" plugin seem active, updating option accordingly.' );
				$shipping_options['tracking_provider'] = 'sf_advanced';
				update_option( Options::SF_SHIPPING_OPTIONS, $shipping_options );
			} else {
				self::upgrade_log( 'Upgrade 6.10.0 : no potential provider detected.' );
			}
		}
	}

	private static function upgrade_log( string $message, string $level = \WC_Log_Levels::INFO ): void {
		// Avoid error with invalid level.
		if ( ! \WC_Log_Levels::is_valid_level( $level ) ) {
			$level = \WC_Log_Levels::INFO;
		}

		ShoppingFeedHelper::get_logger()->log(
			$level,
			esc_html( $message ),
			array(
				'source' => 'shopping-feed-migration',
			)
		);
	}
}
