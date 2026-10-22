<?php

namespace ShoppingFeed\ShoppingFeedWC\Addons\Advanced;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

/**
 * Informative page at the legacy Advanced addon settings URL.
 */
class LegacySettingsPage {

	const MENU_SLUG = 'shopping-feed-advanced';

	public function __construct() {
		if ( ! self::should_register_page() ) {
			return;
		}

		add_action(
			'admin_menu',
			function () {
				add_options_page(
					__( 'ShoppingFeed Advanced', 'shopping-feed' ),
					__( 'ShoppingFeed Advanced', 'shopping-feed' ),
					'manage_options',
					self::MENU_SLUG,
					array( $this, 'render' )
				);
			}
		);
	}

	/**
	 * @return bool
	 */
	public static function should_register_page() {
		if ( defined( 'SFA_PLUGIN_VERSION' ) ) {
			return true;
		}

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return is_plugin_active( 'shoppingfeed-advanced/shoppingfeed-advanced.php' );
	}

	public function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'ShoppingFeed Advanced', 'shopping-feed' ); ?></h1>
			<p>
				<?php esc_html_e( 'EAN and Brand features are now built into ShoppingFeed. You can safely deactivate the ShoppingFeed Advanced plugin.', 'shopping-feed' ); ?>
			</p>
			<p>
				<a href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>">
					<?php esc_html_e( 'Go to Plugins', 'shopping-feed' ); ?>
				</a>
			</p>
		</div>
		<?php
	}
}
