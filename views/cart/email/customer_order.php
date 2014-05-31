<?php echo $order_message_html; ?>

<?php echo View::factory($cart_view)
	->set($kohana_view_data); ?>

<?php echo View::factory('cart/email/order_info')
	->set($kohana_view_data); ?>