<?php

namespace ShoppingFeed\ShoppingFeedWC\Admin;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

use ShoppingFeed\ShoppingFeedWC\Actions\Actions;
use ShoppingFeed\ShoppingFeedWC\Feed\Generator;
use ShoppingFeed\ShoppingFeedWC\Orders\Operations;
use ShoppingFeed\ShoppingFeedWC\ShoppingFeedHelper;

class Options {


	const SF_SLUG = 'shopping-feed';

	/**
	 * Account settings page
	 */
	const SF_ACCOUNT_SETTINGS_PAGE = 'sf_account_settings_page';

	/**
	 * Feed settings page
	 */
	const SF_FEED_SETTINGS_PAGE = 'sf_feed_settings_page';

	/**
	 * Orders settings page
	 */
	const SF_SHIPPING_SETTINGS_PAGE = 'sf_shipping_settings_page';

	/**
	 * Orders settings page
	 */
	const SF_ORDERS_SETTINGS_PAGE = 'sf_orders_settings_page';

	//Account options
	const SF_ACCOUNT_OPTIONS = 'sf_account_options';
	const SF_FEED_OPTIONS    = 'sf_feed_options';

	//Feed options
	const SF_SHIPPING_OPTIONS = 'sf_shipping_options';
	const SF_ORDERS_OPTIONS   = 'sf_orders_options';

	//Shipping options
	const SF_CARRIERS = 'SF_CARRIERS';
	/** @var array $sf_account_options */
	private $sf_account_options;

	//Orders options
	/** @var array $sf_feed_options */
	private $sf_feed_options;
	/** @var array $sf_shipping_options */
	private $sf_shipping_options;

	//SF Carriers
	/** @var array $sf_orders_options */
	private $sf_orders_options;


	/**
	 * Options constructor.
	 */
	public function __construct() {
		/**
		 * Add admin menu
		 */
		add_action(
			'admin_menu',
			function () {
				add_submenu_page(
					'woocommerce',
					__( 'ShoppingFeed', 'shopping-feed' ),
					__( 'ShoppingFeed', 'shopping-feed' ),
					'manage_options',
					self::SF_SLUG,
					array( $this, 'sf_settings_page' )
				);
			}
		);

		/*
		 * Register settings
		 */
		add_action(
			'admin_init',
			function () {
				//Account page
				register_setting(
					'sf_account_page_fields',
					self::SF_ACCOUNT_OPTIONS
				);

				//Feed page
				register_setting(
					'sf_feed_page_fields',
					self::SF_FEED_OPTIONS
				);

				//Shipping page
				register_setting(
					'sf_shipping_page_fields',
					self::SF_SHIPPING_OPTIONS
				);

				//Orders page
				register_setting(
					'sf_orders_page_fields',
					self::SF_ORDERS_OPTIONS
				);
			}
		);

		/**
		 * Clean and register new cron once account option updated
		 */
		add_action(
			'admin_action_update',
			function () {
				Actions::clean_get_orders();
				Actions::register_get_orders();
			}
		);

		//get account options
		$this->sf_account_options = ShoppingFeedHelper::get_sf_account_options();
		//get feed options
		$this->sf_feed_options = ShoppingFeedHelper::get_sf_feed_options();
		//get shipping options
		$this->sf_shipping_options = ShoppingFeedHelper::get_sf_shipping_options();
		//get orders options
		$this->sf_orders_options = ShoppingFeedHelper::get_sf_orders_options();
	}

	/**
	 * Define the page structure
	 */
	public function sf_settings_page() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'account';

		$tabs = array(
			array(
				'tab' => 'account',
				'url' => '?page=' . self::SF_SLUG,
				'title' => __( 'Account', 'shopping-feed' ),
			),
			array(
				'tab' => 'feed-settings',
				'url' => '?page=' . self::SF_SLUG . '&tab=feed-settings',
				'title' => __( 'Feed', 'shopping-feed' ),
			),
			array(
				'tab' => 'shipping-settings',
				'url' => '?page=' . self::SF_SLUG . '&tab=shipping-settings',
				'title' => __( 'Shipping', 'shopping-feed' ),
			),
			array(
				'tab' => 'orders-settings',
				'url' => '?page=' . self::SF_SLUG . '&tab=orders-settings',
				'title' => __( 'Orders', 'shopping-feed' ),
			),
			array(
				'tab' => 'logs',
				'url' => admin_url( 'admin.php?page=wc-status&tab=logs' ),
				'title' => __( 'Logs', 'shopping-feed' ),
			),
		);
		?>
		<div class="wrap sf__plugin">

