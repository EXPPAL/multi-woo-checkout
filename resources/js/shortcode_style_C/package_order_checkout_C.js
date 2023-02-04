jQuery(document).ready( function( $ ) {

    var mwc_first_check_ajax = 1;

    $( window ).load(function() {
        // append form statistic order option onepage checkout to custom checkout form woo
        $(".op_custom_checkout_form .col .col-inner").first().before($("#clone_statistic_option_form"));
        $("#clone_statistic_option_form").show();
        // $(document.body).trigger("update_checkout");
    });

    $('.col_package_item').click(function() {
        $('.col_package_item').removeClass('mwc_active_product');
        $(this).addClass('mwc_active_product');

        $('.w_radio input[type="checkbox"]').prop('checked', false);
        $(this).find('.w_radio input[type="checkbox"]').prop('checked', true);

        $('.checkout_form_woo').slideDown("slow");

        //update price statistical
        $('.statistical td.td-name span').html($(this).find('.opc_title').val());
        $('.statistical td.td-price span').html($(this).find('.opc_total_price').val());
        $('.statistical .grand-total').html($(this).find('.opc_total_price').val());
        $('.statistical .discount-total').html($(this).find('.opc_discount').val());

        mwc_update_item_cart_ajax();
    });
})