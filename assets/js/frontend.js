(function($) {
    jQuery(document).on('click', '#businesspay_tablink_card', function(e){
        e.preventDefault();
        showTabCard();
    });
    
    jQuery(document).on('click', '#businesspay_tablink_transfer', function(e){
        e.preventDefault();
        showTabTransfer();
    });

    jQuery(document).on('click', '#businesspay_tablink_billet', function(e){
        e.preventDefault();
        showTabBillet();
    });

    jQuery(document).on('click', '#businesspay_transfer_itau_radio', function(){
        jQuery("#businesspay-transfer-itau-li").addClass('active');
        jQuery("#businesspay-transfer-bradesco-li").removeClass('active');
    });

    jQuery(document).on('click', '#businesspay_transfer_bradesco_radio', function(){
        jQuery("#businesspay-transfer-itau-li").removeClass('active');
        jQuery("#businesspay-transfer-bradesco-li").addClass('active');
    });

    jQuery(document).on('click', '#businesspay_billet_itau_radio', function(){
        jQuery("#businesspay-billet-itau-li").addClass('active');
        jQuery("#businesspay-billet-bradesco-li").removeClass('active');
    });

    jQuery(document).on('click', '#businesspay_billet_bradesco_radio', function(){
        jQuery("#businesspay-billet-itau-li").removeClass('active');
        jQuery("#businesspay-billet-bradesco-li").addClass('active');
    });

    jQuery(document).on('keyup', '#businesspay_card_number', function(){
        var cardNum = jQuery("#businesspay_card_number");
        if (cardNum.hasClass('elo')){
            jQuery("#businesspay_card_brand").val('elo');
        }
        else if (cardNum.hasClass('visa')){
            jQuery("#businesspay_card_brand").val('visa');
        }
        else if (cardNum.hasClass('mastercard')){
            jQuery("#businesspay_card_brand").val('mastercard');
        }
        else if (cardNum.hasClass('amex')){
            jQuery("#businesspay_card_brand").val('amex');
        }
        else if (cardNum.hasClass('dinersclub')){
            jQuery("#businesspay_card_brand").val('dinersclub');
        }
        else if (cardNum.hasClass('discover')){
            jQuery("#businesspay_card_brand").val('discover');
        }
        else{
            jQuery("#businesspay_card_brand").val('unknown');
        }
    });

    jQuery(document).on('keyup', '#businesspay_holder_name', function(){
            jQuery("#businesspay_holder_name").val(jQuery("#businesspay_holder_name").val().toUpperCase());
    });

    jQuery(document).on('keydown', '#businesspay_doc', function (e) {

        var digit = e.key.replace(/\D/g, '');

        var value = jQuery(this).val().replace(/\D/g, '');

        var size = value.concat(digit).length;

        jQuery(this).mask((size <= 11) ? '000.000.000-00' : '00.000.000/0000-00');
    });

    jQuery(document).on('keyup', '#businesspay_doc', function (e) {

        var digit = e.key.replace(/\D/g, '');

        var value = jQuery(this).val().replace(/\D/g, '');

        var size = value.concat(digit).length -1;

        jQuery(this).mask((size <= 11) ? '000.000.000-00' : '00.000.000/0000-00');
    });

    function showTabCard(){
        jQuery("#businesspay_content_credit_card").show();
        jQuery("#businesspay_content_transfer").hide();
        jQuery("#businesspay_content_billet").hide();

        jQuery("#businesspay_tablink_card").addClass('active');
        jQuery("#businesspay_tablink_transfer").removeClass('active');
        jQuery("#businesspay_tablink_billet").removeClass('active');

        jQuery("#businesspay_selected_tab").val('card');
    }

    function showTabTransfer(){
        jQuery("#businesspay_content_credit_card").hide();
        jQuery("#businesspay_content_transfer").show();
        jQuery("#businesspay_content_billet").hide();

        jQuery("#businesspay_tablink_card").removeClass('active');
        jQuery("#businesspay_tablink_transfer").addClass('active');
        jQuery("#businesspay_tablink_billet").removeClass('active');

        jQuery("#businesspay_selected_tab").val('transfer');
    }

    function showTabBillet() {
        jQuery("#businesspay_content_credit_card").hide();
        jQuery("#businesspay_content_transfer").hide();
        jQuery("#businesspay_content_billet").show();

        jQuery("#businesspay_tablink_card").removeClass('active');
        jQuery("#businesspay_tablink_transfer").removeClass('active');
        jQuery("#businesspay_tablink_billet").addClass('active');

        jQuery("#businesspay_selected_tab").val('billet');
    }

    function startShow(){
        if (jQuery("#businesspay_content_credit_card").length){
            showTabCard();
        }
        else if (jQuery("#businesspay_content_transfer").length){
            showTabTransfer();
        }
        else if (jQuery("#businesspay_content_billet").length){
            showTabBillet();
        }
    }

    jQuery(document).on('updated_checkout', function(){
        startShow();
    });

    jQuery(document).ready(function() {
        startShow();
    });

})( jQuery.noConflict() );