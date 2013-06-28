<div class="grid cart_admin_nav">
	<nav class="col basic_nav">
		<ul>
			<li><?php echo HTML::anchor(Route::get('cart_admin')->uri(), 'Main'); ?></li>
			<li><?php echo HTML::anchor(Route::get('cart_admin')->uri(array('action' => 'shipping')), 'Shipping Rates'); ?></li>
		</ul>
	</nav>
</div>