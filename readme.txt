## ShoppingFeed
Contributors: ShoppingFeed, BeAPI
Tags: shoppingfeed, marketplace, woocommerce, woocommerce shoppingfeed, create woocommerce products shoppingfeed, products feed, generate shoppingfeed, amazon, Jet, Walmart, many marketplace, import orders
Stable tag: 6.1.2
Version: 6.1.2
Requires PHP: 5.6
Requires at least: 5.2
Tested up to: 5.8.0
WC requires at least: 3.8
WC tested up to: 5.5.2

== Upgrade Notice ==
Version 6.0.0 is a major version, there are several changes and improvements which affect the architecture of the plugin. You will have to re-configure the plugin, all the previous settings will be lost

== Changelog ==
6.0.0: this is a major version, there are several changes and improvements which affect the architecture of the plugin. You will have to re-configure the plugin, all the previous settings will be lost
6.0.1: edit readme.txt
6.0.3: edit version number
6.0.4: fix permalink issue
6.0.5: correct attribute values
6.0.6: add ean support for variations
6.0.7: add weight as attribute + fix phone value
6.0.8: add missing commit
6.0.9  send WC shipping method name once order shipped
6.0.10 fix deploy issue
6.0.11 enhance logging
6.0.12 fix static call
6.0.13 add extra fields to feed
6.0.14 add the possibility to export category tree in the feed
6.0.15 add the possibility to choose the default statut for imported order
6.0.16 sum quantity of all variations on parent
6.0.17 using generator for generating products list"
6.0.18 add async generation for feed
6.0.19 add compat to the plugin Chained Product
6.0.20 add compat to the plugin ATS
6.0.21 Set status as publish on product list
6.0.22 Fix file case issue
6.0.23 If the billing address phone is empty, get the shipping one to display phone on the BO
6.0.24 Tracking: fix bad condition
6.0.25 Tracking: Add option to choose Retrieval Mode
6.0.26 Fix version number
6.0.27 Support WP 5.8
6.0.28 Fix bad version
6.0.29 AST compact with Shopping-Feed Advanced helper
6.0.30 Do not force WC mail settings
6.0.31 Do not send mails to other customers
6.0.32 Add link to WC logs
6.0.33 Fix priority issue with other plugins
6.1.0 Add the possibility to connect multiple ShoppingFeed accounts to one WC shop
6.1.2 Fix composer dependencies

== Description ==
WordPress connection Controller Plugin for ShoppingFeed - Sell on Amazon, Ebay, Google, and 1000's of international marketplaces

## Requirements

### Server :
- PHP version 5.6 or above
- PHP cURL extension is activated

### WordPress :

- Core version 5.2 or above
- WooCommerce version 3.8 or above



## Installation

Sign up for free on ShoppingFeed : https://shopping-feed.com/

- Activate the plugin in Plugins > Installed Plugins
- In Plugins > Installed Plugins > ShoppingFeed > settings, log in with your ShoppingFeed credentials
- In Settings, check that ShoppingFeed is enabled and save changes

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
/**
* @return array
*/
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
//array of statuses (strings)
return array();
}
`

`Status available` : created, waiting_store_acceptance, refused, waiting_shipment, shipped, cancelled, refunded, partially_refunded, partially_shipped

__[more details here](https://github.com/shoppingflux/php-sdk/blob/master/docs/manual/resources/order.md)__

### Tracking number
By default, we don’t support any custom plugin for wc order tracking number, you can set custom meta key to identify it, you can use the following snippet

`
add_filter( 'shopping_feed_tracking_number', 'your_custom_tracking_number_function' );
/** @return string */
function your_custom_tracking_number_function() {
return ‘your_custom_order_meta_key’
}
`

### Tracking url
By default, we don’t support any custom plugin for wc order tracking url, you can set custom meta key to identify it, you can use the following snippet
`
add_filter( 'shopping_feed_tracking_link', 'your_custom_tracking_url_function' );
/** @return string */
function your_custom_tracking_url_function() {
return ‘your_custom_order_meta_key’
}
`

### Extra Fields
If you want to add add extra fields to your XML Feed, you can use the following snippet
`
add_filter( 'shopping_feed_extra_fields', 'your_custom_fields_function', 10, 2 );
/** @return array */
function your_custom_tracking_url_function($fields, $wc_product) {
$fields[] = array('name'=>'my_field', 'value'=>'my_value');
return $fields;
}
`

### Variation Images
By default, we don’t support any custom plugin for adding images to WC Product Variation, with this filter you can set the desired images to each variation, you can use the following snippet
`
add_filter( 'shopping_feed_variation_images', 'your_custom_variation_images_function', 10, 2 );
/** @return array */
function your_custom_tracking_url_function($images, $wc_product) {
$images[] = 'https://domain.com/image1.jpg';
$images[] = 'https://domain.com/image2.jpg';
return $images;
}
`
