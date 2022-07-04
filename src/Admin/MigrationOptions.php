<?php

namespace ShoppingFeed\ShoppingFeedWC\Admin;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

use ShoppingFeed\ShoppingFeedWC\ShoppingFeedHelper;

class MigrationOptions {

	const SF_SLUG = 'shopping-feed';

	const SF_MIGRATION_SETTINGS_PAGE = 'sf_migration_settings_page';

	const SF_MIGRATION_OPTIONS = 'sf_migration_options';

	/** @var array $sf_shipping_options */
	private $sf_migration_options;

	public function __construct() {
		/**
		 * Add admin menu
		 */
		add_action( 'admin_menu', [ $this, 'sf_init_migration_options' ] );

		/**
		 * Add migration settings
		 */
		add_action( 'admin_init', [ $this, 'sf_init_migration_settings' ] );

		$this->sf_migration_options = ShoppingFeedHelper::get_sf_migration_options();
	}

	public function sf_init_migration_options() {
		add_submenu_page(
			'woocommerce',
			__( 'ShoppingFeed', 'shopping-feed' ),
			__( 'ShoppingFeed', 'shopping-feed' ),
			'manage_options',
			self::SF_SLUG,
			[ $this, 'sf_migration_settings_page' ]
		);
	}

	public function sf_init_migration_settings() {
		register_setting(
			'sf_migration_page_fields',
			self::SF_MIGRATION_OPTIONS
		);
	}

	public function sf_migration_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'account';

		$tabs = [
			'tab'   => 'migration-settings',
			'url'   => '?page=' . self::SF_SLUG . '&tab=migration-settings',
			'title' => __( 'Migration', 'shopping-feed' ),
		];
		?>

        <div class="wrap sf__plugin">

        <h1 class="sf__header">
            <div class="sf__logo"><?php echo esc_html( get_admin_page_title() ); ?></div>
            <div class="sf__version"><?php esc_html_e( 'Plugin version:', 'shopping-feed' ); ?><?php echo esc_html( SF_VERSION ); ?></div>
        </h1>

        <nav class="nav-tab-wrapper">
        <nav class="nav-tab-wrapper">
			<?php
			foreach ( $tabs as $_tab ) {
				?>
                <a href="<?php echo esc_url( $_tab['url'] ); ?>" class="nav-tab
						<?php
				printf( '%s', ( esc_html( $tab ) === $_tab['tab'] ) ? esc_html( 'nav-tab-active' ) : '' )
				?>
						">
					<?php
					echo esc_html( $_tab['title'] );
					?>
                </a>
				<?php
			}
			?>
        </nav>

		<?php
		$this->init_migration_settings_page();
	}

	private function init_migration_settings_page() {
		$this->load_assets();

		add_settings_section(
			'sf_migration_settings_manual_migration',
			__( 'Migration settings', 'shopping-feed' ),
			function() {},
			self::SF_MIGRATION_SETTINGS_PAGE
		);

        add_settings_field(
			'sf_migration_page_fields',
			__( 'Unlock Shopping Feed migration', 'shopping-feed' ),
			function () {
				?>
				<!-- Here we are comparing stored value with 1. Stored value is 1 if user checks the checkbox otherwise empty string. -->
				<input type="checkbox"
					   name="<?php echo esc_html( self::SF_MIGRATION_OPTIONS ); ?>"
					   value="1" <?php checked( 1, (int) $this->sf_migration_options['unlock_migration'], true ); ?> />
				<?php
				/**
                 * TODO:
                 * - handle unlock_migration in array
                 * - delete SF_UPGRADE_RUNNING option on submit
                 * - add second submit button to relaunch the migration
                 */
			},
			self::SF_MIGRATION_SETTINGS_PAGE,
			'sf_migration_settings_categories'
		);
	}

	private function load_assets() {
		wp_enqueue_style(
			'sf_app',
			SF_PLUGIN_URL . 'assets/css/app.css',
			[],
			true
		);
	}
}