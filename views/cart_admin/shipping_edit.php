<div class="grid">
	<div class="col">
		<h1>Shipping Rate Edit</h1>
		<?php echo $form_open; ?>
			<div class="field"><?php echo $shipping_rate->get_field_layout('name'); ?></div>
			<div class="field"><?php echo $shipping_rate->get_field_layout('display_name'); ?></div>
			<div class="field"><?php echo $shipping_rate->get_field_layout('start'); ?></div>
			<div class="field"><?php echo $shipping_rate->get_field_layout('end'); ?></div>
			<div class="field"><?php echo $shipping_rate->get_field_layout('calculation_method'); ?></div>
			<div class="field"><?php echo $shipping_rate->get_field_layout('amount'); ?></div>
			<div class="field"><?php echo $shipping_rate->get_field_layout('display_order'); ?></div>

			<div class="field">
				<label>Reasons</label>
				<?php echo Form::select('reasons[0][reason]', $reasons); ?>
			</div>

			<div class="buttons"><?php
				echo Form::submit(NULL, 'Save'),
					Form::input_button(NULL, 'Cancel', array('class' => 'js_cl4_button_link', 'data-cl4_link' => $cancel_uri));
			?></div>
		</form>
	</div>
</div>