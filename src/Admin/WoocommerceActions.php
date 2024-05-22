<?php

namespace ShoppingFeed\ShoppingFeedWC\Admin;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

use Exception;
use ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Api\Catalog\{InventoryUpdate, PricingUpdate};
use ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Api\Store\StoreResource;
use ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Client;
use ShoppingFeed\ShoppingFeedWC\Dependencies\ShoppingFeed\Sdk\Credential;
use ShoppingFeed\ShoppingFeedWC\Feed\AsyncGenerator;
use ShoppingFeed\ShoppingFeedWC\Orders\Operations;
use ShoppingFeed\ShoppingFeedWC\Orders\Order;
use ShoppingFeed\ShoppingFeedWC\Orders\Orders;
use ShoppingFeed\ShoppingFeedWC\Products\Product;
use ShoppingFeed\ShoppingFeedWC\Query\Query;
use ShoppingFeed\ShoppingFeedWC\Sdk\Sdk;
use ShoppingFeed\ShoppingFeedWC\ShoppingFeedHelper;

/**
 * Custom WooCommerce actions.
 *
 */
class WoocommerceActions {

	/**
	 * @var PricingUpdate
	 */
	private $pricing_update;

	/**
	 * @var InventoryUpdate
	 */
	private $inventory_update;


	public function __construct() {

		//Generate async feed
		add_action(
			'sf_feed_generation_process',
			[
				AsyncGenerator::get_instance(),
				'launch',
			]
		);

		//Generate feed part
		add_action(
			'sf_feed_generation_part',
			[
				AsyncGenerator::get_instance(),

				'generate_feed_part',
			],
			10,
			3
		);

		//Combine feed's parts
		add_action(
			'sf_feed_generation_combine_feed_parts',
			array(
				AsyncGenerator::get_instance(),
				'combine_feed_parts',
			)
		);

		//Product Update
		add_action( 'woocommerce_update_product', array( $this, 'update_product' ) );

		//Stock Update
		add_action( 'woocommerce_updated_product_stock', array( $this, 'update_stock' ) );

		//Feed Generation
		add_action(
			'sf_generate_feed_action',
			function () {
				if ( ShoppingFeedHelper::is_process_running( 'sf_feed_generation_process' ) ) {
					ShoppingFeedHelper::get_logger()->warning(
						sprintf(
							__( 'Feed generation already running', 'shopping-feed' )
						),
						array(
							'source' => 'shopping-feed',
						)
					);

					return true;
				}

				AsyncGenerator::get_instance()->launch();

				return true;
			}
		);

		//Get Orders
		$sf_accounts = ShoppingFeedHelper::get_sf_account_options();
		if ( empty( $sf_accounts ) ) {
			ShoppingFeedHelper::get_logger()->error(
				sprintf(
					__( 'No Accounts founds', 'shopping-feed' )
				),
				array(
					'source' => 'shopping-feed',
				)
			);
		} else {
			foreach ( $sf_accounts as $key => $sf_account ) {
				add_action(
					'sf_get_orders_action_' . $key,
					function ( $sf_account ) {
						Orders::get_instance()->get_orders( $sf_account );
					}
				);
			}

			add_action(
				'sf_get_orders_action_custom',
				function ( $sf_username, $since ) {
					$sf_account = ShoppingFeedHelper::get_sf_account_credentials_by_username( $sf_username );
					if ( empty( $sf_account ) ) {
						return new \WP_Error( 'sf-invalid-account', 'no account fot the username.' );
					}
					Orders::get_instance()->get_orders( $sf_account, $since );
				},
				10,
				2
			);
		}

		//Acknowledge Remain Order
		add_action(
			'sf_acknowledge_remain_order',
			function ( $order_id, $message ) {
				Operations::acknowledge_order( $order_id, $message );
			},
			10,
			2
		);

		/**
		 * Migrate old accounts to multi account format
		 */
		add_action( 'sf_migrate_single_action', array( $this, 'migrate_old_accounts' ) );

		/**
		 * Orders Statuses Mapping
		 * Each status with action
		 * @see Operations
		 */
		$statuses_actions = ShoppingFeedHelper::get_sf_statuses_actions();
		if ( empty( $statuses_actions ) ) {
			return;
		}
		foreach ( $statuses_actions as $sf_action => $wc_statuses ) {
			if ( ! is_array( $wc_statuses ) || '' === $sf_action ) {
				continue;
			}

			foreach ( $wc_statuses as $wc_status ) {
				$status = str_replace( 'wc-', '', $wc_status );
				$action = 'woocommerce_order_status_' . $status;
				add_action(
					$action,
					function ( $order_id ) use ( $sf_action ) {
						//if its not a sf order
						if ( ! Order::is_sf_order( $order_id ) ) {
							return;
						}
						try {
							$operations = new Operations( $order_id );
							if ( ! method_exists( $operations, $sf_action ) ) {
								ShoppingFeedHelper::get_logger()->error(
									sprintf(
									/* translators: %s: Action name. */
										__( 'Cant find any matched method for %s', 'shopping-feed' ),
										$sf_action
									),
									array(
										'source' => 'shopping-feed',
									)
								);

								return;
							}
							call_user_func( array( $operations, $sf_action ) );
						} catch ( Exception $exception ) {
							wc_get_order( $order_id )->add_order_note(
								sprintf(
								/* translators: %s: Action */
									__( 'Failed to %s order', 'shopping-feed' ),
									ucfirst( $sf_action )
								)
							);
							ShoppingFeedHelper::get_logger()->error(
								sprintf(
								/* translators: %1$s: Action. %2$s: Error Message */
									__( 'Failed to %1$s order => %2$s', 'shopping-feed' ),
									ucfirst( $sf_action ),
									$exception->getMessage()
								),
								array(
									'source' => 'shopping-feed',
								)
							);
						}
					}
				);
			}
		}
	}


