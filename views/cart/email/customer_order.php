<?php if ($donation_cart) { ?>
<p>Thank you for your donation. Below are the details of your donation. You will receive a tax deductable donation receipt at the end of the year.</p>
<?php } else { ?>
<p>Thank you for your order. We have received your order and are currently processing it. Below are the details of your order.</p>
<?php } // if ?>

<?php echo View::factory($cart_view)
	->set($kohana_view_data); ?>

<?php echo View::factory('cart/email/order_info')
	->set($kohana_view_data); ?>