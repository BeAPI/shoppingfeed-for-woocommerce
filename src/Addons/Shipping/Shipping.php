<?php

namespace ShoppingFeed\ShoppingFeedWC\Addons\Shipping;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

use ShoppingFeed\ShoppingFeedWC\Addons\Shipping\Marketplaces\Cdiscount;
use ShoppingFeed\ShoppingFeedWC\Addons\Shipping\Marketplaces\Rdc;
use ShoppingFeed\ShoppingFeedWC\Orders\Order\Metas;

/**
 * Class Shipping
 * @package ShoppingFeed\ShoppingFeedWC\Addons\Shipping
 */
class Shipping {

	/**
	 * Rdc class
	 * @var Rdc
	 */
	private $rdc;

	/**
	 * Cdiscount class
	 * @var Cdiscount
	 */
	private $cdiscount;

	public function __construct() {
		$this->rdc       = new Rdc();
		$this->cdiscount = new Cdiscount();

		add_action( 'sf_add_metas', array( $this, 'add_metas' ) );
		add_action( 'sf_show_metas', array( $this, 'show_metas' ) );
	}

	/**
	 * @param $metas Metas
	 */
	public function add_metas( $metas ) {
		if ( empty( $metas->shipping->get_colis_number() ) ) {
			return;
		}

		$metas->add_meta(
			'sf_relais_colis',
			$metas->shipping->get_colis_number()
		);
	}

	/**
	 * @param $wc_order \WC_Order
	 */
	public function show_metas( $wc_order ) {
		$relais_colis = $wc_order->get_meta( 'Relais Colis' );
		if ( empty( $relais_colis ) ) {
			return;
		}

		?>
		<li>
			<span><?php esc_html_e( 'Relais Colis', 'shopping-feed' ); ?>:</span> <?php echo esc_html( $relais_colis ); ?>
		</li>
		<?php
	}
}
