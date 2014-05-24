<div class="grid">
	<div class="col">
		<h1>Cart</h1>
		<div class="js_cart">
			<span class="js_loading"><?php echo HTML::spinner(); ?></span>
		</div>
	</div>
</div>

<?php echo View::factory('cart/config')->set($kohana_view_data); ?>