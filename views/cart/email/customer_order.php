<p>Thank you for your order. We have received your order and are currently processing it. Below are the details of your order.</p>

<?php echo View::factory('cart/email/cart')
	->set($kohana_view_data); ?>

<p><strong>Shipping Address</strong><br>
	<?php echo Cart::address_html($order->shipping_formatted()); ?></p>

<?php if ( ! $order->same_as_shipping_flag) { ?>
<p><strong>Billing Contact</strong><br>
	<?php echo Cart::address_html($order->billing_contact()); ?></p>
<p><strong>Billing Address</strong><br>
	<?php echo Cart::address_html($order->billing_formatted()); ?></p>
<?php } else { ?>
<p><strong>Billing Information</strong><br>
	<em>Same as shipping</em></p>
<?php } // if ?>

<p>Paid with <?php echo HTML::chars($paid_with['type'] . ' ending in ' . $paid_with['last_4']); ?></p>

<?php if ( ! empty($order->order_note)) { ?>
<p><strong>Notes</strong>
	<br><?php echo nl2br(HTML::chars($order->order_note)); ?>
<?php } // if ?>