	/**
	 * Update product inventory
	 *
	 * @param $product_id
	 */
	public function update_stock( $product_id ) {
		$this->update_product( $product_id, true );
	}

	/**
	 * Update product's price & inventory
	 *
	 * @param $product_id
	 * @param bool $only_stock
	 */
	public function update_product( $product_id, $only_stock = false ) {
		$sf_feed_options = ShoppingFeedHelper::get_sf_feed_options();
		$sync_stock = $sf_feed_options['synchro_stock'] ?? 'yes';
		$sync_price = $sf_feed_options['synchro_price'] ?? 'yes';

		/**
		 * If both stock and price synchronization are disable, bail out.
		 */
		if ( 'no' === $sync_stock && 'no' === $sync_price ) {
			return;
		}

		/**
		 * If we are only syncing stock and the synchronization is disabled, bail out.
		 */
		if ( $only_stock && 'no' === $sync_stock ) {
			return;
		}

		$sf_accounts = ShoppingFeedHelper::get_sf_account_options();
		if ( empty( $sf_accounts ) ) {
			ShoppingFeedHelper::get_logger()->error(
				sprintf(
					__( 'No Accounts founds', 'shopping-feed' )
				),
				[
					'source' => 'shopping-feed',
				]
			);

			return;
		}

		foreach ( $sf_accounts as $sf_account ) {
			$shop = Sdk::get_sf_shop( $sf_account );
			if ( ! $shop instanceof StoreResource ) {
				ShoppingFeedHelper::get_logger()->error(
					sprintf(
					/* translators: %s: Error message. */
						__( 'Cannot retrieve shop from SDK for account : %s', 'shopping-feed' ),
						$sf_account['username']
					),
					[
						'source' => 'shopping-feed',
					]
				);
				continue;
			}

			$pricing_api   = $shop->getPricingApi();
			$inventory_api = $shop->getInventoryApi();

			$this->pricing_update   = new PricingUpdate();
			$this->inventory_update = new InventoryUpdate();

			$product = new Product( $product_id );
			if ( $product->has_variations() ) {
				if ( ! $this->update_variation_product( $product ) ) {
					continue;
				}
			} else {
				if ( ! $this->update_normal_product( $product ) ) {
					continue;
				}
			}

			/**
			 * Send api request to update the price if the flag `$only_stock` if false and the sync option is enabled.
			 */
			if ( ! $only_stock && 'yes' === $sync_price ) {
				$pricing_api->execute( $this->pricing_update );
			}

			/**
			 * Send api request to update the stock if the sync option is enabled.
			 */
			if ( 'yes' === $sync_stock ) {
				$inventory_api->execute( $this->inventory_update );
			}
		}
	}

	/**
	 * @param Product $product
	 *
	 * @psalm-suppress all
	 */
	public function update_normal_product( $product ) {
		if ( empty( $product->get_sku() ) ) {
			ShoppingFeedHelper::get_logger()->warning(
				sprintf(
				/* translators: %s: Product ID. */
					__( 'Cant update product without SKU => %s', 'shopping-feed' ),
					$product->get_wc_product()->get_id()
				),
				array(
					'source' => 'shopping-feed',
				)
			);

			return false;
		}

		$this->may_update_pricing( $product->get_sku(), ! empty( $product->get_discount() ) ? $product->get_discount() : $product->get_price() );
		$this->may_update_inventory( $product->get_sku(), $product->get_quantity() );

		return true;
	}

