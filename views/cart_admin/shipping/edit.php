<div class="cart_admin">
	<div class="grid">
		<div class="col">
			<h1>Shipping Rate</h1>
			<?php echo $form_open; ?>
				<div class="col_container">
					<div class="col">
						<div class="field">
							<?php echo $shipping_rate->get_field_layout('name'); ?>
							<div class="help">This is the internal name of the shipping rate.</div>
						</div>
						<div class="field">
							<?php echo $shipping_rate->get_field_layout('display_name'); ?>
							<div class="help">This is the name of the shipping rate when displayed on the order to customers.</div>
						</div>
						<div class="field js_shipping_calc_method"><?php echo $shipping_rate->get_field_layout('calculation_method'); ?></div>
						<div class="field js_shipping_amount"><?php echo $shipping_rate->get_field_layout('amount'); ?></div>
						<div class="field">
							<?php echo $shipping_rate->get_field_layout('start'); ?>
							<div class="help">Leave the start and end dates blank if the shipping rate should take affect immediately and run indefinitely.</div>
						</div>
						<div class="field"><?php echo $shipping_rate->get_field_layout('end'); ?></div>
					</div>

					<div class="col">
						<ul class="list">
							<?php
							$reason_count = 0;
							foreach ($reasons as $reason) :
							?>
								<li class="field js_reason_container" data-reason-count="<?php echo $reason_count; ?>" data-reason-data="<?php echo HTML::chars(json_encode($reason)); ?>">
									<label>Reasons</label>
									<?php echo Form::select('reasons[0][reason]', $available_reasons, $reason['reason'], array('class' => 'js_shipping_reason')); ?>
									<div class="help js_reason_help"></div>
									<div class="js_reason_details"></div>
								</li>
							<?php endforeach ?>
						</ul>
					</div>
				</div>

				<div class="buttons"><?php
					echo Form::button(NULL, 'Save'),
						HTML::anchor($cancel_uri, 'Cancel');
				?></div>
			</form>
		</div>
	</div>
</div>

<script>
var cart_shipping_admin_config = {
	reason_count : <?php echo $reason_count; ?>, // start at one less than actual as it will incremented when the page loads
	sub_total_last : <?php echo $sub_total_last; ?>
};
</script>