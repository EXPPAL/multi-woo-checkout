jQuery(document).ready( function( $ ) {

    var mwc_first_check_ajax = 1;

    $( window ).load(function() {
        $(document.body).trigger("update_checkout");
    });

    $('.col_package_item').click(function() {
        $('.col_package_item').removeClass('mwc_active_product');
        $(this).addClass('mwc_active_product');

        let title = $(this).find('.product-name .product-title').html();
        let total_price = $(this).find('.prod_prices .total_price').html();
        let discount = $(this).find('.discount_option').val();

        //update price statistical
        $('.statistical td.td-name span').html($(this).find('.opc_title').val());
        $('.statistical td.td-price .price_before').html($(this).find('.price_before .price').html());
        $('.statistical td.td-price .price_now').html($(this).find('.price_now .price').html());
        $('.statistical td.td_total_price').html($(this).find('.price_total .price').html());
        // show statistical form
        $('.statistical').show();

        mwc_update_item_cart_ajax();
        
    });

    // selected auto default option
    $('.mwc_selected_default_opt').click();

})