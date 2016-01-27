<div class="cart_admin">
	<div class="grid">
		<div class="col">
			<h1>Orders</h1>
			<?php echo $form_open; ?>
				<?php echo $order_filters_html['status']; ?>
				<label for="order_filters_start_date">From</label>
				<?php echo $order_filters_html['start_date']; ?>
				<label for="order_filters_end_date">To</label>
				<?php echo $order_filters_html['end_date']; ?>
			</form>
			<?php echo HTML::anchor(Route::get('cart_admin_order_export')->uri(), 'Export'); ?>

			<ul class="list list_orders js_list">
				<?php echo $order_html; ?>
			</ul>
		</div>
	</div>
</div>