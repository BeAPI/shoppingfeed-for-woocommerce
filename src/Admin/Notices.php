<?php

namespace ShoppingFeed\ShoppingFeedWC\Admin;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

use ShoppingFeed\ShoppingFeedWC\ShoppingFeedHelper;


/**
 * Handle admin notices.
 */
class Notices {

	public function __construct() {
		add_action( 'admin_notices', array( $this, 'unavailable_selected_shipping_tracking_provider' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	public function admin_notices() {
		//TODO: CHECK ALL CREDENTIALS
		return;
		//      $options = ShoppingFeedHelper::get_sf_account_options();
		//      if (
		//          empty( $options['token'] ) &&
		//          get_current_screen()->parent_base !== Options::SF_SLUG ) {
		//          if ( empty( $options['username'] ) || empty( $options['password'] ) ) {
		//              $this->display_notice();
		//          }
		//      }
	}

	/**
	 * Enqueue ShoppingFeed notice style.
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts() {
		$options = ShoppingFeedHelper::get_sf_account_options();
		if (
			empty( $options['token'] ) &&
			get_current_screen()->parent_base !== Options::SF_SLUG ) {
			if ( empty( $options['username'] ) || empty( $options['password'] ) ) {
				wp_enqueue_style(
					'sf_notices',
					SF_PLUGIN_URL . 'assets/css/notice.css',
					array(),
					SF_VERSION
				);
			}
		}
	}

	public function display_notice() {
		?>
		<div class="notice sf__notice sf__notice--start">
			<div class="inside">
				<div class="main">
					<h2 class="sf__notice__title"><?php esc_html_e( 'Thank your for downloading Shoppingfeed', 'shopping-feed' ); ?></h2>
					<div class="sf__notice__subtitle"><?php esc_html_e( 'Connect your Ecommerce to 1,000+ Channels', 'shopping-feed' ); ?></div>
					<a href="<?php echo esc_url( ShoppingFeedHelper::get_setting_link() ); ?>" class="button action"><?php esc_html_e( 'Login', 'shopping-feed' ); ?></a>
				</div>
			</div>
		</div>
		<?php
	}

	public function unavailable_selected_shipping_tracking_provider(): void {
		$manager           = ShoppingFeedHelper::wc_tracking_provider_manager();
		$selected_provider = $manager->get_selected_provider( false );
		if ( ! $selected_provider->is_available() ) {
			?>
			<div class="notice notice-error">
				<p>
					<span class="dashicons dashicons-warning"></span>
					<?php
					echo wp_kses_post(
						sprintf(
							// translators: 1: the provider name, 2: the plugin settings link
							__( 'The selected shipment tracking provider <strong>"%1$s"</strong> is not available. Please go the <a href="%2$s">shipping settings</a> to choose a new one.', 'shopping-feed' ),
							$selected_provider->name(),
							add_query_arg( 'tab', 'shipping-settings', ShoppingFeedHelper::get_setting_link() )
						)
					);
					?>
				</p>
			</div>
			<?php
		}
	}
}
