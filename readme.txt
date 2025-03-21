## ShoppingFeed
Contributors: ShoppingFeed, BeAPI
Tags: shoppingfeed, marketplace, woocommerce, woocommerce shoppingfeed, create woocommerce products shoppingfeed, products feed, generate shoppingfeed, amazon, Jet, Walmart, many marketplace, import orders
Stable tag: 6.11.0
Version: 6.11.0
Requires PHP: 7.3
Requires at least: 5.7
Tested up to: 6.7
WC requires at least: 7.0
WC tested up to: 9.6.0

== Upgrade Notice ==
Version 6.0.0 is a major version, there are several changes and improvements which affect the architecture of the plugin. You will have to re-configure the plugin, all the previous settings will be lost

== Changelog ==
* 6.11.0
  * Variations : add new filter to modify variation's main image
* 6.10.0
  * Orders : Redesigned the shipment tracking system.
  * Orders (beta) : Add option to include VAT when importing orders.
* 6.9.0
  * Feed : Fix attributes not use in variations missing in the feed.
  * Feed : Dimension data are correctly included in the feed.
  * Orders : Fix invalid timestamp when scheduling async task to acknowledge orders.
* 6.8.0
  * Feed : Fix the promotion date.
* 6.7.0
  * Orders : The 'buyer_identification_number' field is imported in an order custom field if it exists.
  * Orders : Product updates (price and stock) via the SF API are made asynchronously via a scheduled task.
  * Misc   : ShoppingFeed service identifiers are no longer stored in the application's logs offered by WooCommerce.
* 6.6.0
  * Feed : Fix cases where some out-of-stock products would appear in the feed.
  * Feed : A new option to include out-of-stock products in the feed is available.
  * Feed : Update quantity calculations to be more accurate in various cases
  * Feed : Update feed metadata to include Woocommerce and plugin version.
  * Orders : Don't create orders in Woocommerce if stock is not sufficient and sent a custom acknowledgment error.
  * Orders : A new tools is available to import unacknowledged orders created more than two weeks ago.
  * Account : Refresh the account settings.
  * Account : A new option to change the default store is available.
  * Shipping (AST) : Handle multiple tracking numbers.
  * Misc : Fix remaining conflict in prefixed dependencies.
* 6.5.1
  * Feed : add options to disable stock and price synchronization with ShoppingFeed when a product change.
* 6.5.0
  * Misc : HPOS compatibility.
  * Orders : Update the filters used for retrieving orders from ShoppingFeed.
  * Orders : Rework the checks when importing orders from ShoppingFeed.
* 6.4.0
  * Misc : Prefix Guzzle library and related dependencies to avoid conflicts.
* 6.3.0
  * Orders : Don't import orders fulfilled by the marketplaces by default, see details in the description.
  * WPCLI command : don't rerun the generation process when an error occurs.
* 6.2.0
  * Rework feed generation process to better handle shop with large amount of products.
* 6.1.20
  * Fix an issue with migration process failing to be scheduled with new version of Woocommerce.
  * Update plugin requirements
* 6.1.19
  * Update 'shopping_feed_variation_images' filter to include the WC variation ID.
* 6.1.18
  * Update 'shopping_feed_custom_ean' filter to include the WC product.
* 6.1.17
  * Add new filter 'pre_sf_carrier_from_wc_shipping' to override default carrier data sent to ShoppingFeed for an order.
* 6.1.16
  * PHP 8 compatibility fix
  * Readme update
* 6.1.15
  * Weight of variations is back into attributes
* 6.1.14
  * Add support for Woocommerce Shipment Tracking Pro
  * Fix usage of Yoast option
  * Update readme
* 6.1.13
  * Fix PHP error with WPSEO premium
* 6.1.12
  * Fix missing admin tab
* 6.1.11
  * Weight attribute is at the root of the product xml feed
  * the 'other' field is map to the customer notes
  * Yoast categories are taken into account
* 6.1.10
  * Update AST addon to support the pro version
* 6.1.9
  * Fix wrong quantities for chained products
* 6.1.8
  * Add Zalando as an available marketplace
* 6.1.7
  * Fix shipping options not saving
