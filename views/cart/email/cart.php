<table cellpadding="2" cellspacing="0" width="100%">
	<thead>
		<tr>
			<th width="55%"><img src="<?php echo URL_ROOT; ?>/images/spacer.gif" width="100" height="1"></th>
			<th width="15%"><img src="<?php echo URL_ROOT; ?>/images/spacer.gif" width="75" height="1"></th>
			<th width="15%"><img src="<?php echo URL_ROOT; ?>/images/spacer.gif" width="75" height="1"></th>
			<th width="15%"><img src="<?php echo URL_ROOT; ?>/images/spacer.gif" width="75" height="1"></th>
		</tr>
		<tr>
			<th align="left" valign="top"><font size="2">Item</font></th>
			<th align="center" valign="top"><font size="2">Quantity</font></th>
			<th align="right" valign="top"><font size="2">Unit Price</font></th>
			<th align="right" valign="top"><font size="2">Amount</font></th>
		</tr>
	</thead>
	<tbody>
		<?php
		$i = 0;
		foreach ($order_product_array as $order_product) {
			?>
			<tr<?php echo ($i % 2 ? '' : ' style="background-color: #e6e6e6;"'); ?>>
				<td align="left" valign="top"><font size="2"><?php echo HTML::chars($order_product->cart_product->name); ?></font></td>
				<td align="center" valign="top"><font size="2"><?php echo (int) $order_product->quantity; ?></font></td>
				<td align="right" valign="top"><font size="2"><?php echo HTML::chars(Cart::cf($order_product->unit_price)); ?></font></td>
				<td align="right" valign="top"><font size="2"><?php echo HTML::chars(Cart::cf($order_product->amount())); ?></font></td>
			</tr>
			<?php
			++ $i;
		}
		?>
	</tbody>
</table>

<table cellpadding="2" cellspacing="0" width="100%">
	<tbody>
		<?php
		foreach ($total_rows as $total_row) {
			if (isset($total_row['is_grand_total']) && $total_row['is_grand_total']) { ?>
			<tr>
				<td></td>
				<td align="left" valign="top"><span style="font-weight: bold;"><font size="2"><?php echo HTML::chars($total_row['name']); ?></font></span></td>
				<td align="right" valign="top"><span style="font-weight: bold;"><font size="2"><?php echo HTML::chars(Cart::cf($total_row['value'])); ?></font></span></td>
			</tr>
			<?php
			} else {
			?>
			<tr>
				<td></td>
				<td align="left" valign="top"><font size="2"><?php echo HTML::chars($total_row['name']); ?></font></td>
				<td align="right" valign="top"><font size="2"><?php echo HTML::chars(Cart::cf($total_row['value'])); ?></font></td>
			</tr>
			<?php
			} // if
		}
		?>
		<tr>
			<th width="60%"><img src="<?php echo URL_ROOT; ?>/images/spacer.gif" width="50" height="1"></th>
			<th width="20%"><img src="<?php echo URL_ROOT; ?>/images/spacer.gif" width="100" height="1"></th>
			<th width="20%"><img src="<?php echo URL_ROOT; ?>/images/spacer.gif" width="75" height="1"></th>
		</tr>
	</tbody>
</table>