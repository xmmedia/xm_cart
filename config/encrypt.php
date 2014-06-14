<?php defined('SYSPATH') OR die('No direct script access.');

return array(
	'xm_cart' => array(
		// load the cart salt
		'key' => Cart_Config::load('salt'),
	),
);