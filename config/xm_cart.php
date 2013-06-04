<?php defined('SYSPATH') or die('No direct script access.');

return array(
	// the prefix of all the cart routes
	// can't be an empty string
	// default: cart
	'prefix' => 'cart',
	'routes' => array(
		'product_list' => TRUE,
		'public' => TRUE,
		'admin' => TRUE,
	),
);