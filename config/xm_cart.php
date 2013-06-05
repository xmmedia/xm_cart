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
	// the default country to use on orders which don't have their country set to something else
	// default is Canada, ID 40
	'default_country_id' => 40,

	'order_status_labels' => array(
		1 => 'New Order / Unpaid',
		2 => 'Submitted / Waiting for Payment',
		3 => 'Payment in Progress',
		4 => 'Paid',
		5 => 'Received',
		6 => 'Shipped',
		7 => 'Refunded',
		8 => 'Cancelled',
	),
	'order_status_ids' => array(
		'new' => 1,
		'submitted' => 2,
		'payment' => 3,
		'paid' => 4,
		'received' => 5,
		'shipped' => 6,
		'refunded' => 7,
		'cancelled' => 8,
	),
);