* 6.1.6
  * Release main
* 6.1.5
  * Performances improvement : Logger and HTTP connection
* 6.1.4
  * Added filter for fees handling
  * refresh translations
* 6.1.3
  * Fix composer dependencies
* 6.1.0
  * Add the possibility to connect multiple ShoppingFeed accounts to one WC shop
* 6.0.33
  * Fix priority issue with other plugins
* 6.0.32
  * Add link to WC logs
* 6.0.31
  * Do not send mails to other customers
* 6.0.30
  * Do not force WC mail settings
* 6.0.29
  * AST compact with Shopping-Feed Advanced helper
* 6.0.28
  * Fix bad version
* 6.0.27
  * Support WP 5.8
* 6.0.26
  * Fix version number
* 6.0.25
  * Tracking: Add option to choose Retrieval Mode
* 6.0.24
  * Tracking: fix bad condition
* 6.0.23
  * If the billing address phone is empty, get the shipping one to display phone on the BO
* 6.0.22
  * Fix file case issue
* 6.0.21
  * Set status as publish on product list
* 6.0.20
  * add compat to the plugin ATS
* 6.0.19
  * add compat to the plugin Chained Product
* 6.0.18
  * add async generation for feed
* 6.0.17
  * using generator for generating products list"
* 6.0.16
  * sum quantity of all variations on parent
* 6.0.15
  * add the possibility to choose the default status for imported order
* 6.0.14
  * add the possibility to export category tree in the feed
* 6.0.13
  * add extra fields to feed
* 6.0.12
  * fix static call
* 6.0.11
  * enhance logging
* 6.0.10
  * fix deploy issue
* 6.0.9
  * send WC shipping method name once order shipped
* 6.0.8
  * add missing commit
* 6.0.7
  * add weight as attribute
  * fix phone value
* 6.0.6
  * add ean support for variations
* 6.0.5
  * correct attribute values
* 6.0.4
  * fix permalink issue
* 6.0.3
  * edit version number
* 6.0.1
  * edit readme.txt
* 6.0.0
  * this is a major version, there are several changes and improvements which affect the architecture of the plugin
  * You will have to re-configure the plugin, all the previous settings will be lost

== Description ==
WordPress connection Controller Plugin for ShoppingFeed - Sell on Amazon, Ebay, Google, and 1000's of international marketplaces

## Requirements

### Server :
- PHP version 7.3 or above
- PHP cURL extension is activated

### WordPress :

- Core version 5.7 or above
- WooCommerce version 7.0 or above

## Installation

Sign up for free on ShoppingFeed : https://shopping-feed.com/

- Activate the plugin in Plugins > Installed Plugins
- In Plugins > Installed Plugins > ShoppingFeed > settings, log in with your ShoppingFeed credentials
- In Settings, check that ShoppingFeed is enabled and save changes

## Orders fulfilled by the marketplaces

The plugin won't import orders fulfilled by marketplaces by default.

Options are available in the plugin settings to include those orders during the import.

They can be found in the "Orders" tab :

* Orders fulfilled by marketplace : import orders even if they are fulfilled by the marketplace.
* Fulfilled by marketplace order's status : select the status used for orders fulfilled by marketplaces when they are imported.

## Shipment tracking support

For now, the only shipment tracking plugins supported are :

* Advanced Shipment Tracking : https://wordpress.org/plugins/woo-advanced-shipment-tracking/
* Advanced Shipment Tracking PRO : https://www.zorem.com/product/woocommerce-advanced-shipment-tracking/
* Woocommerce Shipment Tracking Pro : https://www.pluginhive.com/product/woocommerce-shipment-tracking-pro/

## Configuration

To start using the plugin correctly, you need to configure it with your preferences (Feed, Shipping, Orders)

## Available hooks

With this snippets below can be added to your theme's functions.php file or your custom plugin file

### Categories
By default, we support `product_cat` as taxonomy slug to identify product's categories, you can override it using this snippet :

`
add_filter( 'shopping_feed_custom_category_taxonomy', 'your_custom_category_function' );

/** @return string */
function your_custom_category_function() {
    return 'your_custom_category_slug';
}
`

