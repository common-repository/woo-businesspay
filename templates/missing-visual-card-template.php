<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$plugin_slug = 'woo-visual-card';
$plugin_name = 'WooCommerce Visual Card';

if ( function_exists( 'get_plugins' ) ) {
	$all_plugins = get_plugins();
	$installed   = ! empty( $all_plugins['woo-visual-card/woocommerce-visual-card.php'] );
}
?>

<div class="error">
    <p>
        <strong><?php esc_html_e( WC_BusinessPay::PLUGIN_NAME, 'woocommerce-businesspay' ); ?></strong> <?php esc_html_e( 'depends on the last version of WooCommerce Visual Card to work!', 'woocommerce-businesspay' ); ?>
    </p>

	<?php if ( $installed && current_user_can( 'install_plugins' ) ) : ?>
        <p>
            <a href="<?php echo esc_url( wp_nonce_url( self_admin_url( 'plugins.php?action=activate&plugin=woo-visual-card/woocommerce-visual-card.php&plugin_status=active' ), 'activate-plugin_woo-visual-card/woocommerce-visual-card.php' ) ); ?>"
               class="button button-primary"><?php esc_html_e( 'Activate WooCommerce Visual Card', 'woocommerce-businesspay' ); ?></a>
        </p>
	<?php else :
		if ( current_user_can( 'install_plugins' ) ) {
			$url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=woo-visual-card' ), 'install-plugin_woo-visual-card' );
		} else {
			$url = 'http://wordpress.org/plugins/woo-visual-card/';
		}
		?>
        <p><a href="<?php echo esc_url( $url ); ?>"
              class="button button-primary"><?php esc_html_e( 'Install WooCommerce Visual Card', 'woocommerce-businesspay' ); ?></a>
        </p>
	<?php endif; ?>
</div>