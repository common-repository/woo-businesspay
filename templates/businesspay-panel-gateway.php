<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $woocommerce, $post;

if ( isset( $bpConfig ) ) {
	$order = wc_get_order( $post->ID );
	$oid   = $bpConfig->get_order_id( $order );
	if ( $oid > 0 ) {
		$bp_transaction_id = get_post_meta( $oid, '_businesspay-transaction-id', true );
		if ( strlen( $bp_transaction_id ) > 0 ) {
			$bp_woo_currency = get_woocommerce_currency_symbol();
			$bp_transaction_date = str_replace( 'T', ' ', get_post_meta( $oid, '_businesspay-transaction-date', true ) );
			$bp_transaction_date = date_create_from_format( $bpConfig->get_date_format(), $bp_transaction_date );
			$bp_transaction_date = $bp_transaction_date->format( 'd/m/Y ' . esc_html__( '\a\t', 'woocommerce-businesspay' ) . ' H:i:s' );

			$bp_transaction_status = get_post_meta( $oid, '_businesspay-transaction-status', true ) . ' - ' . get_post_meta( $oid, '_businesspay-transaction-status-message', true );

			$bp_transaction_type = get_post_meta( $oid, '_businesspay-transaction-type', true );
			switch ( $bp_transaction_type ) {
				case 'debit-card':
					$bp_transaction_desc = esc_html__( 'Debit Card', 'woocommerce-businesspay' );
					$bp_card_brand       = get_post_meta( $oid, '_businesspay-card-brand', true );
					$bp_installments     = esc_html__( 'At sight via debit', 'woocommerce-businesspay' );
					break;
				case 'credit-card':
					$bp_transaction_desc = esc_html__( 'Credit Card', 'woocommerce-businesspay' );
					$bp_card_brand       = get_post_meta( $oid, '_businesspay-card-brand', true );
					$bp_card_brand       = ( strtolower( $bp_card_brand ) == 'unknown' ) ? esc_html__( 'Unknown Brand', 'woocommerce-businesspay' ) : $bp_card_brand;

					$bp_installments              = get_post_meta( $oid, '_businesspay-card-installments', true );
					$bp_order_value               = $bpConfig->get_total_order( $order );
					$bp_current_installment_value = $bp_order_value / $bp_installments;
					$bp_current_installment_value = number_format( $bp_current_installment_value, 2, ',', '.' );
					$bp_installments              = $bp_installments . esc_html__( 'x of ', 'woocommerce-businesspay' ) . $bp_woo_currency . $bp_current_installment_value;

					break;
				case 'bank-transfer':
					$bp_transaction_desc   = esc_html__( 'Bank Transference', 'woocommerce-businesspay' );
					$bp_provider           = get_post_meta( $oid, '_businesspay-electronic-transfer-provider', true );
					$bp_provider_reference = get_post_meta( $oid, '_businesspay-electronic-transfer-provider-reference', true );

					$bp_payment_date = get_post_meta( $oid, '_businesspay-electronic-transfer-payment-date', true );
					if ( $bp_payment_date == '' ) {
						$bp_payment_date = esc_html__( 'Unavailable', 'woocommerce-businesspay' );
					} else {
						$bp_payment_date = date_create_from_format( 'Y-m-d', $bp_payment_date );
						$bp_payment_date = $bp_payment_date->format( 'd/m/Y' );
					}

					$bp_payment_amount = get_post_meta( $oid, '_businesspay-electronic-transfer-payment-amount', true );
					if ( $bp_payment_amount == '' ) {
						$bp_payment_amount = esc_html__( 'Unavailable', 'woocommerce-businesspay' );
					} else {
						$bp_payment_cents  = substr( $bp_payment_amount, '-2' );
						$bp_payment_amount = $bp_woo_currency . substr( $bp_payment_amount, 0, strlen( $bp_payment_amount ) - 2 ) . ',' . $bp_payment_cents;
					}

					break;
				case 'billet':
					$bp_transaction_desc   = esc_html__( 'Billet', 'woocommerce-businesspay' );
					$bp_provider           = get_post_meta( $oid, '_businesspay-bankslip-provider', true );
					$bp_provider_reference = get_post_meta( $oid, '_businesspay-bankslip-provider-reference', true );

					$bpesc_html_emission_date = get_post_meta( $oid, '_businesspay-bankslip-emission-date', true );
					$bpesc_html_emission_date = date_create_from_format( 'Y-m-d', $bpesc_html_emission_date );
					$bpesc_html_emission_date = $bpesc_html_emission_date->format( 'd/m/Y' );

					$bpesc_html_expiration_date = get_post_meta( $oid, '_businesspay-bankslip-expiration-date', true );
					$bpesc_html_expiration_date = date_create_from_format( 'Y-m-d', $bpesc_html_expiration_date );
					$bpesc_html_expiration_date = $bpesc_html_expiration_date->format( 'd/m/Y' );

					$bp_payment_date = get_post_meta( $oid, '_businesspay-bankslip-payment-date', true );
					if ( $bp_payment_date == '' ) {
						$bp_payment_date = esc_html__( 'Unavailable', 'woocommerce-businesspay' );
					} else {
						$bp_payment_date = date_create_from_format( 'Y-m-d', $bp_payment_date );
						$bp_payment_date = $bp_payment_date->format( 'd/m/Y' );
					}

					$bp_payment_amount = get_post_meta( $oid, '_businesspay-bankslip-payment-amount', true );
					if ( $bp_payment_amount == '' ) {
						$bp_payment_amount = esc_html__( 'Unavailable', 'woocommerce-businesspay' );
					} else {
						$bp_payment_cents  = substr( $bp_payment_amount, '-2' );
						$bp_payment_amount = $bp_woo_currency . substr( $bp_payment_amount, 0, strlen( $bp_payment_amount ) - 2 ) . ',' . $bp_payment_cents;
					}
					break;
				default:
					$bp_transaction_desc = esc_html__( 'Unknown', 'woocommerce-businesspay' );
					break;
			}
			?>
            <div class="clear"></div>
            <h3><?php esc_html_e( 'Payment', 'woosommerce-businesspay' ); ?></h3>
            <div class="businesspay-admin-order-payment">
                <img src='<?php echo plugins_url( 'assets/img/BusinessPay-Logo-Positivo.png', dirname( __FILE__ ) ); ?>'>
                <p>
                    <strong><?php esc_html_e( 'Transaction Information', 'woocommerce-businesspay' ); ?></strong><br/>
                    <strong><?php esc_html_e( 'ID: ', 'woocommerce-businesspay' ); ?></strong><?php echo $bp_transaction_id; ?>
                    <br/>
                    <strong><?php esc_html_e( 'Date: ', 'woocommerce-businesspay' ); ?></strong><?php echo $bp_transaction_date; ?>
                    <br/>
                    <strong><?php esc_html_e( 'Status: ', 'woocommerce-businesspay' ); ?></strong><?php echo $bp_transaction_status; ?>
                    <br/><br/>
                    <strong><?php esc_html_e( 'Payment Information', 'woocommerce-businesspay' ); ?></strong><br/>
                    <strong><?php esc_html_e( 'Type: ', 'woocommerce-businesspay' ); ?></strong><?php echo $bp_transaction_desc; ?>
                    <br/>
					<?php if ( $bp_transaction_type == 'debit-card' || $bp_transaction_type == 'credit-card' ) { ?>
                        <strong><?php esc_html_e( 'Brand: ', 'woocommerce-businesspay' ); ?></strong><?php echo $bp_card_brand; ?>
                        <br/>
                        <strong><?php esc_html_e( 'Installments: ', 'woocommerce-businesspay' ); ?></strong><?php echo $bp_installments; ?>
                        <br/>
					<?php } else if ( $bp_transaction_type == 'bank-transfer' ) { ?>
                        <strong><?php esc_html_e( 'Origin Bank: ', 'woocommerce-businesspay' ); ?></strong><?php echo $bp_provider; ?>
                        <br/>
                        <strong><?php esc_html_e( 'Reference Code: ', 'woocommerce-businesspay' ); ?></strong><?php echo $bp_provider_reference; ?>
                        <br/>
                        <strong><?php esc_html_e( 'Payment Date: ', 'woocommerce-businesspay' ); ?></strong><?php echo $bp_payment_date; ?>
                        <br/>
                        <strong><?php esc_html_e( 'Payment Amount: ', 'woocommerce-businesspay' ); ?></strong><?php echo $bp_payment_amount; ?>
					<?php } else if ( $bp_transaction_type == 'billet' ) { ?>
                        <strong><?php esc_html_e( 'Provider Bank: ', 'woocommerce-businesspay' ); ?></strong><?php echo $bp_provider; ?>
                        <br/>
                        <strong><?php esc_html_e( 'Provider Reference: ', 'woocommerce-businesspay' ); ?></strong><?php echo $bp_provider_reference; ?>
                        <br/>
                        <strong><?php esc_html_e( 'Emission Date: ', 'woocommerce-businesspay' ); ?></strong><?php echo $bpesc_html_emission_date; ?>
                        <br/>
                        <strong><?php esc_html_e( 'Expiration Date: ', 'woocommerce-businesspay' ); ?></strong><?php echo $bpesc_html_expiration_date; ?>
                        <br/>
                        <strong><?php esc_html_e( 'Payment Date: ', 'woocommerce-businesspay' ); ?></strong><?php echo $bp_payment_date; ?>
                        <br/>
                        <strong><?php esc_html_e( 'Payment Amount: ', 'woocommerce-businesspay' ); ?></strong><?php echo $bp_payment_amount; ?>
                        <br/>
					<?php } ?>
                </p>
            </div>
			<?php
		}
	}
}
?>
