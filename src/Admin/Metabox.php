<?php

namespace ShoppingFeed\ShoppingFeedWC\Admin;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

use ShoppingFeed\ShoppingFeedWC\Orders\Order;
use ShoppingFeed\ShoppingFeedWC\Query\Query;
use WP_Post;
use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;

/**
 * Class Metabox
 * @package ShoppingFeed\ShoppingFeedWC\Admin
 */
class Metabox {

	/**
	 * Metabox constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'register_sf_metabox' ), 100 );
	}

	/**
	 * Register a custom metabox to display metadata for the current order.
	 *
	 * This metabox is only register if the current order has been creayed by ShoppingFeed.
	 */
	public function register_sf_metabox() {

		if ( class_exists( 'Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController' ) && wc_get_container()->get( CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled() ) {
			/**
			 * If WC is using the new tables, returns the screen id or empty
			 */
			$screen = wc_get_page_screen_id( 'shop-order' );
			if ( empty( $screen ) ) {
				return;
			}
			if ( ! isset( $_GET['id'], $_GET['page'] ) || ! is_numeric( $_GET['id'] ) || 'wc-orders' !== $_GET['page']
			) {
				return;
			}
			$post_id = (int) $_GET['id'];
		} else {
			/**
			 * If not, we use the legacy test
			 */
			$screen = get_current_screen();
			if ( is_null( $screen ) || 'shop_order' !== $screen->post_type ) {
				return;
			}
			global $post;
			$post_id = $post->ID;
		}

		$order = wc_get_order( $post_id );

		if ( false === $order ) {
			return;
		}

		if ( ! Order::is_sf_order( $order ) ) {
			return;
		}

		add_meta_box(
			'sf-transaction-details',
			__( 'ShoppingFeed details', 'shopping-feed' ),
			array( $this, 'render' ),
			$screen,
			'side'
		);
	}

	/**
	 * Render the metabox content.
	 *
	 * @param WP_Post|\WC_Order $post_or_order_object
	 *
	 * @author Stéphane Gillot
	 */
	public function render( $post_or_order_object ) {
		$order = ( $post_or_order_object instanceof WP_Post ) ? wc_get_order( $post_or_order_object->ID ) : $post_or_order_object;
		if ( false === $order ) {
			return;
		}

		$reference    = $order->get_meta( Query::WC_META_SF_REFERENCE );
		$channel_name = $order->get_meta( Query::WC_META_SF_CHANNEL_NAME );

		if ( empty( $reference ) && empty( $channel_name ) ) : ?>
			<p><?php esc_html_e( 'No metadata available for the current order.', 'shopping-feed' ); ?></p>
		<?php else : ?>
			<ul>
				<li>
					<span><?php esc_html_e( 'Reference', 'shopping-feed' ); ?>:</span> <?php echo esc_html( $reference ); ?>
				</li>
				<li>
					<span><?php esc_html_e( 'MarketPlace', 'shopping-feed' ); ?>:</span> <?php echo esc_html( $channel_name ); ?>
				</li>
				<?php
				do_action( 'sf_show_metas', $order );
				?>
			</ul>
		<?php
		endif;
	}
}
