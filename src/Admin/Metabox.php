<?php

namespace ShoppingFeed\ShoppingFeedWC\Admin;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

use ShoppingFeed\ShoppingFeedWC\Orders\Order;
use ShoppingFeed\ShoppingFeedWC\Query\Query;
use WP_Post;

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
		global $post;
		$screen = get_current_screen();
		if ( is_null( $screen ) || 'shop_order' !== $screen->post_type ) {
			return;
		}

		$order = wc_get_order( $post );
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
			'shop_order',
			'side'
		);
	}

	/**
	 * Render the metabox content.
	 *
	 * @param WP_Post $post
	 */
	public function render( $post ) {
		$order = wc_get_order( $post );
		if ( false === $order ) {
			return;
		}

		$reference    = $order->get_meta( Query::$wc_meta_sf_reference );
		$channel_name = $order->get_meta( Query::$wc_meta_sf_channel_name );

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
