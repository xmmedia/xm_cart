<li>
	<div class="actions"><?php echo HTML::anchor($view_uri, 'View', array('class' => 'js_link')); ?></div>
	<div class="order_num"><?php echo HTML::chars($order->order_num); ?></div>
	<div class="status"><?php echo $order->get_field('status'), '<br>', HTML::chars($last_log->timestamp); ?></div>
	<div class="name">
		<?php if ($billing_shipping_diff) : ?>
		<div title="Shipping"><?php echo HTML::chars($order->shipping_first_name . ' ' . $order->shipping_last_name), ' ', HTML::mailto($order->shipping_email); ?></div>
		<div title="Billing"><?php echo HTML::chars($order->billing_first_name . ' ' . $order->billing_last_name), ' ', HTML::mailto($order->shipping_email); ?></div>
		<?php else : ?>
			<?php echo HTML::chars($order->shipping_first_name, ' ', $order->shipping_last_name), '<br>', HTML::mailto($order->shipping_email); ?>
		<?php endif ?>
	</div>
	<div class="total"><?php echo Cart::cf($order->grand_total); ?></div>
</li>