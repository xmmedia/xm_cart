<p><?php echo HTML::chars(Cart::message('email.customer_order.thank_you' . ($donation_cart ? '_donation' : ''))); ?></p>

<?php echo View::factory($cart_view)
	->set($kohana_view_data); ?>

<?php echo View::factory('cart/email/order_info')
	->set($kohana_view_data); ?>