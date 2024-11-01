<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $woocommerce, $post;

if ( isset( $bpConfig ) ) {

	$order = wc_get_order( $post->ID );
	$oid   = $bpConfig->get_order_id( $order );
	if ( $oid > 0 ) {
		$af_transaction_id = get_post_meta( $oid, '_businesspay-antifraud-transaction-id', true );
		if ( strlen( $af_transaction_id ) > 0 ) {
			$af_id = get_post_meta( $oid, '_businesspay-antifraud-transaction-id', true );

			$af_transaction_date = str_replace( 'T', ' ', get_post_meta( $oid, '_businesspay-antifraud-transaction-date', true ) );
			$af_transaction_date = date_create_from_format( $bpConfig->get_date_format(), $af_transaction_date );
			$af_transaction_date = $af_transaction_date->format( 'd/m/Y ' . esc_html__( '\a\t', 'woocommerce-businesspay' ) . ' H:i:s' );

			$af_transaction_status = get_post_meta( $oid, '_businesspay-antifraud-status-code', true );
			$af_score              = get_post_meta( $oid, '_businesspay-antifraud-score', true );
			if ( empty( $af_score ) ) {
				$af_score = __( 'Not applicable.', 'woocommerce-businesspay' );
			}
			$af_message = get_post_meta( $oid, '_businesspay-antifraud-message', true );

			?>
            <div class="clear"></div>
            <h3><?php esc_html_e( 'Antifraud', 'woosommerce-businesspay' ); ?></h3>
            <div class="businesspay-admin-order-payment">
                <img src='<?php echo plugins_url( 'assets/img/businesspay-logotipo-clearsale.png', dirname( __FILE__ ) ); ?>'
                     style='width: 108px;'>
                <p>
                    <strong><?php esc_html_e( 'Analysis Information', 'woocommerce-businesspay' ); ?></strong><br/>
                    <strong><?php esc_html_e( 'ID: ', 'woocommerce-businesspay' ); ?></strong><?php echo $af_transaction_id; ?>
                    <br/>
                    <strong><?php esc_html_e( 'Date: ', 'woocommerce-businesspay' ); ?></strong><?php echo $af_transaction_date; ?>
                    <br/>
                    <strong><?php esc_html_e( 'Status: ', 'woocommerce-businesspay' ); ?></strong><?php echo $af_transaction_status; ?>
                    <br/>
                    <strong><?php esc_html_e( 'Score: ', 'woocommerce-businesspay' ); ?></strong><?php echo $af_score; ?>
                    <br/>
                    <strong><?php esc_html_e( 'Message: ', 'woocommerce-businesspay' ); ?></strong><?php echo $af_message; ?>
                    <br/>
                </p>
            </div>
			<?php
		}
	}
}
?>
