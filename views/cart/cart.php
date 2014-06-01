<table class="cart_product_list js_cart_product_list">
	<thead>
		<tr>
			<th class="col_name">Item</th>
			<th class="col_quantity">Quantity</th>
			<th class="col_unit_price">Unit Price</th>
			<th class="col_amount">Amount</th>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach ($order_product_array as $order_product) {
			?>
			<tr>
				<td class="col_name js_col_name" data-view-url="<?php echo HTML::chars(URL::site($order_product->cart_product->view_uri())); ?>">
					<?php if (Cart_Config::show_product_photo()) : ?>
						<div class="photo"><img src="<?php echo HTML::chars(URL::site($order_product->cart_product->photo_uri())); ?>"></div>
					<?php endif ?>
					<span class="name"><?php echo HTML::chars($order_product->cart_product->name()); ?></span>
					<?php if ( ! empty($order_product->cart_product->description)) { ?>
					<div class="product_description"><?php echo HTML::chars($order_product->cart_product->description); ?></div>
					<?php } ?>
				</td>
				<td class="col_quantity"><?php echo (int) $order_product->quantity; ?></td>
				<td class="col_unit_price"><?php echo HTML::chars(Cart::cf($order_product->unit_price)); ?></td>
				<td class="col_amount"><?php echo HTML::chars(Cart::cf($order_product->amount())); ?></td>
			</tr>
			<?php
		}

		foreach ($total_rows as $total_row) {
			?>
			<tr class="total_row<?php echo (isset($total_row['is_grand_total']) ? ' grand_total' : '') ; ?>">
				<td class="col_name"></td>
				<td class="col_unit_price" colspan="2"><?php echo HTML::chars($total_row['name']); ?></td>
				<td class="col_amount"><?php echo HTML::chars($total_row['value_formatted']); ?></td>
			</tr>
			<?php
		}
		?>
	</tbody>
</table>