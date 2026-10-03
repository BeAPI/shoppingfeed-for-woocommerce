<?php

namespace ShoppingFeed\ShoppingFeedWC\Addons;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

/**
 * Soft-disable legacy standalone addon bootstraps before they run.
 */
class StandaloneAddonsGuard {

	private const LEGACY_ADDONS = [
		'shoppingfeed-yoast-metas/shoppingfeed-yoast-metas.php' => [
			'hook'     => 'plugins_loaded',
			'callback' => 'ShoppingFeed\\ShoppingFeedWCYoastMetas\\init',
			'priority' => 10,
			'label'    => 'ShoppingFeed Yoast Metas',
		],
		'shoppingfeed-custom-fields/shoppingfeed-custom-fields.php' => [
			'hook'     => 'plugins_loaded',
			'callback' => 'ShoppingFeed\\ShoppingFeedWCCustomFields\\init',
			'priority' => 10,
			'label'    => 'ShoppingFeed Custom Fields',
		],
	];

	/** @var string[] */
	private static $disabled_labels = [];

	public static function disable_legacy_addons(): void {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		foreach ( self::LEGACY_ADDONS as $plugin_file => $config ) {
			if ( ! self::is_legacy_plugin_active( $plugin_file ) ) {
				continue;
			}

			remove_action( $config['hook'], $config['callback'], $config['priority'] );
			self::$disabled_labels[] = $config['label'];
		}

		add_action( 'init', array( __CLASS__, 'disable_advanced_addon' ), 1 );
		add_action( 'admin_notices', array( __CLASS__, 'render_admin_notices' ) );
	}

	public static function disable_advanced_addon(): void {
		if ( ! self::is_advanced_legacy_active() ) {
			return;
		}

		remove_action( 'init', 'ShoppingFeed\\ShoppingFeedWCAdvanced\\init', 20 );

		if ( ! in_array( 'ShoppingFeed Advanced', self::$disabled_labels, true ) ) {
			self::$disabled_labels[] = 'ShoppingFeed Advanced';
		}
	}

	/**
	 * @return string[]
	 */
	public static function get_disabled_labels(): array {
		return self::$disabled_labels;
	}

	public static function render_admin_notices(): void {
		if ( empty( self::$disabled_labels ) || ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		foreach ( array_unique( self::$disabled_labels ) as $label ) {
			?>
			<div class="notice notice-warning">
				<p>
					<?php
					echo esc_html(
						sprintf(
							/* translators: %s: legacy plugin name */
							__( 'ShoppingFeed now includes the features of "%s". The standalone plugin has been overridden, but you must deactivate it to avoid PHP conflicts.', 'shopping-feed' ),
							$label
						)
					);
					?>
					<a href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>">
						<?php esc_html_e( 'Go to Plugins', 'shopping-feed' ); ?>
					</a>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * @param string $plugin_file Plugin basename.
	 *
	 * @return bool
	 */
	private static function is_legacy_plugin_active( $plugin_file ): bool {
		return function_exists( 'is_plugin_active' ) && is_plugin_active( $plugin_file );
	}

	/**
	 * @return bool
	 */
	private static function is_advanced_legacy_active(): bool {
		if ( defined( 'SFA_PLUGIN_VERSION' ) ) {
			return true;
		}

		return self::is_legacy_plugin_active( 'shoppingfeed-advanced/shoppingfeed-advanced.php' );
	}
}
