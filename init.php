<?php defined('SYSPATH') or die ('No direct script access.');

$prefix = Kohana::$config->load('xm_cart.prefix');
$routes = Kohana::$config->load('xm_cart.routes');

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