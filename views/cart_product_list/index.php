<div class="grid">
	<div class="col-1-3">
		<h1>Products</h1>

		<ul>
		<?php echo $product_list; ?>
		</ul>
	</div>
	<div class="col-2-3">
		<h2>Cart</h2>
		<div class="js_cart">
			<span class="js_loading"><?php echo HTML::spinner(); ?></span>
		</div>
	</div>
</div>

<?php echo View::factory('cart/config')->set($kohana_view_data); ?>