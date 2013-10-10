<table cellpadding="1" cellspacing="0" width="100%">
	<thead>
		<tr>
			<th width="70%" align="left">Item</th>
			<th width="10%" align="right">Quantity</th>
			<th width="10%" align="right">Unit Price</th>
			<th width="10%" align="right">Amount</th>
		</tr>
	</thead>
	<tbody>
		<?php
		$i = 0;
		foreach ($order_product_array as $order_product) {
			?>
			<tr<?php echo ($i % 2 ? '' : ' style="background-color: #e6e6e6;"'); ?>>
				<td align="left"><?php echo HTML::chars($order_product->cart_product->name); ?></td>
				<td align="right"><?php echo (int) $order_product->quantity; ?></td>
				<td align="right"><?php echo HTML::chars(Cart::cf($order_product->unit_price)); ?></td>
				<td align="right"><?php echo HTML::chars(Cart::cf($order_product->amount())); ?></td>
			</tr>
			<?php
			++ $i;
		}

		foreach ($total_rows as $total_row) {
			if (isset($total_row['is_grand_total']) && $total_row['is_grand_total']) { ?>
			<tr>
				<td></td>
				<td colspan="2" align="left"><span style="font-weight: bold;"><?php echo HTML::chars($total_row['name']); ?></span></td>
				<td align="right"><span style="font-weight: bold;"><?php echo HTML::chars(Cart::cf($total_row['value'])); ?></span></td>
			</tr>
			<?php
			} else {
			?>
			<tr>
				<td colspan="2"></td>
				<td align="left"><?php echo HTML::chars($total_row['name']); ?></td>
				<td align="right"><?php echo HTML::chars(Cart::cf($total_row['value'])); ?></td>
			</tr>
			<?php
			} // if
		}
		?>
	</tbody>
</table>