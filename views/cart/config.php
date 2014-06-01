<script>
var cart_config = {
	route_prefix : <?php echo json_encode((string) Cart_Config::load('route_prefix')); ?>,
	continue_shopping_url : <?php echo json_encode(Cart_Config::continue_shopping_url()); ?>,
	cart_view_url : <?php echo json_encode(Cart_Config::cart_view_url()); ?>,
	stripe_publishable_key : <?php echo json_encode((string) Cart_Config::load('payment_processor_config.stripe.' . STRIPE_CONFIG . '.publishable_key')); ?>,
	countries : <?php echo json_encode($countries); ?>,
	enable_shipping : <?php echo json_encode(Cart_Config::enable_shipping()); ?>,
	enable_tax : <?php echo json_encode(Cart_Config::enable_tax()); ?>,
	show_shipping_country : <?php echo json_encode((bool) Cart_Config::load('show_shipping_country')); ?>,
	default_country_id : <?php echo json_encode((int) Cart_Config::load('default_country_id')); ?>,
	checkout_https : <?php echo json_encode(Cart_Config::load('checkout_https')); ?>
	show_product_photo : <?php echo json_encode((bool) Cart_Config::load('show_product_photo')); ?>
};
</script>