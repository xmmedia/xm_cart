<script>
var cart_config = {
	prefix : <?php echo json_encode((string) Kohana::$config->load('xm_cart.prefix')); ?>,
	stripe_publishable_key : <?php echo json_encode((string) Kohana::$config->load('xm_cart.payment_processors.stripe.' . STRIPE_CONFIG . '.publishable_key')); ?>
};
</script>