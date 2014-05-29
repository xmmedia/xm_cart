<div class="grid">
	<div class="col completed_container">
		<?php echo Cart::page_header('Thank you for your donation'); ?>

		<p>Your transaction has been completed. Please check your email for a receipt. You will receive a tax deductible donation receipt at the end of the year.</p>

		<?php if ($offer_registration) : ?>
			<h2>Register</h2>
			<p>Registering for an account will make the checkout process faster next time.</p>
			<?php echo Form::open($register_uri), Form::button(NULL, 'Register'), Form::close(); ?>
		<?php endif ?>
	</div>
</div>

<?php echo View::factory('cart/config')->set($kohana_view_data); ?>