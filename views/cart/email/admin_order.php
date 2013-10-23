<?php echo View::factory('cart/email/cart')
	->set($kohana_view_data); ?>

<?php echo View::factory('cart/email/order_info')
	->set($kohana_view_data); ?>