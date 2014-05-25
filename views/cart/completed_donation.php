<div class="grid">
	<div class="col">
		<?php echo Cart::page_header('Thank you for your donation'); ?>

		<p>Your transaction has been completed. Please check your email for a receipt. You will receive a tax deductible donation receipt at the end of the year.</p>
	</div>
</div>

<?php echo View::factory('cart/config')->set($kohana_view_data); ?>