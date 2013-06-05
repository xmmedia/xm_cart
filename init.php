<?php defined('SYSPATH') or die ('No direct script access.');

$prefix = Kohana::$config->load('xm_cart.prefix');
$routes = (array) Kohana::$config->load('xm_cart.routes');
$order_status_ids = (array) Kohana::$config->load('xm_cart.order_status_ids');

// define the order status constants
if ( ! defined('CART_ORDER_STATUS_NEW')) {
	/**
	*   CONST :: Order Status - New Order / Unpaid
	*   @var    string
	*/
	define('CART_ORDER_STATUS_NEW', $order_status_ids['new']);
}
if ( ! defined('CART_ORDER_STATUS_SUBMITTED')) {
	/**
	*   CONST :: Order Status - Submitted / Waiting for Payment
	*   @var    string
	*/
	define('CART_ORDER_STATUS_SUBMITTED', $order_status_ids['submitted']);
}
if ( ! defined('CART_ORDER_STATUS_PAYMENT')) {
	/**
	*   CONST :: Order Status - Payment in Progress
	*   @var    string
	*/
	define('CART_ORDER_STATUS_PAYMENT', $order_status_ids['payment']);
}
if ( ! defined('CART_ORDER_STATUS_PAID')) {
	/**
	*   CONST :: Order Status - Paid
	*   @var    string
	*/
	define('CART_ORDER_STATUS_PAID', $order_status_ids['paid']);
}
if ( ! defined('CART_ORDER_STATUS_RECEIVED')) {
	/**
	*   CONST :: Order Status - Received
	*   @var    string
	*/
	define('CART_ORDER_STATUS_RECEIVED', $order_status_ids['received']);
}
if ( ! defined('CART_ORDER_STATUS_SHIPPED')) {
	/**
	*   CONST :: Order Status - Shipped
	*   @var    string
	*/
	define('CART_ORDER_STATUS_SHIPPED', $order_status_ids['shipped']);
}
if ( ! defined('CART_ORDER_STATUS_REFUNDED')) {
	/**
	*   CONST :: Order Status - Refunded
	*   @var    string
	*/
	define('CART_ORDER_STATUS_REFUNDED', $order_status_ids['refunded']);
}
if ( ! defined('CART_ORDER_STATUS_CANCELLED')) {
	/**
	*   CONST :: Order Status - Cancelled
	*   @var    string
	*/
	define('CART_ORDER_STATUS_CANCELLED', $order_status_ids['cancelled']);
}

// now setup the routes
if ($routes['product_list']) {
	Route::set('cart_product', $prefix . '/product_list')
		->defaults(array(
			'controller' => 'Cart_Product_List',
			'action' => 'index',
	));
}

if ($routes['public']) {
	Route::set('cart_public', $prefix . '(/<action>(/<id>))')
		->defaults(array(
			'controller' => 'Cart',
			'action' => NULL,
	));
}

if ($routes['admin']) {
	Route::set('cart_admin', $prefix . '/admin(/<action>(/<id>))')
		->defaults(array(
			'controller' => 'Cart_Admin',
			'action' => NULL,
	));
}