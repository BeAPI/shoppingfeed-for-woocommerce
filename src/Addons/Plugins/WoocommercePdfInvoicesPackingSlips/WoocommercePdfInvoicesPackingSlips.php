<?php

namespace ShoppingFeed\ShoppingFeedWC\Addons\Plugins\WoocommercePdfInvoicesPackingSlips;

// Exit on direct access
use ShoppingFeed\ShoppingFeedWC\ShoppingFeedHelper;

defined( 'ABSPATH' ) || exit;

/**
 * Class WoocommercePdfInvoicesPackingSlips is used to get the invoice file for the order.
 *
 * @link https://wordpress.org/plugins/woocommerce-pdf-invoices-packing-slips/
 * @package ShoppingFeed\ShoppingFeedWC\Addons\Plugins\WoocommercePdfInvoicesPackingSlips
 */
class WoocommercePdfInvoicesPackingSlips {
	public function __construct() {
		if ( ! class_exists( '\WPO_WCPDF' ) ) {
			return;
		}

		add_filter( 'sf_order_invoice_filepath', array( $this, 'get_order_invoice' ), 5, 2 );
	}

	/**
	 * Get the invoice file for the order.
	 *
	 * @param string|null $order_invoice_filepath
	 * @param \WC_Order $order
	 *
	 * @return string|null
	 */
	public function get_order_invoice( $order_invoice_filepath, $order ) {
		ShoppingFeedHelper::get_logger()->debug(
			sprintf(
				'Check for order invoice to upload.'
			),
			[
				'source' => 'shopping-feed',
				'order'  => $order->get_id(),
			]
		);

		// Don't override existing invoices.
		if ( null !== $order_invoice_filepath ) {
			ShoppingFeedHelper::get_logger()->info(
				sprintf(
					'An invoice file has been provided.'
				),
				[
					'source'           => 'shopping-feed',
					'order'            => $order->get_id(),
					'invoice_filepath' => str_replace( WP_CONTENT_DIR, '', $order_invoice_filepath ),
				]
			);

			return $order_invoice_filepath;
		}

		try {
			$invoice = wcpdf_get_invoice( $order );
			if ( $invoice ) {
				ShoppingFeedHelper::get_logger()->debug(
					sprintf(
						'An invoice is available for the order.'
					),
					[
						'source' => 'shopping-feed',
						'order'  => $order->get_id(),
					]
				);

				$order_invoice_filepath = wcpdf_get_document_file( $invoice );

				ShoppingFeedHelper::get_logger()->info(
					sprintf(
						'Successfully got the invoice file for the order.'
					),
					[
						'source'           => 'shopping-feed',
						'order'            => $order->get_id(),
						'invoice_filepath' => str_replace( WP_CONTENT_DIR, '', $order_invoice_filepath ),
					]
				);
			}
		} catch ( \Exception $exception ) {
			ShoppingFeedHelper::get_logger()->error(
				sprintf(
					'An error occurred while trying to get document file.'
				),
				[
					'source' => 'shopping-feed',
					'order'  => $order->get_id(),
				]
			);
		}

		return $order_invoice_filepath;
	}
}
