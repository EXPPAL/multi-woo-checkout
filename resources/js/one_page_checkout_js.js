jQuery(document).ready( function( $ ) {
    
    //override input customer form
    $('.op_c_checkout_form').find('.large-7').removeClass('large-7').addClass('customer_info');
    $('.op_c_checkout_form').find('.large-5').removeClass('large-5').addClass('payment_opt');
    // $('.op_c_checkout_form').find('.pt-0').removeClass('row');
    // $('.op_c_checkout_form').find('.customer_info').find('h3').html($('#step_2_of_3').val());
    $('.op_c_checkout_form').find('.customer_info #billing_city_field').removeClass('form-row-wide').addClass('form-row-last');
    // $('.op_c_checkout_form').find('.payment_opt').find('h3').html($('#step_3_of_3').val());

    // $('.op_c_checkout_form').find('#customer_details .form-row').each(function(index, el) {

    //     if($(el).find('input').attr('placeholder') == "") {
    //         let _label = $(el).find('label').first().text();
    //         $(el).find('input').attr('placeholder', (_label.split("*", 1)));
    //     }
        
    //     let label_input = ( $(this).find('select').attr('name') != undefined ) ? ( $(el).find('label').html() ) : ( ($(el).find('input').attr('placeholder') != "") ? $(el).find('input').attr('placeholder') : $(el).find('label').html() );
    //     $(el).find('.woocommerce-input-wrapper').prepend("<label class='fl-label'>" +label_input+ "</label>");
        
    //     $(el).find('label').first().hide();
    //     $(el).find('.woocommerce-input-wrapper').attr('class', 'fl-wrap fl-wrap-input');

    //     if( $(this).find('input').val() != "" ) {
    //         $(this).find('.fl-wrap').addClass('fl-is-active');
    //     }
    // });

    $('.op_c_checkout_form #customer_details .form-row select').prev('label').addClass('label_select');

    $('.op_c_checkout_form').find('.woocommerce-form-coupon-toggle').hide();
    $('.op_c_checkout_form').find('.woocommerce-shipping-fields').hide();
    $('.op_c_checkout_form').find('.woocommerce-additional-fields').hide();

    $('.op_c_checkout_form').find('.payment_opt .payment_icon').first().attr('src', '/wp-content/plugins/multi-woo-checkout/images/payment_icon.png');






    $('.op_c_checkout_form').show();
    $('#op_c_loading').hide();
    $('.mwc_selected_default_opt').click();

    $(document.body).trigger("update_checkout");

    // $('.op_c_checkout_form #customer_details input').keyup( function(index, el) {
    //     if( $(this).val() == "" ) {
    //         $(this).parents('.fl-wrap').removeClass('fl-is-active');
    //     }else if( !$(this).parents('.fl-wrap').hasClass('fl-is-active') ) {
    //         $(this).parents('.fl-wrap').addClass('fl-is-active');
    //     }
    // });
    
    // change color label
    // $(document).on('click', '.mwc_item_addon .label_woothumb', function () {
    //     $(this).parents(".select_woothumb").find(".label_woothumb").removeClass("selected");

    //     $(this).addClass("selected");
    //     $(this).parents(".variation_item").find("select").val($(this).data("option")).trigger("change"); 
    // });

});

(($) => {
	$(document).ready(function () {

        var attrs =  $('.var_prod_attr');

        $.each(attrs, function() {
            var chart_append = $(this).siblings('.variation_name'),
                chart_set = $(this).parents().parents().parents().parents().parents().siblings('#sbhtml-show-chart').val();
    
            if (chart_append && chart_set) {
                var sbhtml_label_text = '',
                    // sbhtml_label_text = $(this).parents().parents().parents().parents().parents().siblings('#sbhtml_text_label').val(),
                    sbhtml_link_text = $(this).parents().parents().parents().parents().parents().siblings('#sbhtml_text_open_modal').val(),
                    label_text_content ='<div class="sbhtml_label_wrap">' + sbhtml_label_text+' <span class="sbhtml_link_text">' + sbhtml_link_text+'</span></div>';
    
                chart_append.after(label_text_content);
            }
        });

        // hide modal and overlay
        $('.sbhtml_chart_overlay, .sbhtml_modal_close').on('click', function (e) {
            e.preventDefault();
            $(this).closest('.sbhtml_chart_overlay, .sbhtml_chart_modal').hide();
        });

        $('.sbhtml_modal_close').on('click', function (e) {
            e.preventDefault();
            $(this).parents('.sbhtml_chart_modal').hide();
            $(this).parents().parents('.sbhtml_chart_overlay').hide()
        });

        // show modal and overlay
        $('.sbhtml_link_text').on('click', function (e) {
            e.preventDefault();
            $(this).parents().parents().parents().parents().parents().parents().parents('.mwc_product_variations').find('.sbhtml_chart_overlay, .sbhtml_chart_modal').show();
        });

        // stop modal
        $('.sbhtml_chart_modal').on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
        });

	});
})(window.jQuery);