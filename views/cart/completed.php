<div class="grid">
	<div class="col">
		<?php echo Cart::page_header('Thank you'); ?>

		<p>Your order has been completed. Please check your email for your receipt.</p>
	</div>
</div>

<?php echo View::factory('cart/config')->set($kohana_view_data); ?>