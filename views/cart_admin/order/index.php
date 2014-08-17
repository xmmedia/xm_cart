<div class="cart_admin">
	<div class="grid">
		<div class="col">
			<h1>Orders</h1>
		</div>
	</div>

	<div class="grid">
		<div class="col-1-2">
			<?php echo $form_open; ?>
				<?php echo $order_filters_html['status']; ?>
			</form>
		</div>
		<div class="col-1-2 text_right">
			<?php echo HTML::anchor(Route::get('cart_admin_order')->uri(array('action' => 'export_filter')), HTML::icon('filetypes xlsx') . 'Export'); ?>
		</div>
	</div>

	<div class="grid">
		<div class="col">
			<ul class="list list_orders js_list">
				<?php echo $order_html; ?>
			</ul>
		</div>
	</div>
</div>