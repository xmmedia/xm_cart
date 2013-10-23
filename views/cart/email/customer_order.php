<p>Thank you for your order. We have received your order and are currently processing it. Below are the details of your order.</p>

<?php echo View::factory('cart/email/cart')
	->set($kohana_view_data); ?>

<?php echo View::factory('cart/email/order_info')
	->set($kohana_view_data); ?>