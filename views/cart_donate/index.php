<div class="grid">
	<div class="col">
		<h1>Donate Now</h1>

		<div class="cart">
			<?php echo Form::open(Route::get('cart_donate')->uri(array('action' => 'submit_donation'))); ?>
			<div class="cart_field"><?php
				echo Form::label('donation', 'Donation Amount'),
					'$',
					Form::input('donation', $default_donation_amount, array('size' => 8, 'maxlength' => 8, 'class' => 'text_right', 'id' => 'donation')); ?></div>
			<div class="cart_field"><?php echo Form::button(NULL, 'Submit'); ?></div>
			<?php echo Form::close(); ?>

			<?php if (isset($order) && $order_has_other_products) { ?>
			<p>Note: Starting a donation will remove all existing products from your cart.</p>
			<?php } ?>
		</div>
	</div>
</div>