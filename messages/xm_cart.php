<?php defined('SYSPATH') or die ('No direct script access.');

return array(
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
		'checkout' => 'Checkout - ',
		'completed' => 'Order Complete - ',
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

	'email' => array(
		'order_info' => array(
			'order_num' => 'Order Number:',
			'order_num_donation' => 'Transaction Number:',
		),
		'customer_order' => array(
			'subject' => 'Your order from :company',
			'subject_donation' => 'Your donation to :company',
			'thank_you' => 'Thank you for your order. We have received your order and are currently processing it. Below are the details of your order.',
			'thank_you_donation' => 'Thank you for your donation. Below are the details of your donation. You will receive a tax deductable donation receipt at the end of the year.',
			'email_title' => 'Your order from :company',
		),
		'admin_order' => array(
			'subject' => 'Order Received - :order_num',
			'subject_donation' => 'Donation Received - :order_num',
			'email_title' => 'Order Received - :order_num',
		),
	),
);