<div class="cart_admin">
	<div class="grid">
		<div class="col">
			<h1>Shipping Rates</h1>

			<?php echo HTML::anchor($add_uri, HTML::icon('plus') . 'Add Shipping Rate'); ?>

			<ul class="cart_rate_list js_cart_shipping_list">
				<?php foreach ($shipping_rate_html as $rate) : ?>
				<li><?php echo $rate; ?></li>
				<?php endforeach ?>
			</ul>
		</div>
	</div>
</div>