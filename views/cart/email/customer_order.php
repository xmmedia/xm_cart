<?php echo $order_message_html; ?>

<p><?php echo HTML::anchor($view_order_url, 'View your order online'); ?> along with the current status.</p>

<?php echo View::factory($cart_view)
	->set($kohana_view_data); ?>

<?php echo View::factory('cart/email/order_info')
	->set($kohana_view_data); ?>