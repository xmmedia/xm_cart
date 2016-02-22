<div class="cart_admin">
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
					echo Form::button(NULL, 'Save'),
						Form::anchor($cancel_uri, 'Cancel');
				?></div>
			</form>
		</div>
	</div>
</div>