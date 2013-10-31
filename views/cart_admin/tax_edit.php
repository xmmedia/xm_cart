<div class="grid">
	<div class="col">
		<h1>Tax Edit</h1>
		<?php echo $form_open; ?>
			<div class="field"><?php echo $tax->get_field_layout('name'); ?></div>
			<div class="field"><?php echo $tax->get_field_layout('display_name'); ?></div>
			<div class="field"><?php echo $tax->get_field_layout('start'); ?></div>
			<div class="field"><?php echo $tax->get_field_layout('end'); ?></div>
			<div class="field"><?php echo $tax->get_field_layout('calculation_method'); ?></div>
			<div class="field"><?php echo $tax->get_field_layout('amount'); ?></div>
			<div class="field checkbox"><?php echo $tax->get_field_layout('all_locations_flag', NULL, 'checkbox'); ?></div>
			<div class="field checkbox"><?php echo $tax->get_field_layout('only_without_flag', NULL, 'checkbox'); ?></div>
			<div class="field"><?php echo $tax->get_field_layout('country_id'); ?></div>
			<div class="field"><?php echo $tax->get_field_layout('state_id'); ?></div>
			<div class="field"><?php echo $tax->get_field_layout('display_order'); ?></div>

			<div class="buttons"><?php
				echo Form::submit(NULL, 'Save'),
					Form::input_button(NULL, 'Cancel', array('class' => 'js_xm_button_link', 'data-xm_link' => $cancel_uri));
			?></div>
		</form>
	</div>
</div>