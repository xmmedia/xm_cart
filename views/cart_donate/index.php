<div class="grid">
	<div class="col-1-3">
		<h1>Donate Now</h1>

		<div class="cart">
			<?php echo Form::open(Route::get('cart_donate')->uri(array('action' => 'submit_donation'))); ?>
			<div class="cart_field"><?php
				echo Form::label('donation', 'Donation Amount'),
					'$',
					Form::input('donation', Kohana::$config->load('xm_cart.donation_minimum'), array('size' => 8, 'maxlength' => 8, 'class' => 'text_right', 'id' => 'donation')); ?></div>
			<div class="cart_field"><?php echo Form::submit(NULL, 'Submit'); ?></div>
			<?php echo Form::close(); ?>
		</div>
	</div>
</div>