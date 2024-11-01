(function ( $ ) {
    'use strict';

    $(function () {
        var bpEnable = $( '#woocommerce_businesspay_gateway_enabled' ),
            bpCreditCard = $( '#woocommerce_businesspay_gateway_credit_card' ),
            bpInstallments = $( '#woocommerce_businesspay_gateway_installments' ),
            bpBillet = $( '#woocommerce_businesspay_gateway_billet' );

        // BusinessPay plugin Display.
        function businessPayDisplay() {
            var bpCreditCardFields = $( '.form-table:eq(0) tr:eq(1), .form-table:eq(0) tr:eq(2)' );
            if ( bpEnable.is( ':checked' ) ) {
                bpCreditCardFields.show();
            } else {
                bpCreditCardFields.hide();
            }
        }
        businessPayDisplay();

        bpEnable.on( 'change', function () {
            businessPayDisplay();
            gatewaySettingsDisplay();
            creditCardDisplay();
            billetDisplay();
        });

        // Gateway Settings Display.
        function gatewaySettingsDisplay() {
            var bpGatewayFields = $( '.form-table:eq(1) tr, .form-table:eq(2) tr, .form-table:eq(3) tr, .form-table:eq(4) tr, .form-table:eq(5) tr, #woocommerce_businesspay_gateway_credit_card_settings, #woocommerce_businesspay_gateway_gateway_settings, #woocommerce_businesspay_gateway_testing, #woocommerce_businesspay_gateway_tools' );
            if ( bpEnable.is( ':checked' ) ) {
                bpGatewayFields.show();
            } else {
                bpGatewayFields.hide();
            }
        }
        gatewaySettingsDisplay();

        // Credit Card Display.
        function creditCardDisplay() {
            var bpCreditCardFields = $( '.form-table:eq(2) tr, #woocommerce_businesspay_gateway_credit_card_settings' );
            if ( bpEnable.is( ':checked' ) && bpCreditCard.is( ':checked' ) ) {
                bpCreditCardFields.show();
            } else {
                bpCreditCardFields.hide();
            }
            installmentsDisplay();
        }
        creditCardDisplay();

        bpCreditCard.on( 'change', function () {
            creditCardDisplay();
        });

        // Installments Display.
        function installmentsDisplay() {
            var bpInstallmentsFields = $( '.form-table:eq(3) tr:eq(0), .form-table:eq(3) tr:eq(1), .form-table:eq(3) tr:eq(2), #woocommerce_businesspay_gateway_installments_settings' );
            if ( bpEnable.is( ':checked' ) && bpCreditCard.is( ':checked' ) && bpInstallments.is( ':checked' ) ) {
                bpInstallmentsFields.show();
            } else {
                bpInstallmentsFields.hide();
            }
        }
        installmentsDisplay();

        bpInstallments.on( 'change', function () {
            installmentsDisplay();
        });

        // Billet Display.
        function billetDisplay() {
            var bpBilletFields = $( '.form-table:eq(4), #woocommerce_businesspay_gateway_billet_settings' );
            if ( bpEnable.is( ':checked' ) &&  bpBillet.is( ':checked' ) ) {
                bpBilletFields.show();
            } else {
                bpBilletFields.hide();
            }
        }
        billetDisplay();

        bpBillet.on( 'change', function () {
            billetDisplay();
        });
    });

}(jQuery));