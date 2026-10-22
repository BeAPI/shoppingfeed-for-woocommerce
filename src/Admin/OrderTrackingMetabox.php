<?php

namespace ShoppingFeed\ShoppingFeedWC\Admin;

use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;
use ShoppingFeed\ShoppingFeedWC\Orders\Order;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

/**
 * Tracking number and link metabox for ShoppingFeed orders.
 */
class OrderTrackingMetabox {

	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'register_metabox' ), 100 );
		add_action( 'woocommerce_process_shop_order_meta', array( $this, 'save_metabox' ) );
		add_action( 'woocommerce_process_shop_order', array( $this, 'save_metabox' ) );
	}

	/**
	 * @param int $post_id Order ID.
	 */
	public function save_metabox( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		$nonce  = isset( $_POST['_sfa_tracking_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_sfa_tracking_nonce'] ) ) : '';
		$action = sprintf( 'save_sfa_tracking_%s', $post_id );
		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, $action ) || ! current_user_can( 'edit_shop_order', $post_id ) ) {
			return;
		}

		$order = wc_get_order( $post_id );
		if ( false === $order ) {
			return;
		}
		if ( isset( $_POST[ TRACKING_NUMBER_FIELD_SLUG ] ) ) {
			$order->update_meta_data(
				TRACKING_NUMBER_FIELD_SLUG,
				sanitize_text_field( wp_unslash( $_POST[ TRACKING_NUMBER_FIELD_SLUG ] ) )
			);
		}
		if ( isset( $_POST[ TRACKING_LINK_FIELD_SLUG ] ) ) {
			$order->update_meta_data(
				TRACKING_LINK_FIELD_SLUG,
				sanitize_text_field( wp_unslash( $_POST[ TRACKING_LINK_FIELD_SLUG ] ) )
			);
		}
		if ( isset( $_POST[ TRACKING_NUMBER_FIELD_SLUG ] ) || isset( $_POST[ TRACKING_LINK_FIELD_SLUG ] ) ) {
			$order->save();
		}
	}

	public function register_metabox() {
		if ( class_exists( CustomOrdersTableController::class ) && wc_get_container()->get( CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled() ) {
			$screen = wc_get_page_screen_id( 'shop-order' );
			if ( empty( $screen ) ) {
				return;
			}
			if ( ! isset( $_GET['id'], $_GET['page'] ) || ! is_numeric( $_GET['id'] ) || 'wc-orders' !== $_GET['page'] ) {
				return;
			}
			$post_id = (int) $_GET['id'];
		} else {
			$screen = get_current_screen();
			if ( is_null( $screen ) || 'shop_order' !== $screen->post_type ) {
				return;
			}
			global $post;
			$post_id = $post->ID;
		}

		$order = wc_get_order( $post_id );

		if ( false === $order || ! Order::is_sf_order( $order ) ) {
			return;
		}

		add_meta_box(
			'sfa-carrier_fields',
			__( 'ShoppingFeed Carrier Details', 'shopping-feed' ),
			array( $this, 'render' ),
			$screen,
			'side'
		);
	}

	/**
	 * @param \WP_Post|\WC_Order $order_or_post Order or post.
	 */
	public function render( $order_or_post ) {
		$order = ( $order_or_post instanceof \WP_Post ) ? wc_get_order( $order_or_post->ID ) : $order_or_post;
		if ( false === $order ) {
			return;
		}
		?>
		<p>
			<label for="sfa_tracking_number">
				<?php esc_html_e( 'Tracking Number', 'shopping-feed' ); ?>
			</label>
			<br>
			<input type="text" name="<?php echo esc_attr( TRACKING_NUMBER_FIELD_SLUG ); ?>"
					id="<?php echo esc_attr( TRACKING_NUMBER_FIELD_SLUG ); ?>"
					value="<?php echo esc_attr( $order->get_meta( TRACKING_NUMBER_FIELD_SLUG ) ); ?>">
		</p>
		<p>
			<label for="sfa_tracking_link">
				<?php esc_html_e( 'Tracking Link', 'shopping-feed' ); ?>
			</label>
			<br>
			<input type="text" name="<?php echo esc_attr( TRACKING_LINK_FIELD_SLUG ); ?>"
					id="<?php echo esc_attr( TRACKING_LINK_FIELD_SLUG ); ?>"
					value="<?php echo esc_attr( $order->get_meta( TRACKING_LINK_FIELD_SLUG ) ); ?>">
		</p>
		<?php
		wp_nonce_field(
			sprintf( 'save_sfa_tracking_%d', $order->get_id() ),
			'_sfa_tracking_nonce'
		);
		submit_button( '', 'primary', 'shoppingfeed_carrier_details_submit' );
	}
}
