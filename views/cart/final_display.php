<?php if ( ! empty($order->order_note)) { ?>
<p><strong>Order Notes</strong><br>
	<?php echo nl2br(HTML::chars($order->order_note)); ?></p>
<?php } // if ?>