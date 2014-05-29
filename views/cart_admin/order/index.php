<div class="cart_admin">
	<div class="grid">
		<div class="col">
			<h1>Orders</h1>
			<?php echo $form_open; ?>
				<?php echo $order_filters_html['status']; ?>
			</form>

			<ul class="list list_orders js_list">
				<?php echo $order_html; ?>
			</ul>
		</div>
	</div>
</div>