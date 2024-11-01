<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>

<fieldset id="businesspay_payment_form">
    <p id="businesspay_description"><?php echo $bpConfig->description; ?></p>
    <div class="businesspay-tab">
		<?php
		$btClose = '</button>';
		if ( ( 'yes' == $bpConfig->credit_card ) || ( 'yes' == $bpConfig->debit_card ) ) {
			$btCredit = '<button type="button" id="businesspay_tablink_card" class="businesspay-tablinks">';
			echo $btCredit . esc_html__( 'Credit and Debit Card', 'woocommerce-businesspay' ) . $btClose;
		}

		if ( 'yes' == $bpConfig->transfer ) {
			$btTransfer = '<button type="button" id="businesspay_tablink_transfer" class="businesspay-tablinks">';
			echo $btTransfer . esc_html__( 'Bank Transfer', 'woocommerce-businesspay' ) . $btClose;
		}

		if ( 'yes' == $bpConfig->billet ) {
			$btBillet = '<button type="button" id="businesspay_tablink_billet" class="businesspay-tablinks">';
			echo $btBillet . esc_html__( 'Billet', 'woocommerce-businesspay' ) . $btClose;
		}
		?>
        <input type="hidden" id="businesspay_selected_tab" name="businesspay_selected_tab">
    </div>

	<?php if ( ( 'yes' == $bpConfig->credit_card ) || ( 'yes' == $bpConfig->debit_card ) ) { ?>
        <div id="businesspay_content_credit_card" class="businesspay-tabcontent">
            <div id="businesspay_visual_card"><p style="display: none;"></p></div>
            <p class="form-row form-row-first">
                <label for="businesspay_card_number"><?php esc_html_e( 'Card number', 'woocommerce-businesspay' ); ?>
                    <span
                            class="required">*</span></label>
                <input id="businesspay_card_number" name="businesspay_card_number"
                       class="input-text wc-credit-card-form-card-number" type="text" maxlength="20" autocomplete="off"
                       placeholder="&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull;"
                       style="font-size: 1.5em; padding: 8px;"/>
                <input type="hidden" id="businesspay_card_brand" name="businesspay_card_brand">
            </p>
            <p class="form-row form-row-last">
                <label for="businesspay_holder_name"><?php esc_html_e( 'Name printed on card', 'woocommerce-businesspay' ); ?>
                    <span class="required">*</span></label>
                <input id="businesspay_holder_name" name="businesspay_holder_name" class="input-text" type="text"
                       autocomplete="off" maxlength="25" style="font-size: 1.5em; padding: 8px;"/>
            </p>
            <div class="clear"></div>
            <p class="form-row form-row-first">
                <label for="businesspay_expiry"><?php esc_html_e( 'Expiry date (MM/YYYY)', 'woocommerce-businesspay' ); ?>
                    <span
                            class="required">*</span></label>
                <input id="businesspay_expiry" name="businesspay_expiry"
                       class="input-text wc-credit-card-form-card-expiry" type="text" autocomplete="off"
                       placeholder="<?php esc_html_e( 'MM / YYYY', 'woocommerce-businesspay' ); ?>"
                       style="font-size: 1.5em; padding: 8px;" "/>
            </p>
            <p class="form-row form-row-last">
                <label for="businesspay_cvv"><?php esc_html_e( 'Security code', 'woocommerce-businesspay' ); ?> <span
                            class="required">*</span></label>
                <input id="businesspay_cvv" name="businesspay_cvv" class="input-text wc-credit-card-form-card-cvc"
                       type="text" autocomplete="off"
                       placeholder="<?php esc_html_e( 'CVC', 'woocommerce-businesspay' ); ?>"
                       style="font-size: 1.5em; padding: 8px;"/>
            </p>
            <div class="clear"></div>
            <p class="form-row form-row-first">
                <label for="businesspay_installments"><?php esc_html_e( 'Installments Quantity', 'woocommerce-businesspay' ); ?>
                    <span class="required">*</span></label>
                <select id="businesspay_installments" name="businesspay_installments" class="businesspay-installments"
                        placeholder="<?php esc_html_e( 'Select', 'woocommerce-businesspay' ); ?>">
					<?php
					echo '<option value="0">' . esc_html__( 'Select', 'woocommerce-businesspay' ) . '</option>';

					$bpOrderTotal          = $bpConfig->get_cart_total();
					$bpInstallments        = $bpConfig->get_option( 'installments' );
					$bpInstallmentsMin     = $bpConfig->get_option( 'installments_minimum' );
					$bpInstallmentsMax     = $bpConfig->get_option( 'installments_maximum' );
					$bpInstallmentMinValue = $bpConfig->get_option( 'installment_minimum_value' );
					$bpDebitCard           = $bpConfig->get_option( 'debit_card' );
					$bpWooCurrency         = $bpConfig->get_currency_symbol();

					if ( 'yes' === $bpDebitCard ) {
						echo '<option value="debit">' . esc_html__( 'At sight via debit', 'woocommerce-businesspay' ) . '</option>';
					}

					if ( 'yes' === $bpInstallments ) {
						for ( $i = (int) $bpInstallmentsMin; $i <= (int) $bpInstallmentsMax; $i ++ ) {
							$bpCurrentInstallmentValue = $bpOrderTotal / $i;
							if ( $bpCurrentInstallmentValue >= $bpInstallmentMinValue ) {
								$bpTempValue         = number_format( $bpCurrentInstallmentValue, 2, ',', '.' );
								$bpInstallmentString = esc_html__( 'In ', 'woocommerce-businesspay' ) . $i . esc_html__( 'x of ', 'woocommerce-businesspay' ) . $bpWooCurrency . $bpTempValue . esc_html__( ' on credit card', 'woocommerce-businesspay' );
								echo '<option value="' . $i . '">' . $bpInstallmentString . '</option>';
							}
						}
					}
					?>
                </select>
            </p>
            <p class="form-row form-row-last">
                <label for="businesspay_doc"><?php esc_html_e( 'Card holder document number', 'woocommerce-businesspay' ); ?>
                    <span class="required">*</span></label>
                <input id="businesspay_doc" name="businesspay_doc" class="input-text" type="text" autocomplete="off"
                       placeholder="<?php esc_html_e( '', 'woocommerce-businesspay' ); ?>"
                       style="font-size: 1.5em; padding: 8px;"/>
            </p>
            <div class="clear"></div>
        </div>
	<?php } ?>

	<?php if ( 'yes' == $bpConfig->transfer ) { ?>
        <div id="businesspay_content_transfer" class="businesspay-tabcontent">
            <p class="businesspay-transfer-p"><?php esc_html_e( 'Select your bank:', 'woocommerce-businesspay' ); ?></p>
            <ul>
                <li id="businesspay-transfer-itau-li"><label><input type="radio" id="businesspay_transfer_itau_radio"
                                                                    name="businesspay_transfer_bank" value="Itau"/><i
                                id="businesspay-icon-itau"></i><span><?php esc_html_e( 'Itaú', 'woocommerce-businesspay' ); ?></span></label>
                </li>
                <li id="businesspay-transfer-bradesco-li"><label><input type="radio"
                                                                        id="businesspay_transfer_bradesco_radio"
                                                                        name="businesspay_transfer_bank"
                                                                        value="Bradesco"/><i
                                id="businesspay-icon-bradesco"></i><span><?php esc_html_e( 'Bradesco', 'woocommerce-businesspay' ); ?></span></label>
                </li>
            </ul>
            <div class="clear"></div>
            <p class="form-row">
            <p class="businesspay-transfer-p"><?php esc_html_e( '* Você será redirecionado para efetuar o pagamento diretamente no seu internet banking.', 'woocommerce-businesspay' ); ?></p>
            </p>
            <div class="clear"></div>
        </div>
	<?php } ?>

	<?php if ( 'yes' == $bpConfig->billet ) { ?>
        <div id="businesspay_content_billet" class="businesspay-tabcontent">
            <p class="businesspay-billet-p"><?php esc_html_e( 'Select the bank to generate the billet:', 'woocommerce-businesspay' ); ?></p>
            <ul>
                <li id="businesspay-billet-itau-li"><label><input type="radio" id="businesspay_billet_itau_radio"
                                                                  name="businesspay_billet_bank" value="Itau"/><i
                                id="businesspay-icon-itau"></i><span><?php esc_html_e( 'Itaú', 'woocommerce-businesspay' ); ?></span></label>
                </li>
                <li id="businesspay-billet-bradesco-li"><label><input type="radio"
                                                                      id="businesspay_billet_bradesco_radio"
                                                                      name="businesspay_billet_bank"
                                                                      value="Bradesco"/><i
                                id="businesspay-icon-bradesco"></i><span><?php esc_html_e( 'Bradesco', 'woocommerce-businesspay' ); ?></span></label>
                </li>
            </ul>
            <div class="clear"></div>
            <div class="form-row">
                <p class="businesspay-billet-p"><?php esc_html_e( '* The order will be confirmed only after the payment approval.', 'woocommerce-businesspay' ); ?></p>
            </div>
            <div class="clear"></div>
        </div>
	<?php } ?>

    <p class="businesspay-pay-locale">
		<?php
		if ( is_ssl() ) {
			$secure_url   = plugins_url( 'assets/img/businesspay-icone-cadeado.png', plugin_dir_path( __FILE__ ) );
			$secure_title = esc_html__( 'This is a secure environment.', 'woocommerce-businesspay' );
			$secure_class = 'businesspay-secure-icon';
			$secure_str   = '<img src="' . $secure_url . '" title="' . $secure_title . '" class="' . $secure_class . '">';
			echo $secure_str;
		}

		if ( $bpConfig->enable_antifraud ) {
			$antifraud_url   = plugins_url( 'assets/img/businesspay-icone-clear-sale.png', plugin_dir_path( __FILE__ ) );
			$antifraud_title = esc_html__( 'Antifraud by ClearSale.', 'woocommerce-businesspay' );
			$antifraud_class = 'businesspay-antifraud-icon';
			$antifraud_str   = '<img src="' . $antifraud_url . '" title="' . $antifraud_title . '" class="' . $antifraud_class . '">';
			echo $antifraud_str;
		}
		?>
		<?php esc_html_e( 'This purchase is being made in ', 'woocommerce-businesspay' ); ?>
        <img
                src="<?php echo plugins_url( 'assets/img/brazil.png', plugin_dir_path( __FILE__ ) ); ?>"
                alt="<?php esc_html_e( 'Brazil', 'woocommerce-businesspay' ); ?>"
                title="<?php esc_html_e( 'This purchase is being made in Brazil', 'woocommerce-businesspay' ); ?>"
                class="businesspay-brazil-flag"> <?php esc_html_e( 'Brazil', 'woocommerce-businesspay' ); ?>
    </p>
</fieldset>

<?php
if ( is_checkout() ) {
	wp_enqueue_script( 'woocommerce-businesspay-gateway-js', plugins_url( 'assets/js/frontend.js', plugin_dir_path( __FILE__ ) ), array( 'jquery' ), WC_BusinessPay::VERSION, true );
}

if (class_exists( 'WC_VisualCard' )){
    $disable_default_card_icon = '<style>#payment .payment_methods li .payment_box .wc-credit-card-form-card-number{background-image: none !important;}</style>';
    echo $disable_default_card_icon;
}

?>