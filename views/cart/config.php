<script>
var cart_config = {
	route_prefix : <?php echo json_encode((string) Kohana::$config->load('xm_cart.route_prefix')); ?>,
	stripe_publishable_key : <?php echo json_encode((string) Kohana::$config->load('xm_cart.payment_processor_config.stripe.' . STRIPE_CONFIG . '.publishable_key')); ?>,
	countries : <?php echo json_encode($countries); ?>,
	enable_shipping : <?php echo json_encode((bool) Kohana::$config->load('xm_cart.enable_shipping')); ?>,
	enable_tax : <?php echo json_encode((bool) Kohana::$config->load('xm_cart.enable_tax')); ?>
};
</script>