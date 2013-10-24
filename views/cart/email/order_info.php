<div style="height: 15px;"></div>

<?php
if ($enable_shipping) {
	$col_width = '33%';
} else {
	$col_width = '50%';
}
?>

<table cellpadding="3" cellspacing="0" width="100%" border="0">
	<tbody>
		<tr>
			<?php if ($enable_shipping) { ?>
			<td valign="top" width="<?php echo $col_width; ?>">
				<strong>Shipping Address</strong><br>
				<?php echo Cart::address_html($order->shipping_formatted()); ?>
			</td>
			<?php } // if ?>
			<td valign="top" width="<?php echo $col_width; ?>">
			<?php if ( ! $order->same_as_shipping_flag) { ?>
				<strong><?php echo ($donation_cart ? '' : 'Billing '); ?>Contact</strong><br>
				<?php echo Cart::address_html($order->billing_contact_formatted()); ?><br><br>
				<strong><?php echo ($donation_cart ? '' : 'Billing '); ?>Address</strong><br>
				<?php echo Cart::address_html($order->billing_address_formatted()); ?>
			<?php } else { ?>
				<strong><?php echo ($donation_cart ? '' : 'Billing '); ?>Information</strong><br>
				<em>Same as shipping</em>
			<?php } // if ?>
			</td>
			<td valign="top" width="<?php echo $col_width; ?>">Paid with <?php echo HTML::chars($paid_with['type'] . ' ending in ' . $paid_with['last_4']); ?><br><br>
				<?php echo ($donation_cart ? 'Transaction' : 'Order'); ?> Number: <?php echo HTML::chars($order->order_num); ?></td>
		</tr>
	</tbody>
</table>

<?php if ( ! empty($order->order_note)) { ?>
<p><strong>Notes</strong>
	<br><?php echo nl2br(HTML::chars($order->order_note)); ?></p>
<?php } // if ?>