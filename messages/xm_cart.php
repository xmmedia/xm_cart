<?php defined('SYSPATH') or die ('No direct script access.');

return array(
	'customer_email_subject' => 'Your order from :company',
	'customer_email_subject_donation' => 'Your donation to :company',
	'admin_email_subject' => 'Order Received - :order_num',
	'admin_email_subject_donation' => 'Donation Received - :order_num',
	'empty_cart' => 'You don\'t have any products in your cart. Please browse our available products before checking out.',
	'already_processing' => 'The order you\'ve submitted is already being processed.',
	'already_completed' => 'Your order has already been completed.',
	'please_checkout' => 'Your order has not been completed. Please checkout before continuing.',

	'stripe' => array(
		'incorrect_zip' => 'The Postal/Zip Code you supplied failed validation. Please verify before trying again.',
		'card_declined' => 'Your card was declined. Please check that you\'ve entered it correctly before trying again.',
		'error' => 'There was a problem processing your payment. Please try again or contact us to complete your payment.',
		'fail' => 'There was a problem processing your payment. Please contact us to complete your payment.',
	),

	'page_titles' => array(
		'checkout' => 'Checkout',
	),

	'checkout' => array(
		'headers' => array(
			'cart' => 'Your Cart',
			'shipping' => 'Shipping Address',
			'payment' => 'Payment',
			'final' => 'Notes',
			'confirm' => 'Complete Your Order',
		),
		'confirm_your_order' => 'Please confirm your order.',
		'back_to_cart' => 'Back to Cart',
		'notes_label' => 'Enter any messages you\'d like us to know when processing your order (optional).',
		'complete_order_button' => 'Complete My Order',
		'review_order' => 'Please review your order before continuing.',
	),
);