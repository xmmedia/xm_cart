<table cellpadding="2" cellspacing="0" width="100%" border="0">
	<thead>
		<tr>
			<th width="55%"><img src="<?php echo URL_ROOT; ?>/images/spacer.gif" width="100" height="1"></th>
			<th width="15%"><img src="<?php echo URL_ROOT; ?>/images/spacer.gif" width="75" height="1"></th>
			<th width="15%"><img src="<?php echo URL_ROOT; ?>/images/spacer.gif" width="75" height="1"></th>
			<th width="15%"><img src="<?php echo URL_ROOT; ?>/images/spacer.gif" width="75" height="1"></th>
		</tr>
		<tr>
			<th align="left" valign="top">Item</th>
			<th align="center" valign="top">Quantity</th>
			<th align="right" valign="top">Unit Price</th>
			<th align="right" valign="top">Amount</th>
		</tr>
	</thead>
	<tbody>
		<?php
		$i = 0;
		foreach ($order_product_array as $order_product) {
			?>
			<tr<?php echo ($i % 2 ? '' : ' style="background-color: #e6e6e6;"'); ?>>
				<td align="left" valign="top"><?php echo HTML::chars($order_product->cart_product->name); ?></td>
				<td align="center" valign="top"><?php echo (int) $order_product->quantity; ?></td>
				<td align="right" valign="top"><?php echo HTML::chars(Cart::cf($order_product->unit_price)); ?></td>
				<td align="right" valign="top"><?php echo HTML::chars(Cart::cf($order_product->amount())); ?></td>
			</tr>
			<?php
			++ $i;
		}
		?>
	</tbody>
</table>

<table cellpadding="2" cellspacing="0" width="100%" border="0">
	<tbody>
		<?php
		foreach ($total_rows as $total_row) {
			if (isset($total_row['is_grand_total']) && $total_row['is_grand_total']) { ?>
			<tr>
				<td></td>
				<td align="right" valign="top"><span style="font-weight: bold;"><?php echo HTML::chars($total_row['name']); ?></span></td>
				<td align="right" valign="top"><span style="font-weight: bold;"><?php echo HTML::chars(Cart::cf($total_row['value'])); ?></span></td>
			</tr>
			<?php
			} else {
			?>
			<tr>
				<td></td>
				<td align="right" valign="top"><?php echo HTML::chars($total_row['name']); ?></td>
				<td align="right" valign="top"><?php echo HTML::chars(Cart::cf($total_row['value'])); ?></td>
			</tr>
			<?php
			} // if
		}
		?>
		<tr>
			<th width="55%"><img src="<?php echo URL_ROOT; ?>/images/spacer.gif" width="50" height="1"></th>
			<th width="30%"><img src="<?php echo URL_ROOT; ?>/images/spacer.gif" width="100" height="1"></th>
			<th width="15%"><img src="<?php echo URL_ROOT; ?>/images/spacer.gif" width="75" height="1"></th>
		</tr>
	</tbody>
</table>