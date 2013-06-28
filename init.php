<?php defined('SYSPATH') or die ('No direct script access.');

$prefix = Kohana::$config->load('xm_cart.prefix');
$routes = (array) Kohana::$config->load('xm_cart.routes');
$order_status_ids = (array) Kohana::$config->load('xm_cart.order_status_ids');
$payment_status_ids = (array) Kohana::$config->load('xm_cart.payment_status_ids');

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

if ( ! defined('PAYMENT_PROCESSOR_LIST')) {
	/**
	*   CONST :: The list of payment processors to use.
	*   @var    string
	*/
	define('PAYMENT_PROCESSOR_LIST', 'default');
}

// define the order payment status constants
if ( ! defined('CART_PAYMENT_STATUS_IN_PROGRESS')) {
	/**
	*   CONST :: Order Status - In Progress
	*   @var    string
	*/
	define('CART_PAYMENT_STATUS_IN_PROGRESS', $payment_status_ids['in_progress']);
}
if ( ! defined('CART_PAYMENT_STATUS_SUCCESSFUL')) {
	/**
	*   CONST :: Order Status - Successful
	*   @var    string
	*/
	define('CART_PAYMENT_STATUS_SUCCESSFUL', $payment_status_ids['successful']);
}
if ( ! defined('CART_PAYMENT_STATUS_DENIED')) {
	/**
	*   CONST :: Order Status - Denied
	*   @var    string
	*/
	define('CART_PAYMENT_STATUS_DENIED', $payment_status_ids['denied']);
}
if ( ! defined('CART_PAYMENT_STATUS_ERROR')) {
	/**
	*   CONST :: Order Status - Error
	*   @var    string
	*/
	define('CART_PAYMENT_STATUS_ERROR', $payment_status_ids['error']);
}
if ( ! defined('CART_PAYMENT_STATUS_INCOMPLETE')) {
	/**
	*   CONST :: Order Status - Incomplete
	*   @var    string
	*/
	define('CART_PAYMENT_STATUS_INCOMPLETE', $payment_status_ids['incomplete']);
}
if ( ! defined('CART_PAYMENT_STATUS_CANCELLED')) {
	/**
	*   CONST :: Order Status - Cancelled
	*   @var    string
	*/
	define('CART_PAYMENT_STATUS_CANCELLED', $payment_status_ids['cancelled']);
}
if ( ! defined('CART_PAYMENT_STATUS_REVERSED')) {
	/**
	*   CONST :: Order Status - Reversed
	*   @var    string
	*/
	define('CART_PAYMENT_STATUS_REVERSED', $payment_status_ids['reversed']);
}
if ( ! defined('CART_PAYMENT_STATUS_REFUNDED')) {
	/**
	*   CONST :: Order Status - Refunded
	*   @var    string
	*/
	define('CART_PAYMENT_STATUS_REFUNDED', $payment_status_ids['refunded']);
}

// now setup the routes
if ($routes['product_list']) {
	Route::set('cart_product', $prefix . '/product_list')
		->defaults(array(
			'controller' => 'Cart_Product_List',
			'action' => 'index',
	));
}

if ($routes['admin']) {
	Route::set('cart_admin', $prefix . '/admin(/<action>(/<id>))')
		->defaults(array(
			'controller' => 'Cart_Admin',
			'action' => 'index',
	));
}

if ($routes['public']) {
	Route::set('cart_public', $prefix . '(/<action>(/<id>))')
		->defaults(array(
			'controller' => 'Cart',
	));
}