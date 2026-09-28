<?php

namespace ShoppingFeed\ShoppingFeedWC\Addons\Plugins\AcfPlugin;

use ShoppingFeed\ShoppingFeedWC\Admin\Options;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

class CustomFieldsHelper {

	const LEGACY_OPTIONS = 'sfcf_options';

	const ALLOWED_ACF_FIELD_TYPES = [
		'text',
		'textarea',
		'number',
		'email',
		'password',
		'url',
		'select',
		'checkbox',
		'radio',
		'true_false',
		'link',
	];

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_acf_options() {
		$feed_options = get_option( Options::SF_FEED_OPTIONS, [] );
		if ( ! empty( $feed_options['acf'] ) && is_array( $feed_options['acf'] ) ) {
			return self::decode_acf_option_values( $feed_options['acf'] );
		}

		$legacy_options = get_option( self::LEGACY_OPTIONS, [] );
		if ( ! empty( $legacy_options['acf'] ) && is_array( $legacy_options['acf'] ) ) {
			return self::decode_acf_option_values( $legacy_options['acf'] );
		}

		return [];
	}

	/**
	 * @param string[] $stored_values JSON-encoded field definitions.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private static function decode_acf_option_values( array $stored_values ) {
		return array_values(
			array_filter(
				array_map(
					function ( $field ) {
						if ( ! is_string( $field ) ) {
							return null;
						}
						$decoded = json_decode( $field, true );

						return is_array( $decoded ) ? $decoded : null;
					},
					$stored_values
				)
			)
		);
	}

	/**
	 * @param string $key     ACF field key.
	 * @param array  $options Selected field definitions.
	 *
	 * @return bool
	 */
	public static function acf_is_selected( $key, $options ) {
		$index = array_search( $key, array_column( $options, 'key' ), true );

		return false !== $index;
	}

	/**
	 * @return array<int, array<string, string>>
	 */
	public static function get_acf_product_fields() {
		if ( ! function_exists( 'acf_get_field_groups' ) || ! function_exists( 'acf_get_fields' ) ) {
			return [];
		}

		$product_groups = acf_get_field_groups( [ 'post_type' => 'product' ] );
		$fields         = [];
		if ( empty( $product_groups ) ) {
			return $fields;
		}
		foreach ( $product_groups as $group ) {
			$group_fields = acf_get_fields( $group['key'] );
			if ( empty( $group_fields ) ) {
				continue;
			}
			foreach ( $group_fields as $field ) {
				if ( ! in_array( $field['type'], self::ALLOWED_ACF_FIELD_TYPES, true ) ) {
					continue;
				}

				$fields[] = [
					'type'  => $field['type'],
					'name'  => $field['name'],
					'key'   => $field['key'],
					'label' => $field['label'],
				];
			}
		}

		return $fields;
	}
}
