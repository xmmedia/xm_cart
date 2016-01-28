<?php defined('SYSPATH') or die ('No direct script access.');

$route_prefix = Cart_Config::load('route_prefix');
$routes = (array) Cart_Config::load('routes');
$order_status_ids = (array) Cart_Config::load('order_status_ids');
$transaction_type_ids = (array) Cart_Config::load('transaction_type_ids');
$transaction_status_ids = (array) Cart_Config::load('transaction_status_ids');

// define the order status constants
if ( ! defined('CART_ORDER_STATUS_NEW')) {
	define('CART_ORDER_STATUS_NEW', $order_status_ids['new']);
}
if ( ! defined('CART_ORDER_STATUS_SUBMITTED')) {
	define('CART_ORDER_STATUS_SUBMITTED', $order_status_ids['submitted']);
}
if ( ! defined('CART_ORDER_STATUS_PAYMENT')) {
	define('CART_ORDER_STATUS_PAYMENT', $order_status_ids['payment']);
}
if ( ! defined('CART_ORDER_STATUS_PAID')) {
	define('CART_ORDER_STATUS_PAID', $order_status_ids['paid']);
}
if ( ! defined('CART_ORDER_STATUS_RECEIVED')) {
	define('CART_ORDER_STATUS_RECEIVED', $order_status_ids['received']);
}
if ( ! defined('CART_ORDER_STATUS_SHIPPED')) {
	define('CART_ORDER_STATUS_SHIPPED', $order_status_ids['shipped']);
}
if ( ! defined('CART_ORDER_STATUS_REFUNDED')) {
	define('CART_ORDER_STATUS_REFUNDED', $order_status_ids['refunded']);
}
if ( ! defined('CART_ORDER_STATUS_CANCELLED')) {
	define('CART_ORDER_STATUS_CANCELLED', $order_status_ids['cancelled']);
}
if ( ! defined('CART_ORDER_STATUS_EMPTIED')) {
	define('CART_ORDER_STATUS_EMPTIED', $order_status_ids['cancelled']);
}

// define available payment processor list key
if ( ! defined('PAYMENT_PROCESSOR_LIST')) {
	define('PAYMENT_PROCESSOR_LIST', 'default');
}

// define the order transaction type constants
if ( ! defined('CART_TRANSACTION_TYPE_CHARGE')) {
	define('CART_TRANSACTION_TYPE_CHARGE', $transaction_type_ids['charge']);
}
if ( ! defined('CART_TRANSACTION_TYPE_REFUND')) {
	define('CART_TRANSACTION_TYPE_REFUND', $transaction_type_ids['refund']);
}

// define the order transaction status constants
if ( ! defined('CART_TRANSACTION_STATUS_IN_PROGRESS')) {
	define('CART_TRANSACTION_STATUS_IN_PROGRESS', $transaction_status_ids['in_progress']);
}
if ( ! defined('CART_TRANSACTION_STATUS_SUCCESSFUL')) {
	define('CART_TRANSACTION_STATUS_SUCCESSFUL', $transaction_status_ids['successful']);
}
if ( ! defined('CART_TRANSACTION_STATUS_DENIED')) {
	define('CART_TRANSACTION_STATUS_DENIED', $transaction_status_ids['denied']);
}
if ( ! defined('CART_TRANSACTION_STATUS_ERROR')) {
	define('CART_TRANSACTION_STATUS_ERROR', $transaction_status_ids['error']);
}

// define the constant for the image file path
if ( ! defined('CART_PRODUCT_PHOTO_PATH')) {
	define('CART_PRODUCT_PHOTO_PATH', Cart_Config::load('product_photo_path'));
}


// now setup the routes
if ($routes['product_list']) {
	Route::set('cart_product', $route_prefix . '/product_list')
		->defaults(array(
			'controller' => 'Cart_Product_List',
			'action' => 'index',
	));
}

if ($routes['donate']) {
	Route::set('cart_donate', $route_prefix . '/donate(/<action>)')
		->defaults(array(
			'controller' => 'Cart_Donate',
			'action' => 'index',
	));
}

if ($routes['admin']) {
	Route::set('cart_admin', $route_prefix . '/admin')
		->defaults(array(
			'controller' => 'Cart_Admin',
			'action' => 'index',
	));

	Route::set('cart_admin_order_export', $route_prefix . '/admin/order/export')
		->defaults(array(
			'controller' => 'Cart_Admin_Order_Export',
			'action' => 'export',
	));
	Route::set('cart_admin_order', $route_prefix . '/admin/order(/<action>(/<id>))')
		->defaults(array(
			'controller' => 'Cart_Admin_Order',
			'action' => 'index',
	));

	Route::set('cart_admin_shipping', $route_prefix . '/admin/shipping(/<action>(/<id>))')
		->defaults(array(
			'controller' => 'Cart_Admin_Shipping',
			'action' => 'index',
	));

	Route::set('cart_admin_tax', $route_prefix . '/admin/tax(/<action>(/<id>))')
		->defaults(array(
			'controller' => 'Cart_Admin_Tax',
			'action' => 'index',
	));
}

if ($routes['public']) {
	Route::set('cart_public', $route_prefix . '(/<action>(/<id>))')
		->defaults(array(
			'controller' => 'Cart',
	));

	Route::set('cart_public_photo', $route_prefix . '/product_photo/<id>(.<timestamp>).jpg')
		->defaults(array(
			'controller' => 'Cart',
			'action' => 'product_photo',
	));
}