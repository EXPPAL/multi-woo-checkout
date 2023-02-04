jQuery(document).ready( function( $ ) {

    var mwc_first_check_ajax = 1;

    $( window ).load(function() {
        $(document.body).trigger("update_checkout");
    });

    $('.mwc_package_item').click(function() {
        $('.mwc_package_item').removeClass('mwc_active_product');
        $(this).addClass('mwc_active_product');

        let title = $(this).find('.product-name .product-title').html();
        let total_price = $(this).find('.prod_prices .discounted_price .price').html();
        let discount = $(this).find('.discount_option').val();

        //update price statistical
        $('.statistical td.td-name span').html(title);
        $('.statistical td.td-price span').html(total_price);
        $('.statistical .grand-total').html(total_price);
        $('.statistical .discount-total').html(discount);

        mwc_update_item_cart_ajax();
        
    });

    // selected auto default option
    $('.mwc_selected_default_opt').click();

})