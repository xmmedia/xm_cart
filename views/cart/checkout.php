<div class="grid">
	<div class="col cart">
		<h1>Checkout</h1>

		<div class="cart_checkout_box cart_checkout_box_cart">
			<h2>1. Your Cart</h2>
			<?php echo $cart_html; ?>
			<div class="cart_checkbox_box_actions">
				<div class="cart_checkbox_box_actions_left">
					<?php echo HTML::anchor($continue_shopping_url, 'Back to Cart'); ?>
				</div>
				<div class="cart_checkbox_box_actions_right">
					<?php echo Form::input_button(NULL, 'Continue', array('class' => 'js_cart_checkout_continue')); ?>
				</div>
			</div>
		</div>



		<div class="cart_checkout_box cart_checkout_box_shipping">
			<h2>2. Shipping Address</h2>

			<p><strong>Shipping Contact</strong></p>
			<?php echo Form::open(); ?>
			<div class="cart_field">
				<?php echo $order->get_field_layout('shipping_first_name'); ?>
			</div>
			<div class="cart_field">
				<?php echo $order->get_field_layout('shipping_last_name'); ?>
			</div>
			<div class="cart_field">
				<?php echo $order->get_field_layout('shipping_phone'); ?>
			</div>
			<div class="cart_field">
				<?php echo $order->get_field_layout('shipping_email'); ?>
			</div>

			<p><strong>Shipping Address</strong></p>
			<div class="cart_field">
				<?php echo $order->get_field_layout('shipping_company'); ?>
			</div>
			<div class="cart_field">
				<?php echo $order->get_field_layout('shipping_address_1'); ?>
			</div>
			<div class="cart_field">
				<?php echo $order->get_field_layout('shipping_address_2'); ?>
			</div>
			<div class="cart_field">
				<?php echo $order->get_field_layout('shipping_city'); ?>
			</div>
			<div class="cart_field">
				<?php echo $order->get_field_layout('shipping_state_id'); ?>
			</div>
			<div class="cart_field">
				<?php echo $order->get_field_layout('shipping_postal_code'); ?>
			</div>
			<div class="cart_field">
				<?php echo $order->get_field_layout('shipping_country_id'); ?>
			</div>
			<?php echo Form::close(); ?>

			<div class="cart_checkbox_box_actions">
				<div class="cart_checkbox_box_actions_right">
					<?php echo Form::input_button(NULL, 'Continue', array('class' => 'js_cart_checkout_continue')); ?>
				</div>
			</div>
		</div>



		<div class="cart_checkout_box cart_checkout_box_payment">
			<h2>3. Payment</h2>

			<div class="cart_billing_container">
				<?php echo Form::open(); ?>

				<div class="cart_billing_address">
					<p><strong>Billing Contact</strong></p>
					<div class="cart_field">
						<?php echo $order->get_field_layout('billing_first_name'); ?>
					</div>
					<div class="cart_field">
						<?php echo $order->get_field_layout('billing_last_name'); ?>
					</div>
					<div class="cart_field">
						<?php echo $order->get_field_layout('billing_phone'); ?>
					</div>
					<div class="cart_field">
						<?php echo $order->get_field_layout('billing_email'); ?>
					</div>

					<p><strong>Billing Address</strong></p>
					<div class="cart_field">
						<?php echo $order->get_field_layout('billing_company'); ?>
					</div>
					<div class="cart_field">
						<?php echo $order->get_field_layout('billing_address_1'); ?>
					</div>
					<div class="cart_field">
						<?php echo $order->get_field_layout('billing_address_2'); ?>
					</div>
					<div class="cart_field">
						<?php echo $order->get_field_layout('billing_city'); ?>
					</div>
					<div class="cart_field">
						<?php echo $order->get_field_layout('billing_state_id'); ?>
					</div>
					<div class="cart_field">
						<?php echo $order->get_field_layout('billing_postal_code'); ?>
					</div>
					<div class="cart_field">
						<?php echo $order->get_field_layout('billing_country_id'); ?>
					</div>
				</div>

				<div class="cart_credit_card">
					<p><strong>Payment Method</strong></p>
					<div class="cart_field">
						<?php echo Form::label('credit_card_number', 'Credit Card Number'), Form::input('credit_card[number]', NULL, array('size' => 16, 'maxlength' => 16, 'id' => 'credit_card_number')); ?>
					</div>
					<div class="cart_field">
						<?php echo Form::label('credit_card_security_code', 'Security Code'), Form::input('credit_card[security_code]', NULL, array('size' => 3, 'maxlength' => 3, 'id' => 'credit_card_security_code')); ?>
					</div>
					<div class="cart_field">
						<?php echo Form::label('credit_card_expiry_date_month', 'Expiry Date'),
							Form::select('credit_card[expiry_date][month]', $expiry_date_months, NULL, array('id' => 'credit_card_expiry_date_month')),
							Form::select('credit_card[expiry_date][year]', $expiry_date_years, NULL, array('id' => 'credit_card_expiry_date_year')); ?>
					</div>
				</div>
				<?php echo Form::close(); ?>
			</div>

			<div class="cart_checkbox_box_actions">
				<div class="cart_checkbox_box_actions_right">
					<?php echo Form::input_button(NULL, 'Continue', array('class' => 'js_cart_checkout_continue')); ?>
				</div>
			</div>
		</div>



		<div class="cart_checkout_box cart_checkout_box_confirm">
			<h2>4. Confirm Your Order</h2>

			<?php echo Form::open(); ?>
			<div class="cart_field">
				<?php echo $order->get_field_layout('order_note'); ?>
			</div>
			<?php echo Form::close(); ?>

			<div class="cart_checkbox_box_actions">
				<div class="cart_checkbox_box_actions_right">
					<?php echo Form::input_button(NULL, 'Continue', array('class' => 'js_cart_checkout_continue')); ?>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
var cart_config = {
	prefix : <?php echo json_encode($cart_prefix); ?>
};
</script>