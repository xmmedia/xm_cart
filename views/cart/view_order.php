<div class="js_view_order">
	<div class="grid">
		<div class="col">
			<?php echo Cart::page_header('Your Order'); ?>
		</div>
	</div>

	<div class="grid">
		<?php if ($show_private_info) : ?>
			<?php if (Cart_Config::enable_shipping()) : ?>
			<div class="col-1-3 cart">
				<strong>Shipping Address</strong><br>
				<?php echo Cart::address_html($order->shipping_formatted()); ?>
			</div>
			<?php endif ?>
			<div class="col-1-3 cart">
				<?php if ( ! Cart_Config::enable_shipping() || ! $order->same_as_shipping_flag) : ?>
					<strong>Billing Contact</strong><br>
					<?php echo Cart::address_html($order->billing_contact_formatted()); ?><br><br>
					<strong>Billing Address</strong><br>
					<?php echo Cart::address_html($order->billing_address_formatted()); ?>
				<?php else : ?>
					<strong>Billing Information</strong><br>
					<em>Same as shipping</em>
				<?php endif ?>
			</div>
		<?php endif ?>
		<div class="col-1-3 cart">
			<strong>Order Status: <?php echo $order->get_radio_value_string('status'); ?></strong><br><br>
			<?php if ($show_private_info) : ?>
				Paid with <?php echo HTML::chars($paid_with['type'] . ' ending in ' . $paid_with['last_4']); ?><br><br>
			<?php endif ?>
			Order Number: <?php echo HTML::chars($order->order_num); ?>
		</div>
	</div>

	<div class="grid">
		<div class="col cart">
			<?php echo $cart_html; ?>

			<?php if ( ! empty($order->order_note)) : ?>
			<p><strong>Notes</strong>
				<br><?php echo nl2br(HTML::chars($order->order_note)); ?></p>
			<?php endif ?>
		</div>
	</div>
</div>