				<h1 class="sf__header">
					<div class="sf__logo"><?php echo esc_html( get_admin_page_title() ); ?></div>
					<div class="sf__version"><?php esc_html_e( 'Plugin version:', 'shopping-feed' ); ?> <?php echo esc_html( SF_VERSION ); ?></div>
				</h1>

			<nav class="nav-tab-wrapper">
				<nav class="nav-tab-wrapper">
					<?php
					foreach (
					$tabs as $_tab
					) {
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
				switch ( $tab ) :
					case 'account':
						$this->init_account_setting_page();
						break;
					case 'feed-settings':
						$this->init_feed_setting_page();
						break;
					case 'shipping-settings':
						$this->init_shipping_setting_page();
						break;
					case 'orders-settings':
						$this->init_orders_setting_page();
						break;
					default:
						break;
		endswitch;
				?>
		</div>
		<?php
	}

	/**
	 * Define Account Page
	 */
	public function init_account_setting_page() {
			//check clean action
			$this->check_clean_action();

			//load assets
			$this->load_assets();

			add_settings_section(
				'sf_account_settings',
				__( 'Account settings', 'shopping-feed' ),
				function () {
				},
				self::SF_ACCOUNT_SETTINGS_PAGE
			);
		?>
		<div class="wrap">
			<?php settings_errors(); ?>

			<div class="sf__columns">
				<div class="sf__column account__wrapper">
					<form method="post" action="options.php">
						<div class="blocks">
							<div class="block_links">
								<table class="form-table sf__table">
									<thead>
									<tr>
										<th> <?php esc_html_e( 'Username', 'shopping-feed' ); ?> </th>
										<th> <?php esc_html_e( 'Password', 'shopping-feed' ); ?> </th>
										<th></th>
									</tr>
									</thead>
									<tbody>
									<tr>
										<td><input class="regular-text user" type="text"
												   name="sf_account_options[0][username]"
												   value="<?php echo isset( $this->sf_account_options[0]['username'] ) ? esc_attr( $this->sf_account_options[0]['username'] ) : ''; ?>">
										</td>
										<td><input class="regular-text pass" type="password"
												   name="sf_account_options[0][password]"
												   value="<?php echo isset( $this->sf_account_options[0]['password'] ) ? esc_attr( $this->sf_account_options[0]['password'] ) : ''; ?>">
										</td>
										<td>
											<button class="button sf__button__secondary delete_link sf__button--delete">
												Delete
											</button>
										</td>
										<td class="hidden"><input name="sf_account_options[0][token]"
																  value="<?php echo isset( $this->sf_account_options[0]['token'] ) ? esc_attr( $this->sf_account_options[0]['token'] ) : ''; ?>">
										</td>
										<td class="hidden"><input name="sf_account_options[0][sf_store_id]"
																  value="<?php echo isset( $this->sf_account_options[0]['sf_store_id'] ) ? esc_attr( $this->sf_account_options[0]['sf_store_id'] ) : ''; ?>">
										</td>
									</tr>
									<?php
									if ( count( $this->sf_account_options ) > 1 ) {
										foreach ( $this->sf_account_options as $key => $sf_account_option ) {
											if ( 0 === $key ) {
												continue;
											}
											?>
											<tr>
												<td><input class="regular-text user" type="text"
														   name="sf_account_options[<?php echo esc_attr( $key ); ?>][username]"
														   value="<?php echo isset( $this->sf_account_options[ $key ]['username'] ) ? esc_attr( $this->sf_account_options[ $key ]['username'] ) : ''; ?>">
												</td>
												<td><input class="regular-text pass" type="password"
														   name="sf_account_options[<?php echo esc_attr( $key ); ?>][password]"
														   value="<?php echo isset( $this->sf_account_options[ $key ]['password'] ) ? esc_attr( $this->sf_account_options[ $key ]['password'] ) : ''; ?>">
												</td>
												<td class="hidden"><input
															name="sf_account_options[<?php echo esc_attr( $key ); ?>][token]"
															value="<?php echo isset( $this->sf_account_options[ $key ]['token'] ) ? esc_attr( $this->sf_account_options[ $key ]['token'] ) : ''; ?>">
												</td>
												<td class="hidden"><input
															name="sf_account_options[<?php echo esc_attr( $key ); ?>][sf_store_id]"
															value="<?php echo isset( $this->sf_account_options[ $key ]['sf_store_id'] ) ? esc_attr( $this->sf_account_options[ $key ]['sf_store_id'] ) : ''; ?>">
												</td>
												<td>
													<button class="button sf__button__secondary delete_link sf__button--delete">
														Delete
													</button>
												</td>
											</tr>
											<?php
										}
									}
									?>
									</tbody>
								</table>
								<div class="sf__inline">
									<p>
										<button class="button sf__button__secondary add_link"><?php esc_html_e( 'Add account', 'shopping-feed' ); ?></button>
									</p>
								</div>
								<div class="sf__inline">
									<?php
									settings_fields( 'sf_account_page_fields' );
									submit_button( __( 'Save', 'shopping-feed' ), 'sf__button' );
									?>
								</div>
					</form>
				</div>
			</div>
			<!-- Line template -->
			<script type="text/html" id="tpl-line">
				<tr>
					<td><input class="regular-text user" type="text" name="sf_account_options[<%= row %>][username]"
							   value="<%= user %>"></td>
					<td><input class="regular-text pass" type="password" name="sf_account_options[<%= row %>][password]"
							   value="<%= pass %>"></td>
					<td>
						<button class="button sf__button__secondary sf__button--delete delete_link">Delete</button>
					</td>
				</tr>
			</script>
			<div class="sf__requirements">
				<?php
				$requirements = Requirements::get_instance();
				echo wp_kses_post( $requirements->curl_requirement() );
				echo wp_kses_post( $requirements->php_requirement() );
				echo wp_kses_post( $requirements->openssl_requirement() );
				echo wp_kses_post( $requirements->uploads_directory_access_requirement() );
				?>
				<!--        REQUIREMENTS     -->
		</div>
		</div>

		<div class="sf__column">
			<div class="sf__marketing">
			<?php MarketingBord::get_instance()->display_marketing_bord(); ?>
			</div>
		</div>
		</div>
		<?php
	}

