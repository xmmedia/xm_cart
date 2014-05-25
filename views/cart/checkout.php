<?php $step_count = 1; ?>
<div class="grid">
	<div class="col cart js_cart_checkout">
		<?php echo Cart::page_header('Checkout'); ?>

		<div class="cart_checkout_box cart_checkout_box_cart js_cart_checkout_step" data-cart_checkout_step="<?php echo $step_count; ?>" data-cart_checkout_step_type="cart">
			<div class="cart_checkout_box_closed cart_checkout_box_edit hidden js_cart_checkout_box_closed">
				<a href="" class="js_cart_checkout_box_edit">Edit</a>
			</div>

			<h2><?php echo $step_count; ?>. <?php echo HTML::chars(Cart::message('checkout.headers.cart')); ?></h2>

			<div class="cart_checkout_box_open js_cart_checkout_box_open">
				<div class="js_cart_checkout_box_messages"></div>
				<p><strong><?php echo HTML::chars(Cart::message('checkout.confirm_your' . ($order->donation_cart_flag ? '_donation' : '_order'))); ?></strong></p>
				<?php echo $cart_html; ?>
				<div class="cart_checkout_box_actions">
					<div class="cart_checkout_box_actions_left">
						<?php echo HTML::anchor($continue_shopping_url, HTML::chars(Cart::message('checkout.continue_shopping'))); ?>
						<?php echo HTML::anchor($cart_view_url, HTML::chars(Cart::message('checkout.back_to_cart'))); ?>
					</div>
					<div class="cart_checkout_box_actions_right">
						<?php echo Form::input_button(NULL, 'Continue', array('class' => 'js_cart_checkout_continue')); ?>
					</div>
				</div>
			</div>

			<div class="cart_checkout_box_closed hidden js_cart_checkout_box_closed js_cart_checkout_box_result">
				<?php echo $cart_html; ?>
			</div>
		</div>
		<?php ++ $step_count; ?>


		<?php if ($enable_shipping) { ?>
		<div class="cart_checkout_box cart_checkout_box_shipping js_cart_checkout_step" data-cart_checkout_step="<?php echo $step_count; ?>" data-cart_checkout_step_type="shipping">
			<div class="cart_checkout_box_closed cart_checkout_box_edit hidden js_cart_checkout_box_closed">
				<a href="" class="js_cart_checkout_box_edit">Edit</a>
			</div>

			<h2><?php echo $step_count; ?>. <?php echo HTML::chars(Cart::message('checkout.headers.shipping')); ?></h2>

			<div class="cart_checkout_box_open hidden js_cart_checkout_box_open">
				<div class="js_cart_checkout_box_messages"></div>

				<p><strong>Shipping Contact</strong></p>
				<?php if (KOHANA_ENVIRONMENT > Kohana::PRODUCTION) { ?>
				<p><a href="" class="js_cart_add_shipping_test_values" title="Only available on test sites">Add Valid Test Values</a></p>
				<?php } ?>
				<?php echo Form::open(Route::get('cart_public')->uri(array('action' => 'save_shipping')) . '?c_ajax=1', array('class' => 'js_cart_checkout_form_shipping')); ?>
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
					<?php echo $order->get_field_layout('shipping_municipality'); ?>
				</div>
				<div class="cart_field">
					<?php echo $order->get_field_layout('shipping_state_id'); ?>
				</div>
				<div class="cart_field">
					<?php echo $order->get_field_layout('shipping_postal_code'); ?>
				</div>
				<?php if ($show_shipping_country) : ?>
					<div class="cart_field">
						<?php echo $order->get_field_layout('shipping_country_id'); ?>
					</div>
				<?php else : ?>
					<div class="cart_field">
						<label>Country</label>
						<?php echo HTML::chars($shipping_country_name),
							Form::hidden('c_record[order][0][shipping_country_id]', Cart_Config::load('default_country_id'), array('data-cart_shipping_field' => 'country_id')); ?>
					</div>
				<?php endif ?>
				<?php echo Form::close(); ?>

				<div class="cart_checkout_box_actions">
					<div class="cart_checkout_box_actions_right">
						<?php echo Form::input_button(NULL, 'Continue', array('class' => 'js_cart_checkout_continue')); ?>
					</div>
				</div>
			</div>

			<div class="cart_checkout_box_closed hidden js_cart_checkout_box_closed js_cart_checkout_box_result"></div>
		</div>
		<?php ++ $step_count; ?>
		<?php } // if enable shipping ?>



		<div class="cart_checkout_box cart_checkout_box_payment js_cart_checkout_step" data-cart_checkout_step="<?php echo $step_count; ?>" data-cart_checkout_step_type="payment">
			<div class="cart_checkout_box_closed cart_checkout_box_edit hidden js_cart_checkout_box_closed">
				<a href="" class="js_cart_checkout_box_edit">Edit</a>
			</div>

			<h2><?php echo $step_count; ?>. <?php echo HTML::chars(Cart::message('checkout.headers.payment')); ?></h2>

			<div class="cart_checkout_box_open hidden js_cart_checkout_box_open">
				<div class="cart_billing_container">
					<div class="js_cart_checkout_box_messages"></div>
					<?php if ($enable_shipping) { ?>
					<p><a href="" class="js_cart_checkout_copy_shipping">Copy shipping contact &amp; address</a></p>
					<?php } ?>

					<div class="cart_billing_address">
						<?php echo Form::open(Route::get('cart_public')->uri(array('action' => 'save_billing')) . '?c_ajax=1', array('class' => 'js_cart_checkout_form_billing')),
							$order->get_field('same_as_shipping_flag'); ?>
						<p><strong>Billing Contact</strong></p>
						<?php if (KOHANA_ENVIRONMENT > Kohana::PRODUCTION) { ?>
						<p><a href="" class="js_cart_add_billing_test_values" title="Only available on test sites">Add Valid Test Values</a></p>
						<?php } ?>
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
						<?php if ($show_billing_company) { ?>
						<div class="cart_field">
							<?php echo $order->get_field_layout('billing_company'); ?>
						</div>
						<?php } ?>
						<div class="cart_field">
							<?php echo $order->get_field_layout('billing_address_1'); ?>
						</div>
						<div class="cart_field">
							<?php echo $order->get_field_layout('billing_address_2'); ?>
						</div>
						<div class="cart_field">
							<?php echo $order->get_field_layout('billing_municipality'); ?>
						</div>
						<div class="cart_field">
							<?php echo $order->get_field_layout('billing_state_id'); ?>
						</div>
						<div class="cart_field">
							<?php echo $order->get_field_layout('billing_postal_code'); ?>
						</div>
						<?php if ($show_billing_country) : ?>
							<div class="cart_field">
								<?php echo $order->get_field_layout('billing_country_id'); ?>
							</div>
						<?php else : ?>
							<div class="cart_field">
								<label>Country</label>
								<?php echo HTML::chars($billing_country_name),
									Form::hidden('c_record[order][0][billing_country_id]', Cart_Config::load('default_country_id'), array('data-cart_billing_field' => 'country_id')); ?>
							</div>
						<?php endif ?>
						<?php echo Form::close(); ?>
					</div>


					<div class="cart_credit_card">
						<?php echo Form::open(Route::get('cart_public')->uri(array('action' => 'validate_payment')) . '?c_ajax=1', array('class' => 'js_cart_checkout_form_payment')); ?>
						<p><strong>Payment Method</strong></p>

						<?php if (KOHANA_ENVIRONMENT > Kohana::PRODUCTION) { ?>
						<p><?php echo $card_testing_select; ?></p>
						<?php } ?>

						<div class="cart_field">
							<?php echo Form::label('credit_card_number', 'Credit Card Number'), Form::input('credit_card[number]', NULL, array('size' => 22, 'maxlength' => 20, 'id' => 'credit_card_number', 'class' => 'js_cart_checkout_credit_card_number')); ?>
						</div>
						<div class="cart_field">
							<?php echo Form::label('credit_card_security_code', 'Security Code'), Form::input('credit_card[security_code]', NULL, array('size' => 4, 'maxlength' => 4, 'id' => 'credit_card_security_code', 'class' => 'js_cart_checkout_credit_card_security_code')); ?>
						</div>
						<div class="cart_field">
							<?php echo Form::label('credit_card_expiry_date_month', 'Expiry Date'),
								Form::select('credit_card[expiry_date][month]', $expiry_date_months, NULL, array('id' => 'credit_card_expiry_date_month', 'class' => 'js_cart_checkout_credit_card_expiry_date_month')),
								Form::select('credit_card[expiry_date][year]', $expiry_date_years, NULL, array('id' => 'credit_card_expiry_date_year', 'class' => 'js_cart_checkout_credit_card_expiry_date_year')); ?>
						</div>
						<?php echo Form::close(); ?>
					</div>

				</div>

				<div class="cart_checkout_box_actions">
					<div class="cart_checkout_box_actions_right">
						<?php echo Form::input_button(NULL, 'Continue', array('class' => 'js_cart_checkout_continue')); ?>
					</div>
				</div>
			</div>

			<div class="cart_checkout_box_closed hidden cart_checkout_box_billing js_cart_checkout_box_closed js_cart_checkout_box_result"></div>
		</div>
		<?php ++ $step_count; ?>



		<div class="cart_checkout_box cart_checkout_box_final js_cart_checkout_step" data-cart_checkout_step="<?php echo $step_count; ?>" data-cart_checkout_step_type="final">
			<div class="cart_checkout_box_closed cart_checkout_box_edit hidden js_cart_checkout_box_closed">
				<a href="" class="js_cart_checkout_box_edit">Edit</a>
			</div>

			<h2><?php echo $step_count; ?>. <?php echo HTML::chars(Cart::message('checkout.headers.final')); ?></h2>

			<div class="cart_checkout_box_open hidden js_cart_checkout_box_open">
				<div class="js_cart_checkout_box_messages"></div>
				<?php echo Form::open(Route::get('cart_public')->uri(array('action' => 'save_final')) . '?c_ajax=1', array('class' => 'js_cart_checkout_form_final')); ?>
				<div class="cart_field">
					<?php echo Form::label($order->get_field_id('order_note'), HTML::chars(Cart::message('checkout.notes_label' . ($order->donation_cart_flag ? '_donation' : '')))), $order->get_field('order_note'); ?>
				</div>
				<?php echo Form::close(); ?>

				<div class="cart_checkout_box_actions">
					<div class="cart_checkout_box_actions_right">
						<?php echo Form::input_button(NULL, 'Continue', array('class' => 'js_cart_checkout_continue')); ?>
					</div>
				</div>
			</div>

			<div class="cart_checkout_box_closed hidden js_cart_checkout_box_closed js_cart_checkout_box_result"></div>
		</div>
		<?php ++ $step_count; ?>



		<div class="cart_checkout_box cart_checkout_box_confirm js_cart_checkout_step" data-cart_checkout_step="<?php echo $step_count; ?>" data-cart_checkout_step_type="confirm">
			<h2><?php echo $step_count; ?>. <?php echo HTML::chars(Cart::message('checkout.headers.confirm' . ($order->donation_cart_flag ? '_donation' : ''))); ?></h2>

			<div class="cart_checkout_box_open hidden js_cart_checkout_box_open">
				<div class="js_cart_checkout_box_messages"></div>
				<div style="text-align: right;">
					<div class="cart_checkout_totals js_cart_totals"></div>

					<div class="cart_checkout_box_actions">
						<div class="cart_checkout_box_actions_right">
							<?php echo Form::open(Route::get('cart_public')->uri(array('action' => 'complete_order')) . '?c_ajax=1', array('class' => 'js_cart_checkout_form_complete_order')),
								Form::hidden('stripe_token', NULL, array('class' => 'js_cart_checkout_stripe_token')); ?>
							<?php echo Form::submit(NULL, Cart::message('checkout.complete_' . ($order->donation_cart_flag ? 'donation' : 'order') . '_button'), array('class' => 'js_cart_checkout_complete_order_submit')); ?>
							<?php echo Form::close(); ?>
						</div>
					</div>

					<p><strong><?php echo HTML::chars(Cart::message('checkout.review_' . ($order->donation_cart_flag ? 'donation' : 'order'))); ?></strong></p>
				</div>
			</div>
		</div>
		<?php ++ $step_count; ?>



		<div class="cart_checkout_totals js_cart_totals js_cart_totals_outside"></div>
	</div>
</div>

<?php echo View::factory('cart/config')->set($kohana_view_data); ?>

<script>
var cart_preload = {
	total_rows : <?php echo json_encode($total_rows); ?>
};
</script>