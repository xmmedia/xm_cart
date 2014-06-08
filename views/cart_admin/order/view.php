<div class="cart_admin">
	<div class="grid cart_order_view_header">
		<div class="col-1-2 cart">
			<h1>Order<?php if ( ! empty($order->order_num)) { echo ' – ', HTML::chars($order->order_num); } ?></h1>
			<?php echo HTML::anchor(Route::get('cart_admin_order')->uri(), HTML::icon('chevron-left') . 'Order List'); ?>
		</div>
		<div class="col-1-2 cart">
			<h2 class="text_right" title="Order Status"><?php echo $order->get_radio_value_string('status'); ?></h2>
		</div>
	</div>

	<div class="grid">
		<?php if (Cart_Config::enable_shipping()) { ?>
		<div class="col-1-3 cart">
			<strong>Shipping Address</strong><br>
			<?php echo Cart::address_html($order->shipping_formatted()); ?>
		</div>
		<?php } ?>
		<div class="col-1-3 cart">
			<?php if ( ! Cart_Config::enable_shipping() || ! $order->same_as_shipping_flag) { ?>
				<strong>Billing Contact</strong><br>
				<?php echo Cart::address_html($order->billing_contact_formatted()); ?><br><br>
				<strong>Billing Address</strong><br>
				<?php echo Cart::address_html($order->billing_address_formatted()); ?>
			<?php } else { ?>
				<strong>Billing Information</strong><br>
				<em>Same as shipping</em>
			<?php } ?>
		</div>
		<div class="col-1-3 cart">
			Paid with <?php echo HTML::chars($paid_with['type'] . ' ending in ' . $paid_with['last_4']); ?><br><br>
			Order Number: <?php echo HTML::chars($order->order_num); ?>
		</div>
	</div>

	<div class="grid">
		<div class="col cart">
			<?php echo $cart_html; ?>

			<?php if ( ! empty($actions)) : ?>
				<div class="cart_order_actions js_cart_order_actions">
					<?php foreach ($actions as $action_title => $action_attr) : ?>
						<?php echo HTML::anchor('', $action_title, $action_attr); ?>
					<?php endforeach ?>
				</div>
			<?php endif ?>

			<?php if ( ! empty($order->order_note)) { ?>
			<p><strong>Notes</strong>
				<br><?php echo nl2br(HTML::chars($order->order_note)); ?></p>
			<?php } ?>
		</div>
	</div>
</div>

<script>
var cart_order_view_data = {
	order : {
		id : <?php echo json_encode((int) $order->pk()); ?>,
		grand_total : <?php echo json_encode(floatval($order->grand_total)); ?>,
		grand_total_formatted : <?php echo json_encode(Cart::cf($order->grand_total)); ?>,
		refund_total : <?php echo json_encode(floatval($order->refund_total)); ?>,
		refund_total_formatted : <?php echo json_encode(Cart::cf($order->refund_total)); ?>,
		final_total : <?php echo json_encode(floatval($order->final_total())); ?>,
		final_total_formatted : <?php echo json_encode(Cart::cf($order->final_total())); ?>,
	}
}
</script>