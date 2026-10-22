<?php

namespace ShoppingFeed\ShoppingFeedWC\Addons\Advanced;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

/**
 * EAN field on product edit screen.
 */
class Fields {

	/** @var array */
	private $fields;

	public function __construct() {
		add_action(
			'woocommerce_product_options_inventory_product_data',
			array(
				$this,
				'fields_product_inventory_tab',
			)
		);
		add_action(
			'woocommerce_admin_process_product_object',
			array(
				$this,
				'save_fields_product_option',
			)
		);

		$this->set_fields();

		add_action(
			'woocommerce_product_after_variable_attributes',
			array(
				$this,
				'fields_product_option_variation',
			),
			10,
			3
		);
		add_action(
			'woocommerce_save_product_variation',
			array(
				$this,
				'save_fields_product_option_variation',
			),
			10,
			2
		);
	}

	public function set_fields() {
		$this->fields = array(
			array(
				'type'                => 'text',
				'id'                  => EAN_FIELD_SLUG,
				'label'               => 'EAN',
				'placeholder'         => '',
				'description'         => '',
				'is_variation_option' => true,
			),
		);
	}

	public function fields_product_inventory_tab() {
		if ( empty( $this->fields ) ) {
			return;
		}

		echo '<div class="options_group">';
		foreach ( $this->fields as $field ) {
			$this->add_field( $field );
		}
		echo '</div>';
	}

	/**
	 * @param array $field Field config.
	 */
	public function add_field( $field ) {
		$input = array(
			'id'            => $field['id'],
			'label'         => $field['label'],
			'placeholder'   => $field['label'],
			'desc_tip'      => ! empty( $field['description'] ),
			'description'   => $field['description'],
			'class'         => isset( $field['class'] ) ? $field['class'] : '',
			'wrapper_class' => isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '',
		);

		if ( isset( $field['value'] ) ) {
			$input['value'] = $field['value'];
		}

		woocommerce_wp_text_input( $input );
	}

	/**
	 * @param \WC_Product|\WC_Product_Variable $wc_product Product.
	 */
	public function save_fields_product_option( $wc_product ) {
		if ( empty( $this->fields ) ) {
			return;
		}

		foreach ( $this->fields as $field ) {
			if ( ! isset( $_POST[ $field['id'] ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				continue;
			}

			$field_post_data = sanitize_text_field( wp_unslash( $_POST[ $field['id'] ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

			$wc_product->update_meta_data( $field['id'], wc_clean( wp_unslash( $field_post_data ) ) );
			$wc_product->save_meta_data();
		}
	}

	/**
	 * @param int   $index Index.
	 * @param array $variation_data Variation data.
	 * @param \WP_Post $variation Variation post.
	 */
	public function fields_product_option_variation( $index, $variation_data, $variation ) {
		if ( empty( $this->fields ) || empty( $variation_data ) ) {
			return;
		}

		$wc_product = wc_get_product( $variation );
		if ( ! $wc_product ) {
			return;
		}

		foreach ( $this->fields as $field ) {
			if ( empty( $field['is_variation_option'] ) ) {
				continue;
			}

			$meta_key = $field['id'];
			if ( EAN_FIELD_SLUG === $field['id'] && empty( $wc_product->get_meta( $meta_key ) ) ) {
				$old_meta_key = AdvancedHelper::find_old_variation_ean_meta_key( $wc_product );
				if ( ! empty( $old_meta_key ) ) {
					$meta_key = $old_meta_key;
				}
			}

			$field_id               = $field['id'] . '_' . $index;
			$field['value']         = $wc_product->get_meta( $meta_key );
			$field['id']            = $field_id;
			$field['class']         = 'short';
			$field['wrapper_class'] = 'form-row form-row-full form-field';
			$this->add_field( $field );
		}
	}

	/**
	 * @param int $variation_id Variation ID.
	 * @param int $index Index.
	 */
	public function save_fields_product_option_variation( $variation_id, $index ) {
		$wc_product = wc_get_product( $variation_id );

		foreach ( $this->fields as $field ) {
			if ( empty( $field['is_variation_option'] ) ) {
				continue;
			}

			$meta_key      = $field['id'];
			$post_data_key = $field['id'] . '_' . $index;

			if ( ! isset( $_POST[ $post_data_key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				continue;
			}

			$field_post_data = wc_clean( wp_unslash( $_POST[ $post_data_key ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

			$wc_product->update_meta_data( $meta_key, $field_post_data );
			$wc_product->save_meta_data();
		}
	}
}
