<script>
var cart_config = {
	prefix : <?php echo json_encode($cart_prefix); ?>,
	stripe_publishable_key : <?php echo json_encode(STRIPE_PUBLISHABLE_KEY); ?>
};
</script>