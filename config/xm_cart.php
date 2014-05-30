<?php defined('SYSPATH') or die('No direct script access.');

return array(
	'salt' => 'MH[i_9>4w<LuP8{J.Y#:N!Nim@-aSQJwzOrV#GI50{BY]&8tz/,|6=d"7&\'_4hz',
	'user_cookie_expiration' => Date::MONTH * 6,

	'administrator_email' => array(
		'email' => 'webmaster@example.com',
		'name' => 'Cart Admin',
	),

	// the prefix of all the cart routes
	// can't be an empty string
	// default: cart
	'route_prefix' => 'cart',
	'cart_view_url' => '/cart/view',
	'continue_shopping_url' => '/cart/product_list',
	'routes' => array(
		'product_list' => FALSE,
		'donate' => FALSE,
		'public' => TRUE,
		'admin' => TRUE,
	),
	// by default, we want to send the user HTTPS when they go to the checkout page
	'checkout_https' => TRUE,
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

	// controls the display of the country fields
	// if disable, they will be set to the value of 'default_country_id'
	'show_shipping_country' => TRUE,
	'show_billing_country' => TRUE,

	// enables/disables the company field on the billing information
	'show_billing_company' => TRUE,

	// by default, hide the phone country codes (both shipping & billing)
	'show_phone_country_codes' => FALSE,
	// by default, show the phone extensions (both shipping & billing)
	'show_phone_extensions' => TRUE,

	// if TRUE, the customer will be given the opportunity to login, register or checkout as guest
	// before getting to the checkout page
	'offer_login' => TRUE,
	// offer registration after the order is completed
	// the last order ID will be stored in the session as 'xm_cart.last_order_id'
	'offer_register_after_completed' => TRUE,
	// the uri to the registration page
	// used on the offer login page
	// to disable, empty the value or set to false
	'register_uri' => '/register',

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
		9 => 'Emptied',
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

	// transaction types
	'transaction_type_labels' => array(
		1 => 'Charge',
		2 => 'Refund',
	),
	'transaction_type_ids' => array(
		'charge' => 1,
		'refund' => 2,
	),

	// transaction statuses
	'transaction_status_labels' => array(
		// the transaction is in progress and has not been completed
		1 => 'In Progress',
		// the transaction was completed succesfully, without errors
		2 => 'Successful',
		// the transaction was denied, for any number of reasons: wrong info, invalid card, not enough room, etc
		3 => 'Denied',
		// there was a problem processing the transaction
		4 => 'Error',
	),
	'transaction_status_ids' => array(
		'in_progress' => 1,
		'successful'  => 2,
		'denied'      => 3,
		'error'       => 4,
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
				'api_version' => '2014-03-28',
			),
			'live' => array(
				'secret_key' => NULL,
				'publishable_key' => NULL,
				'api_version' => '2014-03-28',
			),
		),
	),

	'cart_order_log_actions' => array(
		'created' => 'Order Created',
		'add_product' => 'Add Product',
		'remove_product' => 'Remove Product',
		// product removed because it no longer exists or is inactive
		'cleaned_product' => 'Cleaned Product',
		'change_quantity' => 'Change Quantity',
		// the unit price was changed, likely because it was changed by an admin
		'change_unit_price' => 'Change Unit Price',
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
		'update_additional_charge' => 'Update Additional Charge',
		'donation_unit_price_change' => 'Donation Amount Changed',
		'processing_refund' => 'Processing Refund',
		'processed_refund' => 'Processed Refund',
		'refunded' => 'Refunded',
		'refund_error' => 'Refund Error',
		'refund_failed' => 'Refund Failed',
		'cancelled' => 'Cancelled',
	),
);