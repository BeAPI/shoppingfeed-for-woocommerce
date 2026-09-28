<?php

namespace ShoppingFeed\ShoppingFeedWC\Addons\Plugins\AcfPlugin;

use ShoppingFeed\ShoppingFeedWC\Admin\Options;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

/**
 * Informative page at the legacy Custom Fields addon settings URL.
 */
class LegacySettingsPage {

	const MENU_SLUG = 'shopping-feed-custom-fields';

	public function __construct() {
		if ( ! self::should_register_page() ) {
			return;
		}

		add_action(
			'admin_menu',
			function () {
				add_options_page(
					__( 'ShoppingFeed Custom Fields', 'shopping-feed' ),
					__( 'ShoppingFeed Custom Fields', 'shopping-feed' ),
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
		if ( defined( 'SFCF_PLUGIN_VERSION' ) ) {
			return true;
		}

		if ( false !== get_option( CustomFieldsHelper::LEGACY_OPTIONS, false ) ) {
			return true;
		}

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return is_plugin_active( 'shoppingfeed-custom-fields/shoppingfeed-custom-fields.php' );
	}

	public function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$feed_settings_url = admin_url(
			add_query_arg(
				array(
					'page' => Options::SF_SLUG,
					'tab'  => 'feed-settings',
				),
				'admin.php'
			)
		);
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'ShoppingFeed Custom Fields', 'shopping-feed' ); ?></h1>
			<p>
				<?php esc_html_e( 'ACF fields are now configured in ShoppingFeed → Feed. You can safely deactivate the ShoppingFeed Custom Fields plugin.', 'shopping-feed' ); ?>
			</p>
			<p>
				<a href="<?php echo esc_url( $feed_settings_url ); ?>">
					<?php esc_html_e( 'Open Feed settings', 'shopping-feed' ); ?>
				</a>
				|
				<a href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>">
					<?php esc_html_e( 'Go to Plugins', 'shopping-feed' ); ?>
				</a>
			</p>
		</div>
		<?php
	}
}
