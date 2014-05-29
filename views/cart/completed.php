<div class="grid">
	<div class="col completed_container">
		<?php echo Cart::page_header('Thank you'); ?>

		<p>Your order has been completed. Please check your email for your receipt.</p>

		<?php if ($offer_registration) : ?>
			<h2>Register</h2>
			<p>Registering for an account will make the checkout process faster next time.</p>
			<?php echo Form::open($register_uri), Form::button(NULL, 'Register'), Form::close(); ?>
		<?php endif ?>
	</div>
</div>

<?php echo View::factory('cart/config')->set($kohana_view_data); ?>