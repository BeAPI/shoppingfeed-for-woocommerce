<?php

namespace ShoppingFeed\ShoppingFeedWC\Admin;

defined( 'ABSPATH' ) || exit;

use ShoppingFeed\ShoppingFeedWC\Products\ProductFeedDiagnostic;

/**
 * Product feed diagnostic tool under WordPress Tools menu.
 */
class FeedDiagnosticPage {

	const PAGE_SLUG = 'shopping-feed-diagnostics';

	/**
	 * Register hooks.
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'register_menu' ] );
	}

	/**
	 * Add the page under Tools.
	 */
	public function register_menu() {
		add_management_page(
			__( 'ShoppingFeed Diagnostics', 'shopping-feed' ),
			__( 'ShoppingFeed Diagnostics', 'shopping-feed' ),
			'manage_options',
			self::PAGE_SLUG,
			[ $this, 'render_page' ]
		);
	}

	/**
	 * Render the diagnostic form and results.
	 */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$product_id = 0;
		$result     = null;

		if (
			isset( $_POST['sf_diagnose_product_nonce'] )
			&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sf_diagnose_product_nonce'] ) ), 'sf_diagnose_product' )
		) {
			$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;

			if ( $product_id > 0 ) {
				$diagnostic = new ProductFeedDiagnostic();
				$result     = $diagnostic->diagnose( $product_id );
			}
		}

		$page_url = admin_url( 'tools.php?page=' . self::PAGE_SLUG );
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<form method="post" action="<?php echo esc_url( $page_url ); ?>">
				<h2><?php esc_html_e( 'Product feed diagnostic', 'shopping-feed' ); ?></h2>
				<p class="description">
					<?php esc_html_e( 'Enter a product ID to see why it is included in or excluded from the feed query and generation pipeline.', 'shopping-feed' ); ?>
				</p>
				<table class="form-table" role="presentation">
					<tbody>
					<tr>
						<th scope="row">
							<label for="sf_diagnose_product_id"><?php esc_html_e( 'Product ID', 'shopping-feed' ); ?></label>
						</th>
						<td>
							<input type="number"
								   min="1"
								   step="1"
								   id="sf_diagnose_product_id"
								   name="product_id"
								   value="<?php echo esc_attr( (string) $product_id ); ?>"
								   required
							>
						</td>
					</tr>
					</tbody>
				</table>
				<?php wp_nonce_field( 'sf_diagnose_product', 'sf_diagnose_product_nonce' ); ?>
				<?php submit_button( __( 'Run diagnostic', 'shopping-feed' ) ); ?>
			</form>

			<?php if ( is_array( $result ) ) : ?>
				<?php
				$failed_checks = array_filter(
					$result['checks'],
					static function ( $check ) {
						return empty( $check['pass'] );
					}
				);
				$is_included = ! empty( $result['in_query'] ) && empty( $failed_checks );

				$included_variations_count = 0;
				$excluded_variations_count = 0;
				if ( ! empty( $result['variations'] ) ) {
					foreach ( $result['variations'] as $variation ) {
						if ( ! empty( $variation['included'] ) ) {
							++$included_variations_count;
						} else {
							++$excluded_variations_count;
						}
					}
				}
				?>
				<hr>
				<h2>
					<?php
					echo esc_html(
						sprintf(
							/* translators: 1: product ID, 2: product name */
							__( 'Results for product #%1$d — %2$s', 'shopping-feed' ),
							$result['product_id'],
							$result['name'] ? $result['name'] : __( '(unknown)', 'shopping-feed' )
						)
					);
					?>
				</h2>

				<?php if ( $is_included ) : ?>
					<div class="notice notice-success inline">
						<p>
							<strong><?php esc_html_e( 'This product is included in the feed.', 'shopping-feed' ); ?></strong>
							<?php if ( $included_variations_count > 0 || $excluded_variations_count > 0 ) : ?>
								<?php
								echo esc_html(
									sprintf(
										/* translators: 1: included variations count, 2: excluded variations count */
										__( 'Variations: %1$d included, %2$d excluded.', 'shopping-feed' ),
										$included_variations_count,
										$excluded_variations_count
									)
								);
								?>
							<?php endif; ?>
						</p>
					</div>
				<?php else : ?>
					<div class="notice notice-error inline">
						<p>
							<strong><?php esc_html_e( 'This product is excluded from the feed.', 'shopping-feed' ); ?></strong>
							<?php esc_html_e( 'See the checks below for the reason.', 'shopping-feed' ); ?>
						</p>
					</div>
				<?php endif; ?>

