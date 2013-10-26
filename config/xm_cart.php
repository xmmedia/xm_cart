<?php defined('SYSPATH') or die('No direct script access.');

return array(
	'administrator_email' => array(
		'email' => 'webmaster@example.com',
		'name' => 'Cart Admin',
	),

	// the prefix of all the cart routes
	// can't be an empty string
	// default: cart
	'route_prefix' => 'cart',
	'continue_shopping_url' => '/cart/product_list',
	'routes' => array(
		'product_list' => FALSE,
		'donate' => FALSE,
		'public' => TRUE,
		'admin' => TRUE,
	),
	// the default country to use on orders which don't have their country set to something else
	// default is Canada, ID 40
	'default_country_id' => 40,
	'default_currency' => 'CAD',

	'enable_shipping' => TRUE,
	'enable_tax' => TRUE,
	'enable_sub_total' => TRUE,

	'donation_cart' => FALSE,
	'donation_product_id' => NULL,
	'donation_minimum' => 25,
	'donation_maximum' => 10000,

	// order status labels and values
	// used in the xm_cart init.php and can also be customized per site
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
		'new'       => 1,
		'submitted' => 2,
		'payment'   => 3,
		'paid'      => 4,
		'received'  => 5,
		'shipped'   => 6,
		'refunded'  => 7,
		'cancelled' => 8,
	),

	'payment_status_labels' => array(
		// the payment is in progress and has not been completed
		1 => 'In Progress',
		// the payment was completed succesfully, without errors
		2 => 'Successful',
		// the payment was denied, for any number of reasons: wrong info, invalid card, not enough room, etc
		3 => 'Denied',
		// there was a problem processing the payment
		4 => 'Error',
		// the payment was not completed
		5 => 'Incomplete',
		// the payment was cancelled, likely on the payment processor
		6 => 'Cancelled',
		// the payment was reversed, probably a chargeback
		7 => 'Reversed',
		// the payment was refunded
		8 => 'Refunded',
	),
	'payment_status_ids' => array(
		'in_progress' => 1,
		'successful'  => 2,
		'denied'      => 3,
		'error'       => 4,
		'incomplete'  => 5,
		'cancelled'   => 6,
		'reversed'    => 7,
		'refunded'    => 8,
	),

	// payment processor ids
	// this also controls which ones are enabled for the site
	// recommended: don't change the IDs between configs (ie, 1 is stripe on one config and 2 on another config)
	'payment_processors' => array(
		'default' => array(
			1 => 'Stripe',
		),
	),
	'payment_processor_ids' => array(
		'stripe' => 1,
	),

	// the configuration for each payment processor
	// the one used is controlled by constants for each processor
	'payment_processor_config' => array(
		'stripe' => array(
			'test' => array(
				'secret_key' => NULL,
				'publishable_key' => NULL,
				'api_version' => '2013-08-13',
				'charge_description' => LONG_NAME . ' Payment ' . ADMIN_EMAIL,
			),
			'live' => array(
				'secret_key' => NULL,
				'publishable_key' => NULL,
				'api_version' => '2013-08-13',
				'charge_description' => LONG_NAME . ' Payment ' . ADMIN_EMAIL,
			),
		),
	),

	'cart_order_log_actions' => array(
		'created' => 'Order Created',
		'add_product' => 'Add Product',
		'remove_product' => 'Remove Product',
		'change_quantity' => 'Change Quantity',
		'empty_cart' => 'Empty Cart',
		'checkout' => 'Checkout',
		'save_shipping' => 'Save Shipping',
		'save_billing' => 'Save Billing',
		'save_final' => 'Save Final',
		'complete_order' => 'Complete Order',
		'processing_payment' => 'Processing Payment',
		'paid' => 'Paid',
		'payment_error' => 'Payment Error',
		'payment_failed' => 'Payment Failed',
		'set_user' => 'Set User/Assigned to User',
		'set_shipping_country' => 'Set Shipping Country',
		'set_shipping_state' => 'Set Shipping State',
		'unset_shipping_country' => 'Unset Shipping Country',
		'add_shipping' => 'Add Shipping Rate',
		'remove_shipping' => 'Remove Shipping Rate',
		'add_tax' => 'Add Tax',
		'remove_tax' => 'Remove Tax',
		'add_additional_charge' => 'Add Additional Charge',
		'remove_additional_charge' => 'Remove Additional Charge',
	),
);