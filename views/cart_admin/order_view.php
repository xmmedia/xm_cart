<div class="grid cart_order_view_header">
	<div class="col-1-2 cart">
		<h1>Order<?php if ( ! empty($order->order_num)) { echo ' – ', HTML::chars($order->order_num); } ?></h1>
	</div>
	<div class="col-1-2 cart">
		<h2 class="text_right" title="Order Status"><?php echo $order->get_radio_value_string('status'); ?></h2>
	</div>
</div>

<div class="grid">
	<div class="col-1-3 cart">
		<strong>Shipping Address</strong><br>
		<?php echo Cart::address_html($order->shipping_formatted()); ?>
	</div>
	<div class="col-1-3 cart">
		<?php if ( ! $order->same_as_shipping_flag) { ?>
			<strong>Billing Contact</strong><br>
			<?php echo Cart::address_html($order->billing_contact_formatted()); ?>
			<strong>Billing Address</strong><br>
			<?php echo Cart::address_html($order->billing_address_formatted()); ?>
		<?php } else { ?>
			<strong>Billing Information</strong><br>
			<em>Same as shipping</em>
		<?php } // if ?>
	</div>
	<div class="col-1-3 cart">
		Paid with <?php echo HTML::chars($paid_with['type'] . ' ending in ' . $paid_with['last_4']); ?><br><br>
		Order Number: <?php echo HTML::chars($order->order_num); ?>
	</div>
</div>

<div class="grid">
	<div class="col cart">
		<?php echo $cart_html; ?>

		<?php if ( ! empty($order->order_note)) { ?>
		<p><strong>Notes</strong>
			<br><?php echo nl2br(HTML::chars($order->order_note)); ?></p>
		<?php } // if ?>
	</div>
</div>