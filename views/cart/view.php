<div class="grid">
	<div class="col">
		<?php echo Cart::page_header('Cart'); ?>
		<div class="js_cart">
			<span class="js_loading"><?php echo HTML::spinner(); ?></span>
		</div>
	</div>
</div>

<?php echo View::factory('cart/config')->set($kohana_view_data); ?>