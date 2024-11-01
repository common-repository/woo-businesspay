<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="error">
    <p>
        <strong><?php esc_html_e( WC_BusinessPay::PLUGIN_NAME, 'woocommerce-businesspay' ); ?></strong> <?php esc_html_e( 'depends on an active security certificate to work!', 'woocommerce-businesspay' ); ?>
    </p>
</div>