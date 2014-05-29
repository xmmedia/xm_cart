<div class="cart_admin">
	<div class="grid">
		<div class="col">
			<h1>Taxes</h1>

			<?php echo HTML::anchor($add_uri, 'Add Tax'); ?>

			<ul class="cart_rate_list">
				<?php foreach ($taxes_html as $tax) { ?>
				<li><?php echo $tax; ?></li>
				<?php } // foreach ?>
			</ul>
		</div>
	</div>
</div>