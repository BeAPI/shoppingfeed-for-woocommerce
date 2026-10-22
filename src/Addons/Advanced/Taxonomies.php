<?php

namespace ShoppingFeed\ShoppingFeedWC\Addons\Advanced;

// Exit on direct access
defined( 'ABSPATH' ) || exit;

/**
 * Product brand taxonomy.
 */
class Taxonomies {

	const BRAND_TAXONOMY_SLUG = 'product_brand';

	public function __construct() {
		add_action( 'init', array( $this, 'add_brand' ), 50 );
	}

	public function add_brand() {
		if ( taxonomy_exists( self::BRAND_TAXONOMY_SLUG ) ) {
			return;
		}

		$labels = array(
			'name'                       => _x( 'Brands', 'Taxonomy General Name', 'shopping-feed' ),
			'singular_name'              => _x( 'Brand', 'Taxonomy Singular Name', 'shopping-feed' ),
			'menu_name'                  => __( 'Brands', 'shopping-feed' ),
			'all_items'                  => __( 'All Brands', 'shopping-feed' ),
			'parent_item'                => __( 'Parent Brand', 'shopping-feed' ),
			'parent_item_colon'          => __( 'Parent Brand:', 'shopping-feed' ),
			'new_item_name'              => __( 'New Brand Name', 'shopping-feed' ),
			'add_new_item'               => __( 'Add New Brand', 'shopping-feed' ),
			'edit_item'                  => __( 'Edit Brand', 'shopping-feed' ),
			'update_item'                => __( 'Update Brand', 'shopping-feed' ),
			'view_item'                  => __( 'View Brand', 'shopping-feed' ),
			'separate_items_with_commas' => __( 'Separate items with commas', 'shopping-feed' ),
			'add_or_remove_items'        => __( 'Add or remove items', 'shopping-feed' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'shopping-feed' ),
			'popular_items'              => __( 'Popular Brands', 'shopping-feed' ),
			'search_items'               => __( 'Search Brands', 'shopping-feed' ),
			'not_found'                  => __( 'Not Found', 'shopping-feed' ),
			'no_terms'                   => __( 'No brands', 'shopping-feed' ),
			'items_list'                 => __( 'Brands list', 'shopping-feed' ),
			'items_list_navigation'      => __( 'Brands list navigation', 'shopping-feed' ),
		);
		$args   = array(
			'labels'            => $labels,
			'hierarchical'      => false,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => false,
			'show_tagcloud'     => false,
			'meta_box_cb'       => false,
		);

		register_taxonomy( self::BRAND_TAXONOMY_SLUG, array( 'product' ), $args );
	}
}
