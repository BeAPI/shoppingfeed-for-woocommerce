<?php

namespace ShoppingFeed\ShoppingFeedWC\Actions;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

use ShoppingFeed\ShoppingFeedWC\Sdk\Sdk;
use ShoppingFeed\ShoppingFeedWC\ShoppingFeedHelper;

/**
 * Add required actions using Action Scheduler
 * @package ShoppingFeed\Actions
 */
class Actions {

	/** @var string orders group */
	const ORDERS_GROUP = 'sf_orders';

	/** @var string feed group */
	const FEED_GROUP = 'sf_feed';

	/**
	 * Register new action to generate feed
	 */
	public static function register_feed_generation() {
		as_schedule_recurring_action(
			time() + 60,
			ShoppingFeedHelper::get_sf_feed_generation_frequency(),
			'sf_generate_feed_action',
			array(),
			self::FEED_GROUP
		);
	}

	/**
	 * Register new action to get orders
	 */
	public static function register_get_orders() {
		$sf_accounts = ShoppingFeedHelper::get_sf_account_options();
		if ( empty( $sf_accounts ) ) {
			ShoppingFeedHelper::get_logger()->error(
				sprintf(
					__( 'No Accounts founds', 'shopping-feed' )
				),
				array(
					'source' => 'shopping-feed',
				)
			);

			return;
		}
		foreach ( $sf_accounts as $key => $sf_account ) {
			as_schedule_recurring_action(
				time() + 60,
				ShoppingFeedHelper::get_sf_orders_import_frequency(),
				'sf_sync_orders',
				array(
					'sf_account' => $key,
				),
				self::ORDERS_GROUP
			);
		}
	}

	/**
	 * Remove all orders actions
	 */
	public static function clean_get_orders() {
		as_unschedule_all_actions( false, false, self::ORDERS_GROUP );
	}

	/**
	 * Remove all feed actions
	 */
	public static function clean_feed_generation() {
		as_unschedule_all_actions( false, false, self::FEED_GROUP );
	}
}
