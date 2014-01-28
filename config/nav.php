<?php defined('SYSPATH') or die ('No direct script access.');

return array(
	'private' => array(
		'items' => array(
			'Shopping Cart' => array(
				'route' => 'cart_admin',
				'perm' => 'cart/admin',
				'class' => 'cart_admin',
				'logged_in_only' => TRUE,
				'order' => 150,

				'sub_menu' => array(
					'items' => array(
						'Orders' => array(
							'route' => 'cart_admin',
							'params' => array('action' => 'order'),
							'perm' => 'cart/admin/order',
							'class' => 'cart_admin_order',
							'order' => 100,
						),
						'Shipping Rates' => array(
							'route' => 'cart_admin',
							'params' => array('action' => 'shipping'),
							'perm' => (Cart_Config::enable_shipping() && Auth::instance()->allowed('cart/admin/shipping')),
							'class' => 'cart_admin_shipping',
							'order' => 200,
						),
						'Taxes' => array(
							'route' => 'cart_admin',
							'params' => array('action' => 'tax'),
							'perm' => (Cart_Config::enable_tax() && Auth::instance()->allowed('cart/admin/tax')),
							'class' => 'cart_admin_tax',
							'order' => 300,
						),
					),
				),
			),
		),
	),
);