<?php
namespace ShoppingFeed\ShoppingFeedWC\Orders;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

use Exception;
use ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Api\Order\{OrderDomain,
	OrderOperation,
	OrderOperationResult};
use ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Api\Store\StoreResource;
use ShoppingFeed\ShoppingFeedWC\Sdk\Sdk;
use ShoppingFeed\ShoppingFeedWC\ShoppingFeedHelper;

class Operations {

	/**
	 * @var OrderDomain
	 */
	private $order_api;

	/**
	 * @var OrderOperation
	 */
	private $order_operation;

	/**
	 * @var string
	 */
	private $sf_reference;

	/**
	 * @var string
	 */
	private $sf_channel_name;

	/**
	 * @var bool
	 */
	private $validate_operation = false;

	/**
	 * @var \WC_Order $wc_order
	 */
	private $wc_order;

	/**
	 * Operations constructor.
	 *
	 * @param $order_id
	 *
	 * @throws Exception
	 */
	public function __construct( $order_id ) {
		//Check if the order from SF and return it with metas data
		$order_sf_metas = Order::get_order_sf_metas( $order_id );
		if (
			empty( $order_sf_metas ) ||
			empty( $order_sf_metas['sf_store_id'] )
		) {
			throw new Exception(
				sprintf(
				/* translators: %s: Error message. */
					__( 'No SF order found in %s', 'shopping-feed' ),
					$order_id
				)
			);
		}

		$account_id = $order_sf_metas['sf_store_id'];

		$shop = Sdk::get_sf_account_shop( $account_id );

		if ( ! $shop instanceof StoreResource ) {
			ShoppingFeedHelper::get_logger()->error(
				sprintf(
				/* translators: %s: Error message. */
					__( 'Cannot retrieve shop from SDK for account : %s', 'shopping-feed' ),
					$account_id
				),
				array(
					'source' => 'shopping-feed',
				)
			);
			throw new Exception(
				__( 'No store found', 'shopping-feed' )
			);
		}

		$this->wc_order           = $order_sf_metas['order'];
		$this->order_api          = $shop->getOrderApi();
		$this->order_operation    = new OrderOperation();
		$this->sf_reference       = (string) $order_sf_metas['sf_reference'];
		$this->sf_channel_name    = (string) $order_sf_metas['sf_channel_name'];
		$this->validate_operation = true;
	}

	/**
	 * Cancel order in SF
	 * @return void
	 */
	public function cancel() {
		if ( ! $this->validate_operation ) {
			return;
		}

		try {
			$this->order_operation->cancel(
				$this->sf_reference,
				$this->sf_channel_name
			);
			$this->order_api->execute( $this->order_operation );
		} catch ( Exception $exception ) {
			ShoppingFeedHelper::get_logger()->error(
				sprintf(
				/* translators: %s: Error message. */
					__( 'Failed to cancel sf order %s', 'shopping-feed' ),
					$exception->getMessage()
				),
				array(
					'source' => 'shopping-feed',
				)
			);
		}
	}

	/**
	 * Ship order in SF
	 * @return void
	 */
	public function ship() {
		if ( ! $this->validate_operation ) {
			return;
		}

		try {
			$this->order_operation->ship(
				$this->sf_reference,
				$this->sf_channel_name,
				ShoppingFeedHelper::get_sf_carrier_from_wc_shipping( $this->wc_order ),
				ShoppingFeedHelper::wc_tracking_number( $this->wc_order ),
				ShoppingFeedHelper::wc_tracking_link( $this->wc_order )
			);
			$this->order_api->execute( $this->order_operation );
		} catch ( Exception $exception ) {
			ShoppingFeedHelper::get_logger()->error(
				sprintf(
				/* translators: %s: Error message. */
					__( 'Failed to ship sf order %s', 'shopping-feed' ),
					$exception->getMessage()
				),
				array(
					'source' => 'shopping-feed',
				)
			);
		}
	}

	/**
	 * Acknowledge order in SF
	 *
	 * @param string $message
	 *
	 * @return OrderOperationResult
	 * @throws Exception
	 */
	private function acknowledge( $message = '' ) {
		if ( ! $this->validate_operation ) {
			throw new Exception();
		}

		try {
			$this->order_operation->acknowledge(
				$this->sf_reference,
				$this->sf_channel_name,
				(string) $this->wc_order->get_id(),
				! empty( $message ) ? 'error' : 'success',
				$message
			);

			return $this->order_api->execute( $this->order_operation );
		} catch ( Exception $exception ) {
			throw $exception;
		}
	}

	/**
	 * Acknowledge order
	 *
	 * @param $order_id
	 * @param $message
	 */
	public static function acknowledge_order( $order_id, $message ) {
		$ok = true;
		try {
			$operations = new self( $order_id );
			if ( ! $operations->acknowledge( $message ) instanceof OrderOperationResult ) {
				$ok = false;
			}
		} catch ( Exception $exception ) {
			$ok = false;
		}

		if ( false === $ok ) {
			//if we cant acknowledge order => add action after 15 min
			as_schedule_single_action(
				MINUTE_IN_SECONDS * 15,
				'sf_acknowledge_remain_order',
				array(
					$order_id,
					$message,
				),
				'sf_orders'
			);

			ShoppingFeedHelper::get_logger()->error(
				sprintf(
				/* translators: %s: Error message. */
					__( 'Failed to acknowledge order %s, we will retry in 15 minutes', 'shopping-feed' ),
					$order_id
				),
				array(
					'source' => 'shopping-feed',
				)
			);
		}
	}

	/**
	 * Return Available operations
	 * @return array
	 */
	public static function get_available_operations() {
		return array(
			'cancel' => __( 'Canceled orders', 'shopping-feed' ),
			'ship'   => __( 'Shipped orders', 'shopping-feed' ),
		);
	}
}