### Brands
By default, we don’t support any custom plugin for product's brand, you can set custom taxonomy slug to identify it by using this snippet :

`
add_filter( 'shopping_feed_custom_brand_taxonomy', 'your_custom_brand_function' );

/** @return string */
function your_custom_brand_function() {
    return 'your_custom_brand_slug';
}
`

### EAN
By default, we don’t support any custom plugin for product EAN, you can set custom taxonomy slug to identify it by using this snippet :

`
add_filter( 'shopping_feed_custom_ean', 'your_custom_ean_function' );

/** @return string */
function your_custom_ean_function() {
    return 'your_custom_ean_slug';
}
`

### Feed’s products list args
To export the feed, we use the plugin’s setting, if you want to add/use specific args, you can use the following snippet

`
add_filter( 'shopping_feed_products_custom_args', 'your_custom_args_function' );

/** @return array */
function your_custom_args_function() {
//array of args
    return array();
}
`

You can find all available args here
__[WooCommerce documentation](https://github.com/woocommerce/woocommerce/wiki/wc_get_products-and-WC_Product_Query#parameters)__

### Orders to import (statuses)
By default, we import orders with ‘waiting_shipment’ status, if you want to import more statuses or a specific one, you can use the following snippet

`
add_filter( 'shopping_feed_orders_to_import', 'your_custom_statuses_function' );

/** @return array */
function your_custom_statuses_function() {
    // array of statuses (strings)
    return array();
}
`

`Status available` : created, waiting_store_acceptance, refused, waiting_shipment, shipped, cancelled, refunded, partially_refunded, partially_shipped

__[more details here](https://github.com/shoppingflux/php-sdk/blob/master/docs/manual/resources/order.md)__

### Tracking number
If you want to set a custom meta key to identify it, you can use the following snippet

`
add_filter( 'shopping_feed_tracking_number', 'your_custom_tracking_number_function' );

/** @return string */
function your_custom_tracking_number_function() {
    return ‘your_custom_order_meta_key’
}
`

### Tracking url
If you want to set a custom meta key to identify it, you can use the following snippet

`
add_filter( 'shopping_feed_tracking_link', 'your_custom_tracking_url_function' );

/** @return string */
function your_custom_tracking_url_function() {
    return ‘your_custom_order_meta_key’
}
`

### Extra Fields
If you want to add an extra fields to your XML Feed, you can use the following snippet

`
add_filter( 'shopping_feed_extra_fields', 'your_custom_fields_function', 10, 2 );

/** @return array */
function your_custom_fields_function($fields, $wc_product) {
    $fields[] = array('name'=>'my_field', 'value'=>'my_value');
    return $fields;
}
`

### Variation Images

#### Main image
By default the variation's thumbnail is used as the main image in the feed.

You can customize the main image using the filter "shopping_feed_variation_main_image".

`
add_filter( 'shopping_feed_variation_main_image', 'your_custom_variation_main_images_function', 10, 3 );

/**
 * Use the parent's thumbnail if the variation doesn't have one.
 *
 * @param string $main_image The main image of the variation.
 * @param \WC_Product_Variation $variation The variation.
 * @param \WC_Product_Variable $product The product.
 *
 * @return string
 */
function your_custom_variation_main_images_function( $main_image, $variation, $product ) {
    if ( empty( $main_image ) && has_post_thumbnail( $product->get_id() ) ) {
      $main_image = get_the_post_thumbnail_url( $product->get_id(), 'full' );
    }

    return $main_image;
}
`

#### Additional images
By default, we don’t support any custom plugin for adding images to WC Product Variation, with this filter you can set the desired images to each variation, you can use the following snippet

`
add_filter( 'shopping_feed_variation_images', 'your_custom_variation_images_function', 10, 3 );

/**
 * @param array $images
 * @param WC_Product $wc_product
 * @param int $variation_id
 *
 * @return array
 */
function your_custom_variation_images_function( $images, $wc_product, $variation_id ) {
    $images[] = 'https://domain.com/image1.jpg';
    $images[] = 'https://domain.com/image2.jpg';

    return $images;
}
`
