<div class="grid">
	<div class="col-1-3">
		<h1>Products</h1>

		<ul>
		<?php echo $product_list; ?>
		</ul>
	</div>
	<div class="col-2-3 js_cart">
	</div>
</div>

<script>
var cart_config = {
	prefix : <?php echo json_encode($cart_prefix); ?>
};
</script>