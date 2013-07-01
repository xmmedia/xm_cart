<?php echo View::factory('cart_admin/menu'); ?>

<div class="grid">
	<div class="col">
		<h1>Orders</h1>
		<?php echo $form_open; ?>
			<?php echo $order_filters_html['status']; ?>
		</form>

		<?php echo $order_html; ?>
	</div>
</div>