	/**
	 * Check clean action
	 */
	private function check_clean_action() {
		if ( ! empty( $_GET['clean_process'] ) ) {
			ShoppingFeedHelper::clean_process_running( 'sf_feed_generation_process' );
		}
	}

	private function load_assets() {
		wp_enqueue_style(
			'sf_app',
			SF_PLUGIN_URL . 'assets/css/app.css',
			array(),
			true
		);

		wp_enqueue_script(
			'multi_js',
			SF_PLUGIN_URL . 'assets/js/multi.min.js',
			array( 'jquery' ),
			true,
			true
		);

		wp_enqueue_script(
			'accounts',
			SF_PLUGIN_URL . 'assets/js/accounts.js',
			array( 'jquery', 'underscore' ),
			true,
			true
		);

		wp_enqueue_script( 'multi_js_init', SF_PLUGIN_URL . 'assets/js/init.js', array( 'multi_js' ), true );
		wp_localize_script(
			'multi_js_init',
			'sf_options',
			array(
				'selected_orders'   => __( 'Selected order status', 'shopping-feed' ),
				'unselected_orders' => __( 'Unselected order status', 'shopping-feed' ),
				'search'            => __( 'Search', 'shopping-feed' ),
			)
		);
	}

	/**
	 * Define Feed Page
	 */
	private function init_feed_setting_page() {

		//load assets
		$this->load_assets();

		//Products
		add_settings_section(
			'sf_feed_settings_categories',
			__( 'Products', 'shopping-feed' ),
			function () {
			},
			self::SF_FEED_SETTINGS_PAGE
		);

		add_settings_field(
			'url',
			__( 'Your source feed', 'shopping-feed' ),
			function () {
				$sf_feed_public_url      = ShoppingFeedHelper::get_public_feed_url();
				$sf_process_running      = ShoppingFeedHelper::is_process_running( 'sf_feed_generation_process' );
				$sf_last_generation_date = get_option( Generator::SF_FEED_LAST_GENERATION_DATE );
				?>
				<?php if ( ! $sf_process_running ) : ?>
					<a href="<?php echo esc_html( $sf_feed_public_url ); ?>" target="_blank">
						<?php
						echo esc_url( $sf_feed_public_url );
						;
						?>
					</a>
				<?php endif; ?>
				<br>
				<p>
					<?php if ( ! $sf_process_running ) : ?>
						<?php esc_html_e( 'Last update', 'shopping-feed' ); ?> :
						<?php
						echo ! empty( $sf_last_generation_date ) ? esc_html( $sf_last_generation_date ) : esc_html__( 'Never', 'shopping-feed' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>
						<a href="<?php echo esc_url( ShoppingFeedHelper::get_public_feed_url_with_generation() ); ?>" target="_blank">
							<?php esc_html_e( 'Refresh', 'shopping-feed' ); ?>
						</a>
					<?php else : ?>
						<strong>(<?php esc_html_e( 'The feed is update is running', 'shopping-feed' ); ?>) <a href="#" onClick="window.location.reload();"><?php esc_html_e( 'Refresh to check progress', 'shopping-feed' ); ?></a></strong>
					<?php endif; ?>
				</p>
				<?php
			},
			self::SF_FEED_SETTINGS_PAGE,
			'sf_feed_settings_categories'
		);

		//get available categories
		$product_categories = get_terms(
			ShoppingFeedHelper::wc_category_taxonomy(),
			array(
				'orderby' => 'name',
				'order' => 'asc',
				'hide_empty' => false,
			)
		);

		//Identifier Field
		add_settings_field(
			'identifier',
			__( 'Product identifier', 'shopping-feed' ),
			function () {
				?>
				<select name="<?php echo esc_html( sprintf( '%s[product_identifier]', self::SF_FEED_OPTIONS ) ); ?>">
					<option value="id" <?php selected( 'id', $this->sf_feed_options['product_identifier'] ? $this->sf_feed_options['product_identifier'] : false ); ?>>
						<?php esc_html_e( 'Product ID (recommended)', 'shopping-feed' ); ?>
					</option>
					<option value="sku" <?php selected( 'sku', $this->sf_feed_options['product_identifier'] ? $this->sf_feed_options['product_identifier'] : false ); ?>>
						<?php esc_html_e( 'SKU', 'shopping-feed' ); ?>
					</option>
				</select>
				<p class="description"
				   id="tagline-description"><?php echo esc_attr_e( 'Product identifier', 'shopping-feed' ); ?></p>

				<?php
			},
			self::SF_FEED_SETTINGS_PAGE,
			'sf_feed_settings_categories'
		);

		//Identifier Field
		add_settings_field(
			'category_display_mode',
			__( 'Category Display Mode', 'shopping-feed' ),
			function () {
				?>
				<select name="<?php echo esc_html( sprintf( '%s[category_display_mode]', self::SF_FEED_OPTIONS ) ); ?>">
					<option value="normal" <?php selected( 'id', $this->sf_feed_options['category_display_mode'] ? $this->sf_feed_options['category_display_mode'] : false ); ?>>
						<?php esc_html_e( 'Normal', 'shopping-feed' ); ?>
					</option>
					<option value="breadcrumb" <?php selected( 'breadcrumb', $this->sf_feed_options['category_display_mode'] ? $this->sf_feed_options['category_display_mode'] : false ); ?>>
						<?php esc_html_e( 'Breadcrumb', 'shopping-feed' ); ?>
					</option>
				</select>
				<p class="description" id="tagline-description"><?php echo esc_attr_e( 'Category Display Mode', 'shopping-feed' ); ?></p>
				<?php
			},
			self::SF_FEED_SETTINGS_PAGE,
			'sf_feed_settings_categories'
		);

		//Categories
		add_settings_field(
			'categories',
			__( 'Categories to export', 'shopping-feed' ),
			function () use ( $product_categories ) {
				?>
				<select class="categories" multiple
						name='<?php echo esc_html( sprintf( '%s[categories][]', self::SF_FEED_OPTIONS ) ); ?>'>
					<?php
					foreach ( $product_categories as $category ) {
						?>
						<option value="<?php echo esc_html( $category->term_id ); ?>"
							<?php selected( in_array( $category->term_id, ! empty( $this->sf_feed_options['categories'] ) ? $this->sf_feed_options['categories'] : array() ), 1 ); ?>
						>
							<?php echo esc_html( $category->name ); ?></option>
						<?php
					}
					?>
				</select>
				<p class="description"
				   id="tagline-description"><?php echo esc_attr_e( 'Product categories to export to Shoppingfeed. Default : all', 'shopping-feed' ); ?></p>
				<?php

			},
			self::SF_FEED_SETTINGS_PAGE,
			'sf_feed_settings_categories'
		);

		/**
		 * Frequencies
		 */
		add_settings_section(
			'sf_feed_settings_frequency',
			__( 'Feed generation frequency', 'shopping-feed' ),
			function () {
				//Init feed actions after update
				Actions::clean_feed_generation();
				Actions::register_feed_generation();
			},
			self::SF_FEED_SETTINGS_PAGE
		);

		$frequencies_options = array();
		for ( $i = 1; $i <= 24; $i++ ) {
			$frequencies_options[ $i * HOUR_IN_SECONDS ] = sprintf(
				/* translators: %s: Frequency. */
				_n(
					'%s hour',
					'%s hours',
					$i,
					'shopping-feed'
				),
				number_format_i18n( $i )
			);
		}

		add_settings_field(
			'Frequency',
			__( 'Number of hours', 'shopping-feed' ),
			function () use ( $frequencies_options ) {
				?>
				<select name="<?php echo esc_html( sprintf( '%s[frequency]', self::SF_FEED_OPTIONS ) ); ?>">
					<?php
					foreach ( $frequencies_options as $frequency => $name ) {
						?>
						<option
								value="<?php echo esc_html( $frequency ); ?>"
							<?php selected( $frequency, $this->sf_feed_options['frequency'] ? $this->sf_feed_options['frequency'] : false ); ?>
						><?php echo esc_html( $name ); ?></option>
						<?php
					}
					?>
				</select>
				<p class="description"
				   id="tagline-description"><?php echo esc_attr_e( 'Frequency to generate the feed (usually 6h)', 'shopping-feed' ); ?></p>
				<?php
			},
			self::SF_FEED_SETTINGS_PAGE,
			'sf_feed_settings_frequency'
		);

		add_settings_field(
			'Batch size',
			__( 'Batch size', 'shopping-feed' ),
			function () {
				$running_process = ShoppingFeedHelper::get_running_process( 'sf_feed_generation_process' );
				$running_process = is_array( $running_process ) ? count( $running_process ) : 0;
				?>
				<select name="<?php echo esc_html( sprintf( '%s[part_size]', self::SF_FEED_OPTIONS ) ); ?>">
					<?php
					foreach ( array( 10, 20, 50, 100, 200, 500, 1000 ) as $part_size_option ) {
						?>
						<option
								value="<?php echo esc_html( $part_size_option ); ?>"
							<?php selected( $part_size_option, $this->sf_feed_options['part_size'] ? $this->sf_feed_options['part_size'] : false ); ?>
						><?php echo esc_html( $part_size_option ); ?></option>
						<?php
					}
					?>
				</select>
				<p class="description" id="tagline-description">
				   <?php esc_attr_e( 'Batch size (default 200 to decrease in case of performance issues)', 'shopping-feed' ); ?>
				</p>
				<p class="description">
					<?php esc_attr_e( 'If the feed is blocked and not generated', 'shopping-feed' ); ?>
					<a href="<?php echo sprintf( '%s&clean_process=true', esc_url( ShoppingFeedHelper::get_setting_link() ) ); ?>" class="button-link-delete"><?php esc_attr_e( 'click here', 'shopping-feed' ); ?></a>
					<?php esc_attr_e( 'to clean all running process', 'shopping-feed' ); ?>
					<?php echo esc_html( sprintf( '(%s)', $running_process ) ); ?>
				</p>
				<?php
			},
			self::SF_FEED_SETTINGS_PAGE,
			'sf_feed_settings_frequency'
		);

		add_settings_section(
			'sf_feed_settings',
			__( 'Shipping', 'shopping-feed' ),
			function () {
			},
			self::SF_FEED_SETTINGS_PAGE
		);

		$shipping_zones = \WC_Shipping_Zones::get_zones();
		$selected_shipping_zone = ! empty( $this->sf_shipping_options['zone'] ) ? $this->sf_shipping_options['zone'] : false;
		add_settings_field(
			'default_zone',
			__( 'Default Shipping Zone', 'shopping-feed' ),
			function () use ( $shipping_zones, $selected_shipping_zone ) {
				?>
					<input class="hidden" id="selected_shipping_zone" value="<?php echo esc_html( $selected_shipping_zone ); ?>">
					<select id="default_shipping_zone" name="<?php echo esc_html( sprintf( '%s[zone]', self::SF_SHIPPING_OPTIONS ) ); ?>">
						<option value=""><?php echo esc_attr_e( 'None', 'shopping-feed' ); ?></option>
						<?php
						if ( ! empty( $shipping_zones ) ) {
							foreach ( $shipping_zones as $zone ) {
								?>
							<option
									value="<?php echo esc_html( $zone['id'] ); ?>"
								<?php selected( $zone['id'], $selected_shipping_zone ); ?>
							><?php echo esc_html( $zone['zone_name'] ); ?></option>
								<?php
							}
							?>
					</select>
					<p class="description"
					   id="tagline-description"><?php echo esc_attr_e( 'Selected shipping zone defines shipping method data used in the feed', 'shopping-feed' ); ?></p>
							<?php
						}},
			self::SF_FEED_SETTINGS_PAGE,
			'sf_feed_settings'
		);

		add_settings_field(
			'shipping_fees',
			__( 'Default Shipping Fees', 'shopping-feed' ),
			function () {
				?>
				<input type="number"
					   id="shipping_fees"
					   step="any"
					   name='<?php echo esc_html( sprintf( '%s[fees]', self::SF_SHIPPING_OPTIONS ) ); ?>'
					   value='<?php echo esc_html( isset( $this->sf_shipping_options['fees'] ) ? esc_attr( $this->sf_shipping_options['fees'] ) : 0 ); ?>'>

				<p class="description"
				   id="tagline-description"><?php echo esc_attr_e( 'Default shipping price added in the feed if no shipping methods were founded or no shipping zone is selected', 'shopping-feed' ); ?></p>

				<?php
			},
			self::SF_FEED_SETTINGS_PAGE,
			'sf_feed_settings'
		);
		?>
		<div class="wrap">
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
				settings_fields( 'sf_feed_page_fields' );
				do_settings_sections( self::SF_FEED_SETTINGS_PAGE );
				submit_button( __( 'Save changes', 'shopping-feed' ), 'sf__button' );
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Define Shipping Page
	 */
	private function init_shipping_setting_page() {
		//load assets
		$this->load_assets();

		$zone_with_methods = ShoppingFeedHelper::get_zones_with_shipping_methods();
		$default_shipping_method = ShoppingFeedHelper::get_default_shipping_method();
		$sf_orders_options_default_shipping_method_id = ! empty( $default_shipping_method['method_id'] ) ? $default_shipping_method['method_id'] : false;

		add_settings_section(
			'sf_orders_settings_shippings_methods',
			__( 'Methods', 'shopping-feed' ),
			function () {
			},
			self::SF_SHIPPING_SETTINGS_PAGE
		);
		add_settings_field(
			'shipping_is_compatible_with_addons',
			__( 'Retrieval mode', 'shopping-feed' ),
			function () {
				?>
					<select id="retrieval_mode" name="<?php echo esc_html( sprintf( '%s[retrieval_mode]', self::SF_SHIPPING_OPTIONS ) ); ?>">
						<option value="ADDONS"
							<?php selected( 'ADDONS', $this->sf_shipping_options['retrieval_mode'] ? $this->sf_shipping_options['retrieval_mode'] : false ); ?>>Addons</option>
						<option value="METAS"
							<?php selected( 'METAS', $this->sf_shipping_options['retrieval_mode'] ? $this->sf_shipping_options['retrieval_mode'] : false ); ?>>Métas</option>
					</select>
					<p class="description"
					   id="tagline-description">
					   <?php echo esc_attr_e( 'How shipping information will be retrieved', 'shopping-feed' ); ?>
					   </p>
					<?php
			},
			self::SF_SHIPPING_SETTINGS_PAGE,
			'sf_orders_settings_shippings_methods'
		);

		add_settings_field(
			'default_shipping_method',
			__( 'Default Shipping Method', 'shopping-feed' ),
			function () use ( $zone_with_methods, $sf_orders_options_default_shipping_method_id ) {
				?>
					<select id="default_shipping_method" name="<?php echo esc_html( sprintf( '%s[default_shipping_method]', self::SF_SHIPPING_OPTIONS ) ); ?>">
						<option value=""><?php echo esc_attr_e( '-', 'shopping-feed' ); ?></option>
						<?php
						if ( ! empty( $zone_with_methods ) ) {
							foreach ( $zone_with_methods as $zone_with_method ) {
								?>
								<optgroup label="<?php echo esc_html( $zone_with_method['zone_name'] ); ?>">
								<?php
								if ( ! empty( $zone_with_method['methods'] ) ) {
									foreach ( $zone_with_method['methods'] as $shipping_method ) {
										?>
										<option value="<?php echo wc_esc_json( wp_json_encode( $shipping_method ) ); ?>"
										<?php selected( $shipping_method['method_id'], $sf_orders_options_default_shipping_method_id ); ?>>
										<?php echo sprintf( '%s', esc_html( $shipping_method['method_title'] ) ); ?>
										</option>
										<?php
									}
								}
								?>
								</optgroup>
								<?php
							}
						}
						?>
					</select>
					<p class="description"
					   id="tagline-description">
					   <?php echo esc_attr_e( 'Default shipping method for imported orders from SF', 'shopping-feed' ); ?>
					   </p>
					<?php
			},
			self::SF_SHIPPING_SETTINGS_PAGE,
			'sf_orders_settings_shippings_methods'
		);

		$sf_carriers = ShoppingFeedHelper::get_sf_carriers();
		$zone_with_methods = ShoppingFeedHelper::get_zones_with_shipping_methods();

		if ( ! empty( $sf_carriers ) ) {
			$matching_shipping_method = ! empty( $this->sf_shipping_options['matching_shipping_method'] ) ? $this->sf_shipping_options['matching_shipping_method'] : array();
			add_settings_section(
				'sf_orders_settings_shippings_methods_matching',
				__( 'Shipping Matching', 'shopping-feed' ),
				function () {
				},
				self::SF_SHIPPING_SETTINGS_PAGE
			);
			add_settings_field(
				'matching_shipping',
				'ShoppingFeed Carrier',
				function () {
					printf( '<strong>WooCommerce Shipping</strong>' );
				},
				self::SF_SHIPPING_SETTINGS_PAGE,
				'sf_orders_settings_shippings_methods_matching'
			);
			foreach ( $sf_carriers as $sf_carrier_id => $sf_carrier ) {
				$matching_shipping_method_carrier = ! empty( $matching_shipping_method[ $sf_carrier_id ] ) ? $matching_shipping_method[ $sf_carrier_id ] : false;
				add_settings_field(
					'matching_shipping_' . $sf_carrier_id,
					$sf_carrier,
					function () use ( $zone_with_methods, $sf_carrier_id, $matching_shipping_method_carrier ) {
						?>
						<select id="<?php echo esc_html( 'matching_shipping_' . $sf_carrier_id ); ?>" name="<?php echo esc_html( sprintf( '%s[matching_shipping_method][%s]', self::SF_SHIPPING_OPTIONS, $sf_carrier_id ) ); ?>">
							<option value=""><?php echo esc_attr_e( '-', 'shopping-feed' ); ?></option>
							<?php
							if ( ! empty( $zone_with_methods ) ) {
								foreach ( $zone_with_methods as $zone_with_method ) {
									?>
									<optgroup label="<?php echo esc_html( $zone_with_method['zone_name'] ); ?>">
									<?php
									if ( ! empty( $zone_with_method['methods'] ) ) {
										foreach ( $zone_with_method['methods'] as $shipping_method ) {
											$shipping_method['sf_shipping'] = $sf_carrier_id;
											?>
											<option value="<?php echo wc_esc_json( wp_json_encode( $shipping_method ) ); ?>"
											<?php selected( wp_json_encode( $shipping_method ), $matching_shipping_method_carrier ); ?>>
											<?php echo sprintf( '%s', esc_html( $shipping_method['method_title'] ) ); ?></option>>
											<?php echo sprintf( '%s', esc_html( $shipping_method['method_title'] ) ); ?></option>
											<?php
										}
									}
									?>
</optgroup>

															<?php
								}
							}
							?>
						</select>
						<?php
					},
					self::SF_SHIPPING_SETTINGS_PAGE,
					'sf_orders_settings_shippings_methods_matching'
				);
			}
		}

		?>
		<div class="wrap">
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
				settings_fields( 'sf_shipping_page_fields' );
				do_settings_sections( self::SF_SHIPPING_SETTINGS_PAGE );
				submit_button( __( 'Save changes', 'shopping-feed' ), 'sf__button' );
				?>
			</form>
		</div>
		<?php
	}

	private function init_orders_setting_page() {
		//load assets
		$this->load_assets();

		add_settings_section(
			'sf_orders_settings_import_frequency',
			__( 'Import Options', 'shopping-feed' ),
			function () {
				//Init orders actions after update
				Actions::clean_get_orders();
				Actions::register_get_orders();
			},
			self::SF_ORDERS_SETTINGS_PAGE
		);

		//cron settings
		$frequencies = array( 5, 10, 15, 30, 45, 60 );
		$frequencies_options = array();
		foreach ( $frequencies as $frequency ) {
			$frequencies_options[ $frequency * MINUTE_IN_SECONDS ] = sprintf( '%s %s', $frequency, __( 'min', 'shopping-feed' ) );
		}

		add_settings_field(
			'Frequency',
			__( 'Frequency', 'shopping-feed' ),
			function () use ( $frequencies_options ) {
				?>
				<select name="<?php echo esc_html( sprintf( '%s[import_frequency]', self::SF_ORDERS_OPTIONS ) ); ?>">
					<?php
					foreach ( $frequencies_options as $frequency => $name ) {
						?>
						<option
								value="<?php echo esc_html( $frequency ); ?>"
							<?php selected( $frequency, $this->sf_orders_options['import_frequency'] ? $this->sf_orders_options['import_frequency'] : false ); ?>
						><?php echo esc_html( $name ); ?></option>
						<?php
					}
					?>
				</select>
				<p class="description"
				   id="tagline-description"><?php echo esc_attr_e( 'Frequency to import orders from SF', 'shopping-feed' ); ?></p>
				<?php
			},
			self::SF_ORDERS_SETTINGS_PAGE,
			'sf_orders_settings_import_frequency'
		);

		//statuses settings
		$wc_order_statuses = wc_get_order_statuses();

		//default status
		add_settings_section(
			'sf_orders_settings_default_status',
			null,
			function () {
			},
			self::SF_ORDERS_SETTINGS_PAGE
		);

		add_settings_field(
			'Default Order Status',
			__( 'Default Order Status', 'shopping-feed' ),
			function () use ( $wc_order_statuses ) {
				?>
				<select name="<?php echo esc_html( sprintf( '%s[default_status]', self::SF_ORDERS_OPTIONS ) ); ?>">
					<?php
					foreach ( $wc_order_statuses as $wc_order_statuse => $name ) {
						?>
						<option
								value="<?php echo esc_html( $wc_order_statuse ); ?>"
							<?php selected( $wc_order_statuse, $this->sf_orders_options['default_status'] ? $this->sf_orders_options['default_status'] : false ); ?>
						><?php echo esc_html( $name ); ?></option>
						<?php
					}
					?>
				</select>
				<p class="description"
				   id="tagline-description"><?php echo esc_attr_e( 'Default Status for orders imported from from SF', 'shopping-feed' ); ?></p>
				<?php
			},
			self::SF_ORDERS_SETTINGS_PAGE,
			'sf_orders_settings_default_status'
		);

		//mapping
		$sf_actions = Operations::get_available_operations();

		add_settings_section(
			'sf_orders_settings_actions',
			__( 'Synchronization', 'shopping-feed' ),
			function () {
			},
			self::SF_ORDERS_SETTINGS_PAGE
		);
		foreach ( $sf_actions as $sf_action => $name ) {
			add_settings_field(
				$name,
				$name,
				function () use ( $sf_action, $wc_order_statuses ) {
					?>
					<select class="statuses_actions" multiple
							name="<?php echo esc_html( sprintf( '%s[statuses_actions][%s][]', self::SF_ORDERS_OPTIONS, $sf_action ) ); ?>">
						<?php
						foreach ( $wc_order_statuses as $wc_status => $name ) {
							?>
							<option value="<?php echo esc_html( $wc_status ); ?>"
								<?php selected( in_array( $wc_status, ! empty( $this->sf_orders_options['statuses_actions'][ $sf_action ] ) ? $this->sf_orders_options['statuses_actions'][ $sf_action ] : array() ), 1 ); ?>

							><?php echo esc_html( $name ); ?></option>
						<?php } ?>
					</select>
					<p class="description"
					   id="tagline-description"><?php echo esc_attr_e( 'Selected Status will send update on Shoppingfeed', 'shopping-feed' ); ?></p>
					<?php
				},
				self::SF_ORDERS_SETTINGS_PAGE,
				'sf_orders_settings_actions'
			);
		}

		?>
		<div class="wrap">
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
				settings_fields( 'sf_orders_page_fields' );
				do_settings_sections( self::SF_ORDERS_SETTINGS_PAGE );
				submit_button( __( 'Save changes', 'shopping-feed' ), 'sf__button' );
				?>
			</form>
		</div>
		<?php
	}

}
