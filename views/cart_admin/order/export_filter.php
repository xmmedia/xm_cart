<div class="cart_admin">
	<div class="grid">
		<div class="col cart">
			<h1>Order Export</h1>

			<?php echo $form_open; ?>
				<div class="field">
					<?php echo Form::label('time_frame', 'Time Frame'), $time_frame_select; ?>
					<span class="js_cart_order_time_frame_custom" style="display: none;"><?php echo $time_frame_start, ' – ', $time_frame_end; ?></span>
				</div>
				<div class="buttons"><?php
					echo Form::button(NULL, 'Generate Export'),
						HTML::anchor($cancel_uri, 'Back to Order List');
				?></div>
			</form>
		</div>
	</div>
</div>