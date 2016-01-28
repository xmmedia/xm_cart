<div class="cart_admin">
	<div class="grid">
		<div class="col">
			<h1>Orders</h1>
			<div class="list_actions">
				<?php echo HTML::anchor($export_uri, 'Export'); ?>
			</div>
			<?php echo $form_open; ?>
				<?php echo $order_filters_html['status']; ?>
				<label for="order_filters_start_date">Paid Between</label>
				<?php echo $order_filters_html['start_date']; ?>
				<label for="order_filters_end_date">To</label>
				<?php echo $order_filters_html['end_date']; ?>
			</form>

			<ul class="list list_orders js_list">
				<?php echo $order_html; ?>
			</ul>
		</div>
	</div>
</div>