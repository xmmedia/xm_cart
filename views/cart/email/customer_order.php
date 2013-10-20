<p>Thank you for your order. We have received your order and are currently processing it. Below are the details of your order.</p>

<?php echo View::factory('cart/email/cart')
	->set($kohana_view_data); ?>

<div style="height: 15px;"></div>

<table cellpadding="3" cellspacing="0" width="100%" border="0">
	<tbody>
		<tr>
			<td valign="top" width="33%">
				<strong>Shipping Address</strong><br>
				<?php echo Cart::address_html($order->shipping_formatted()); ?>
			</td>
			<td valign="top" width="33%">
			<?php if ( ! $order->same_as_shipping_flag) { ?>
				<strong>Billing Contact</strong><br>
				<?php echo Cart::address_html($order->billing_contact_formatted()); ?>
				<strong>Billing Address</strong><br>
				<?php echo Cart::address_html($order->billing_address_formatted()); ?>
			<?php } else { ?>
				<strong>Billing Information</strong><br>
				<em>Same as shipping</em>
			<?php } // if ?>
			</td>
			<td valign="top" width="33%">Paid with <?php echo HTML::chars($paid_with['type'] . ' ending in ' . $paid_with['last_4']); ?><br><br>
				Order Number: <?php echo HTML::chars($order->order_num); ?></td>
		</tr>
	</tbody>
</table>

<?php if ( ! empty($order->order_note)) { ?>
<p><strong>Notes</strong>
	<br><?php echo nl2br(HTML::chars($order->order_note)); ?></p>
<?php } // if ?>