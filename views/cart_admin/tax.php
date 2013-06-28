<?php echo View::factory('cart_admin/menu'); ?>

<div class="grid">
	<div class="col">
		<h1>Taxes</h1>
		<ul>
			<?php foreach ($taxes_html as $tax) { ?>
			<li><?php echo $tax; ?></li>
			<?php } // foreach ?>
		</ul>
	</div>
</div>