				<p>
					<strong><?php esc_html_e( 'In feed query:', 'shopping-feed' ); ?></strong>
					<?php
					echo ! empty( $result['in_query'] )
						? esc_html__( 'yes', 'shopping-feed' )
						: esc_html__( 'no', 'shopping-feed' );
					?>
					<?php if ( ! empty( $result['sku'] ) ) : ?>
						— <strong><?php esc_html_e( 'SKU:', 'shopping-feed' ); ?></strong> <?php echo esc_html( $result['sku'] ); ?>
					<?php endif; ?>
					<?php if ( ! empty( $result['type'] ) ) : ?>
						— <strong><?php esc_html_e( 'Type:', 'shopping-feed' ); ?></strong> <?php echo esc_html( $result['type'] ); ?>
					<?php endif; ?>
					— <strong><?php esc_html_e( 'Language:', 'shopping-feed' ); ?></strong>
					<?php
					echo ! empty( $result['language'] )
						? esc_html( $result['language'] )
						: esc_html__( 'n/a', 'shopping-feed' );
					?>
				</p>

				<table class="widefat striped" style="max-width: 960px;">
					<thead>
					<tr>
						<th><?php esc_html_e( 'Check', 'shopping-feed' ); ?></th>
						<th><?php esc_html_e( 'Result', 'shopping-feed' ); ?></th>
						<th><?php esc_html_e( 'Details', 'shopping-feed' ); ?></th>
					</tr>
					</thead>
					<tbody>
					<?php foreach ( $result['checks'] as $check ) : ?>
						<tr>
							<td><?php echo esc_html( $check['label'] ); ?></td>
							<td>
								<?php if ( ! empty( $check['pass'] ) ) : ?>
									<span style="color:#007017;font-weight:600;"><?php esc_html_e( 'OK', 'shopping-feed' ); ?></span>
								<?php else : ?>
									<span style="color:#b32d2e;font-weight:600;"><?php esc_html_e( 'FAIL', 'shopping-feed' ); ?></span>
								<?php endif; ?>
							</td>
							<td><?php echo esc_html( $check['detail'] ); ?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>

				<?php if ( ! empty( $result['variations'] ) ) : ?>
					<h3><?php esc_html_e( 'Variations', 'shopping-feed' ); ?></h3>
					<table class="widefat striped" style="max-width: 960px;">
						<thead>
						<tr>
							<th><?php esc_html_e( 'Variation ID', 'shopping-feed' ); ?></th>
							<th><?php esc_html_e( 'SKU', 'shopping-feed' ); ?></th>
							<th><?php esc_html_e( 'Included', 'shopping-feed' ); ?></th>
							<th><?php esc_html_e( 'Reason', 'shopping-feed' ); ?></th>
						</tr>
						</thead>
						<tbody>
						<?php foreach ( $result['variations'] as $variation ) : ?>
							<tr>
								<td><?php echo esc_html( (string) $variation['id'] ); ?></td>
								<td><?php echo esc_html( $variation['sku'] ); ?></td>
								<td>
									<?php
									echo ! empty( $variation['included'] )
										? esc_html__( 'yes', 'shopping-feed' )
										: esc_html__( 'no', 'shopping-feed' );
									?>
								</td>
								<td><?php echo esc_html( $variation['reason'] ); ?></td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			<?php elseif ( isset( $_POST['sf_diagnose_product_nonce'] ) && 0 === $product_id ) : ?>
				<div class="notice notice-error inline"><p><?php esc_html_e( 'Please enter a valid product ID.', 'shopping-feed' ); ?></p></div>
			<?php endif; ?>
		</div>
		<?php
	}
}
