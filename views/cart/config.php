<script>
var cart_config = {
	prefix : <?php echo json_encode((string) Kohana::$config->load('xm_cart.prefix')); ?>,
	stripe_publishable_key : <?php echo json_encode((string) Kohana::$config->load('xm_cart.payment_processor_config.stripe.' . STRIPE_CONFIG . '.publishable_key')); ?>,
	countries : <?php echo json_encode($countries); ?>
};
</script>