<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="error">
    <p>
        <strong><?php _e( WC_BusinessPay::PLUGIN_NAME, 'woocommerce-businesspay' ); ?></strong> <?php echo sprintf( esc_html__( 'depends on the PHP %s minimum version to work!', 'woocommerce-businesspay' ), WC_BusinessPay::VERSION_MIN_PHP ); ?>
    </p>
</div>