jQuery(document).ready( function( $ ) {

    var mwc_first_check_ajax = 1;

    $( window ).load(function() {
        $(document.body).trigger("update_checkout");

        // click default item
        $('.mwc_active_product .radio_select').click();
    });

    $('.radio_select').change(function() {
        $('.productRadioListItem').removeClass('mwc_active_product');
        var div_parent = $(this).parents('.productRadioListItem');
        div_parent.addClass('mwc_active_product');

        //update price statistical
        $('.statistical td.td-name span').html(div_parent.find('.opc_title').val());
        $('.statistical td.td-price span').html(div_parent.find('.opc_total_price').val());
        $('.statistical .grand-total').html(div_parent.find('.opc_total_price').val());
        $('.statistical .discount-total').html(div_parent.find('.opc_discount').val());

        mwc_update_item_cart_ajax();
        
    });

})