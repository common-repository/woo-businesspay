<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="error">
    <p>
        <strong><?php esc_html_e( WC_BusinessPay::PLUGIN_NAME, 'woocommerce-businesspay' ); ?></strong> <?php echo sprintf( esc_html__( 'depends on the WooCommerce %s minimum version to work!', 'woocommerce-businesspay' ), WC_BusinessPay::VERSION_MIN_WOO ); ?>
    </p>
</div>