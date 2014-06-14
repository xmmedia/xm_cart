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
		'error_refund' => 'There was a problem processing the refund. Please try again or contact the system administrator.',
		'fail' => 'There was a problem processing your payment. Please contact us to complete your payment.',
		'fail_refund' => 'There was a problem processing the refund. Please try again or contact the system administrator.',
	),

	'page_titles' => array(
		'cart_view' => 'Cart - ',
		'login' => 'Login - ',
		'checkout' => 'Checkout - ',
		'completed' => 'Order Complete - ',
		'view_order' => 'Order - :order_num - ',
		'view_order_none' => 'Order - ',
	),

	'checkout' => array(
		'headers' => array(
			'cart' => 'Your Cart',
			'shipping' => 'Shipping Address',
			'payment' => 'Payment',
			'final' => 'Notes',
			'confirm' => 'Complete Your Order',
			'confirm_donation' => 'Complete Your Donation',
		),
		'confirm_your_order' => 'Please confirm your order.',
		'confirm_your_donation' => 'Please confirm your donation.',
		'continue_shopping' => 'Continue Shopping',
		'back_to_cart' => 'Back to Cart',
		'empty_cart' => 'Empty Cart',
		'notes_label' => 'Optional: Enter any messages you\'d like us to know when processing your order.',
		'notes_label_donation' => 'Optional: Enter any messages you\'d like us to know when processing your donation.',
		'complete_order_button' => 'Complete My Order',
		'complete_donation_button' => 'Complete My Donation',
		'review_order' => 'Please review your order before continuing.',
		'review_donation' => 'Please review your donation before continuing.',
	),

	'view_order' => array(
		'not_found' => 'There was a problem loading your order. Please contact us directly regarding your order.',
		'refunded' => 'The order you selected has been refunded. Please contact us if you have any questions.',
		'cancelled' => 'The order you selected has been cancelled. Please contact us if you have any questions.',
		'cant_view' => 'The order you selected cannot be viewed at this time. Please contact us if you have any questions.',
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
			'thank_you_donation' => 'Thank you for your donation. Below are the details of your donation. You will receive a tax deductible donation receipt at the end of the year.',
			'email_title' => 'Your order from :company',
			'email_title_donation' => 'Your donation to :company',
			'full_refund' => 'The following order has been fully refunded. Please contact us if you have any questions.',
			'partial_refund' => 'A refund of :amount has been applied to this order. Please contact us if you have any questions.',
			'cancelled' => 'The following order has been cancelled and refunded. Please contact us if you have any questions.',
		),
		'admin_order' => array(
			'subject' => 'Order Received - :order_num',
			'subject_donation' => 'Donation Received - :order_num',
			'email_title' => 'Order Received - :order_num',
		),
	),
);