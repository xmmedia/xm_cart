<?php echo View::factory($cart_view)
	->set($kohana_view_data); ?>

<?php echo View::factory('cart/email/order_info')
	->set($kohana_view_data); ?>

<p><?php echo HTML::anchor($admin_view_order_url, 'View ' . HTML::chars($order->order_num) . ' online'); ?></p>