	/**
	 * @param Product $product
	 *
	 * @psalm-suppress all
	 * @return bool
	 */
	public function update_variation_product( $product ) {
		foreach ( $product->get_variations() as $variation ) {
			if ( empty( $variation['sku'] ) ) {
				ShoppingFeedHelper::get_logger()->warning(
					sprintf(
					/* translators: %s: Product ID. */
						__( 'Cant update product without SKU => %s', 'shopping-feed' ),
						$product->get_wc_product()->get_id()
					),
					array(
						'source' => 'shopping-feed',
					)
				);

				return false;
			}

			$this->may_update_pricing( $variation['sku'], ! empty( $variation['discount'] ) ? $variation['discount'] : $variation['price'] );
			$this->may_update_inventory( $variation['sku'], $variation['quantity'] );
		}

		return true;
	}

	/**
	 * Update price in SF if we have the price locally
	 *
	 * @param $sku
	 * @param $price
	 */
	private function may_update_pricing( $sku, $price ) {
		if ( empty( $price ) ) {
			ShoppingFeedHelper::get_logger()->warning(
				sprintf(
				/* translators: %s: Product SKU. */
					__( 'Cant update product without price => %s', 'shopping-feed' ),
					$sku
				),
				array(
					'source' => 'shopping-feed',
				)
			);

			return;
		}
		$this->pricing_update->add( $sku, $price );
	}

	/**
	 * Update quantity in SF if we have the quantity locally
	 *
	 * @param $sku
	 * @param $inventory
	 */
	private function may_update_inventory( $sku, $inventory ) {
		if ( ! is_numeric( $inventory ) ) {
			ShoppingFeedHelper::get_logger()->warning(
				sprintf(
				/* translators: %s: Product SKU. */
					__( 'Cant update product without quantity => %s', 'shopping-feed' ),
					$sku
				),
				array(
					'source' => 'shopping-feed',
				)
			);

			return;
		}
		$this->inventory_update->add( $sku, intval( $inventory ) );
	}

	/**
	 * @return bool
	 * @throws Exception
	 * @psalm-suppress all
	 */
	public function migrate_old_accounts() {
		//migrate account data and retrieve account ID
		$account_options = ShoppingFeedHelper::get_sf_account_options();

		if ( empty( $account_options['token'] ) ) {
			ShoppingFeedHelper::get_logger()->error(
				sprintf(
					__( 'No token founds', 'shopping-feed' )
				),
				array(
					'source' => 'shopping-feed',
				)
			);
			ShoppingFeedHelper::end_upgrade();

			return false;
		}
		try {
			$main_store = Client\Client::createSession( new Credential\Token( $account_options['token'] ) )->getMainStore();
			if ( is_null( $main_store ) ) {
				ShoppingFeedHelper::get_logger()->error(
					sprintf(
						__( 'Cant retrieve main store', 'shopping-feed' )
					),
					array(
						'source' => 'shopping-feed',
					)
				);

				ShoppingFeedHelper::end_upgrade();

				return false;
			}
			$account_id            = $main_store->getId();
			$new_account_options   = array();
			$new_account_options[] = array(
				'sf_store_id' => $account_id,
				'username'    => ! empty( $account_options['username'] ) ? $account_options['username'] : '',
				'password'    => ! empty( $account_options['password'] ) ? $account_options['password'] : '',
				'token'       => $account_options['token'],
			);
			ShoppingFeedHelper::set_sf_account_options( $new_account_options );
		} catch ( \Exception $exception ) {
			ShoppingFeedHelper::get_logger()->error(
				sprintf(
				/* translators: %s: Error message. */
					__( 'Cant login with actual credentials => %s', 'shopping-feed' ),
					$exception->getMessage()
				),
				array(
					'source' => 'shopping-feed',
				)
			);

			ShoppingFeedHelper::end_upgrade();

			return false;
		}

		//Migrate old orders to default account
		$args = array(
			'limit'        => - 1,
			'meta_key'     => Query::WC_META_SF_REFERENCE,
			'meta_compare' => 'EXISTS',
		);

		$query     = new \WC_Order_Query( $args );
		$wc_orders = $query->get_orders();
		if ( empty( $wc_orders ) ) {
			ShoppingFeedHelper::get_logger()->error(
				sprintf(
					__( 'No SF orders founds for migration', 'shopping-feed' )
				),
				array(
					'source' => 'shopping-feed',
				)
			);
			ShoppingFeedHelper::end_upgrade();

			return false;
		}

		foreach ( $wc_orders as $wc_order ) {
			//add store id
			/** @var \WC_Order $wc_order */
			$wc_order->add_meta_data( Query::WC_META_SF_STORE_ID, $account_id );
			$wc_order->save();
		}

		ShoppingFeedHelper::end_upgrade();

		return true;
	}
}
