<?php

namespace ShoppingFeed\ShoppingFeedWC\Addons\Marketplaces;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

use ShoppingFeed\ShoppingFeedWC\Addons\Marketplaces\Types\AmazonBusiness;
use ShoppingFeed\ShoppingFeedWC\Addons\Marketplaces\Types\AmazonPrime;

/**
 * Class Shipping
 * @package ShoppingFeed\ShoppingFeedWC\Addons\Shipping
 */
class Marketplaces {

	/**
	 * @var AmazonBusiness
	 */
	private $amazon_business;

	/**
	 * @var AmazonPrime
	 */
	private $amazon_prime;

	public function __construct() {
		$this->amazon_business = new AmazonBusiness();
		$this->amazon_prime    = new AmazonPrime();

		add_action( 'sf_show_metas', array( $this, 'show_metas' ) );
	}

	/**
	 * @param $wc_order \WC_Order
	 */
	public function show_metas( $wc_order ) {
		$type = $wc_order->get_meta( 'sf_type' );
		if ( empty( $type ) ) {
			return;
		}

		?>
		<li>
			<span><?php esc_html_e( 'Type', 'shopping-feed' ); ?>:</span> <?php echo esc_html( $type ); ?>
		</li>
		<?php
	}
}
