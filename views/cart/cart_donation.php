<table class="cart_product_list cart_donation_list js_cart_product_list">
	<thead>
		<tr>
			<th class="col_name"></th>
			<th class="col_amount">Amount</th>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach ($order_product_array as $order_product) {
			?>
			<tr>
				<td class="col_name"><?php echo HTML::chars($order_product->cart_product->name()); ?></td>
				<td class="col_amount"><?php echo HTML::chars(Cart::cf($order_product->amount())); ?></td>
			</tr>
			<?php
		}

		foreach ($total_rows as $total_row) {
			?>
			<tr class="total_row<?php echo (isset($total_row['is_grand_total']) ? ' grand_total' : '') ; ?>">
				<td class="col_name"><?php echo HTML::chars($total_row['name']); ?></td>
				<td class="col_amount"><?php echo HTML::chars($total_row['value_formatted']); ?></td>
			</tr>
			<?php
		}
		?>
	</tbody>
</table>