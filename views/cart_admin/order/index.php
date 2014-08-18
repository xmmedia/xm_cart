<div class="cart_admin">
	<div class="grid">
		<div class="col">
			<h1>Orders</h1>
		</div>
	</div>

	<div class="grid">
		<div class="col-1-2">
			<?php echo $form_open; ?>
				<div class="field">
					<?php echo Form::label(NULL, 'Order Status'), $order_filters_html['status']; ?>
				</div>
				<div class="field">
					<?php echo Form::label(NULL, 'Last Change'), $order_filters_html['time_frame_select']; ?>
					<span class="js_cart_order_time_frame_custom" style="display: none;"><?php echo $order_filters_html['time_frame_start'], ' – ', $order_filters_html['time_frame_end']; ?></span>
				</div>
				<div class="buttons"><?php echo Form::button(NULL, 'Filter Orders'), HTML::anchor($reset_order_filters_uri, 'Reset'); ?></div>
			</form>
		</div>
		<div class="col-1-2 text_right">
			<?php echo HTML::anchor($export_uri, HTML::icon('filetypes xlsx') . 'Export'); ?>
		</div>
	</div>

	<div class="grid">
		<div class="col">
			<ul class="list list_orders js_list">
				<?php echo $order_html; ?>
			</ul>
		</div>
	</div>
</div>