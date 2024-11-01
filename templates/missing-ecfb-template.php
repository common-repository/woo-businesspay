<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$plugin_slug = 'woocommerce-extra-checkout-fields-for-brazil';
$plugin_name = 'Extra Checkout Fields for Brazil';

if ( function_exists( 'get_plugins' ) ) {
	$all_plugins = get_plugins();
	$installed   = ! empty( $all_plugins['woocommerce-extra-checkout-fields-for-brazil/woocommerce-extra-checkout-fields-for-brazil.php'] );
}
?>

<div class="error">
    <p>
        <strong><?php esc_html_e( WC_BusinessPay::PLUGIN_NAME, 'woocommerce-businesspay' ); ?></strong> <?php esc_html_e( 'depends on the last version of WooCommerce Extra Checkout Fields for Brazil to work!', 'woocommerce-businesspay' ); ?>
    </p>

	<?php if ( $installed && current_user_can( 'install_plugins' ) ) : ?>
        <p>
            <a href="<?php echo esc_url( wp_nonce_url( self_admin_url( 'plugins.php?action=activate&plugin=woocommerce-extra-checkout-fields-for-brazil/woocommerce-extra-checkout-fields-for-brazil.php&plugin_status=active' ), 'activate-plugin_woocommerce-extra-checkout-fields-for-brazil/woocommerce-extra-checkout-fields-for-brazil.php' ) ); ?>"
               class="button button-primary"><?php esc_html_e( 'Activate WooCommerce Extra Checkout Fields for Brazil', 'woocommerce-businesspay' ); ?></a>
        </p>
	<?php else :
		if ( current_user_can( 'install_plugins' ) ) {
			$url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=woocommerce-extra-checkout-fields-for-brazil' ), 'install-plugin_woocommerce-extra-checkout-fields-for-brazil' );
		} else {
			$url = 'http://wordpress.org/plugins/woocommerce-extra-checkout-fields-for-brazil/';
		}
		?>
        <p><a href="<?php echo esc_url( $url ); ?>"
              class="button button-primary"><?php esc_html_e( 'Install WooCommerce Extra Checkout Fields for Brazil', 'woocommerce-businesspay' ); ?></a>
        </p>
	<?php endif; ?>
</div>