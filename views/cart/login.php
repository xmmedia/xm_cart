<?php
if ($register_uri) :
	$col_class = 'col-1-3';
else :
	$col_class = 'col-1-2';
endif;
?>
<div class="grid cart">
	<div class="<?php echo $col_class; ?> cart_login_container">
		<h1>Returning Customer</h1>
		<?php echo Form::open($login_uri),
			Form::hidden('redirect', $checkout_uri); ?>
			<div class="cart_field">
				<label for="username">Email Address</label>
				<?php echo Form::input('username', NULL, array('size' => 20, 'maxlength' => 100, 'id' => 'username', 'autofocus')); ?>
			</div>
			<div class="cart_field">
				<label for="password">Password</label>
				<?php echo Form::password('password', NULL, array('size' => 20, 'maxlength' => 255, 'id' => 'password')); ?>
			</div>

			<div class="buttons"><?php echo Form::button(NULL, 'Login'); ?></div>
		</form>

		<div class="go_link"><?php echo HTML::anchor($forgot_password_uri, 'Forgot your password?') ?></div>
	</div>

	<?php if ($register_uri) : ?>
		<div class="<?php echo $col_class; ?> cart_login_container">
			<h1>Register</h1>
			<p>Registering for an account will make the checkout process faster.</p>
			<?php echo Form::open($register_uri), Form::button(NULL, 'Register'), Form::close(); ?>
		</div>
	<?php endif ?>

	<div class="<?php echo $col_class; ?> cart_login_container">
		<h1>Guest Checkout</h1>
		<p>Proceed to checkout, and you can create an account at the end.</p>
		<?php echo Form::open($checkout_uri), Form::hidden('continue_as_guest', 1), Form::button(NULL, 'Continue as Guest'), Form::close(); ?>
	</div>
</div>

<div class="grid cart">
	<div class="col">
		<?php echo HTML::anchor($cart_view_url, HTML::chars(Cart::message('checkout.back_to_cart'))); ?>
	</div>
</div>