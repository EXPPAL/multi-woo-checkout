jQuery(document).ready( function( $ ) {

    var mwc_first_check_ajax = 1;
    var checkout_link = $('#mwc_checkout_link').val();
    var price_symbol = $('.woocommerce-Price-currencySymbol').first().text();
    var product_id_cart = null;
    // $(".mwc_product_attribute").Segment();
    var mwc_package_is_empty = 0;
    if( $('#mwc_package_is_empty').length )
        mwc_package_is_empty = $('#mwc_package_is_empty');
    

    /////////////////////////
    
    //var mwc_padding_bottom = 80;
    $('.mwc_selected_package_product').change( mwc_selected_package_changed );
    function mwc_selected_package_changed(){

        var _parent = $(this).parents('.mwc_item_div'),
            id = $(this).parents('.mwc_item_div').data('bundle_id').toString().trim();
        
        $('.mwc_item_config_div_variation').hide();
        let id_product = $(this).val();
        let i_index = $(this).data('index');
        $('.product_mwc_id_'+id_product+'_'+i_index).show();
        
        $('.mwc_item_div').removeClass('mwc_active_product');
        if( $(this).is(':checked') ){
            $(this).parents('.mwc_item_div').addClass('mwc_active_product');

            if($('#shortcode_type').val() == 'op_c') {
                $('.select_button').removeClass('op_c_btn_selected');
                $('.op_c_desc').hide();
                $(this).parents('.mwc_item_div').find('.select_button').addClass('op_c_btn_selected');
                $(this).parents('.mwc_item_div').find('.op_c_desc').show();

                mwc_set_summary_prices();

            }else {
                $('.btn-select').removeClass('btn_selected');
                $('.btn-select').html('<span>SELECT</span>');

                $(this).parents('.mwc_item_div').find('.btn-select').html('<span>SELECTED</span>');
                $(this).parents('.mwc_item_div').find('.btn-select').addClass('btn_selected');
            }
            
        } else {
            $(this).parents('.mwc_item_div').removeClass('mwc_active_product');
        }
        if( $('.mwc_active_product .mwc_product_attribute').length ){
            $('.mwc_active_product .mwc_product_attribute').first().change();
        } else {
            
            $('.i_mwc_pack_variations_intro_div').hide();
            $('.step').css('padding-bottom', '0px');
                
            // update_mwc_item_pack();
        }

        $('.option_item').removeClass('option_active');
        $('#opt_item_'+id_product+'_'+i_index).addClass('option_active');

        // show form select product variations
        $('.mwc_product_variations').hide();
        if($(this).parents('.mwc_item_div').parents().siblings('.mwc-tab-content').find('.mwc_product_variations .product_variations_table tr td .variation_item').length > 0) {
            $('.mwc_product_variations_' + id).slideDown();
        }

        // call func updatae cart
        mwc_update_item_cart_ajax();

        // get set price
        if(_parent.find('.js-input-cus_bundle_total_price').val() <= 0) {
            $(this).getPriceTotalAndDiscountBundleOption();
        }
        
    }


    // get price package

    jQuery.fn.getPriceTotalAndDiscountBundleOption = function() {
        
        _parent = $(this).parents('.mwc_item_div');

        if(_parent.data('type') == "bun") {
            // get product ids
            var discoutProductIDs = $(this).getDiscountProductIDs();

            var info = {};
            info['action'] = 'mwc_get_price_package';
            info['discount'] = discoutProductIDs.discount;
            info['product_ids'] = discoutProductIDs.products;

            //ajax update cart
            jQuery.get(mwc_infos.ajax_url, info).done(function (data) {
                data = JSON.parse(data);
                
                if(data.status) {
                    _parent.find('.js-input-price_package').val(JSON.stringify(data));
                    // change label price
                    _parent.find('.js-label-price_each').empty().append(data.each_price_html);
                    _parent.find('.js-label-price_total').empty().append(data.total_price_html);
                    _parent.find('.js-label-price_old').empty().append(data.old_price_html);

                    // change price package
                    // _parent.find('.pi-price-pricing .pi-price-each span').empty().append(data.total_price_html);
                    // _parent.find('.pi-price-pricing .pi-price-orig span').empty().append(data.old_price_html);

                    // set price summary
                    _parent.find('.mwc_bundle_price_hidden').val(data.total_price);
                    _parent.find('.mwc_bundle_price_regular_hidden').val(data.old_price);

                    if(_parent.hasClass('mwc_active_product')) {
                        mwc_set_summary_prices();
                    }
                }
            });
        }
    }


    // function set image variation
    function mwc_set_image_variation(_parent) {

        var prod_id = _parent.attr("data-id");
        var bun_img = _parent.find('.mwc_variation_img');

        var var_arr = {};
        _parent.find(".variation_item").each(function (index, el) {
            let _select = $(el).find(".var_prod_attr");
            if(_select.val()) {
                var_arr[_select.data("attribute_name")] = _select.val();
            }
        });

        var variation_id = '';
        $.each(opc_variation_data[prod_id], function(index, val) {
            var img = '';

            $.each(var_arr, function(i, e) {

                if(val['attributes'][i] && val['attributes'][i] == e) {
                    variation_id = val['id'];
                    img = val['image'];
                }else {
                    img = '';
                    return false;
                }
            });

            if(img){
                bun_img.attr({
                    'src': img,
                    'data-src': img
                });
                return false;
            }
        });

        return variation_id;
    }

    // update cart when select dropdown product variation
    $('.checkout_prod_attr').change(function(e) {

        var _parent = $(this).closest(".c_prod_item");

        // get variation id, set image variation
        var var_id = mwc_set_image_variation(_parent);
        // set variation id
        _parent.attr('data-variation_id', var_id);
        
        // call func updatae cart
        mwc_update_item_cart_ajax();

        // update variation price product mwc
        mwc_get_price_variation_product($(this).closest(".mwc_item_div"));

        // get set price
        $(this).getPriceTotalAndDiscountBundleOption();
    });
    

    // set image variation all option when load page
    $(window).on('load', function() {
        $(".variation_selectors").each(function (i, e) {
            if ($(this).find(".var_prod_attr").length) {
                var _parent = $(this).parents(".c_prod_item");

                var var_id = mwc_set_image_variation(_parent);
                // set variation id
                _parent.attr('data-variation_id', var_id);
            }
        });

        // update variation price product mwc
        $('.mwc_item_div').each(function (index, element) {
            id = $(this).data('bundle_id').toString().trim();
            if($(element).attr('data-type') == 'bun') {
                $('.mwc_product_variations_' + id).getPriceTotalAndDiscountBundleOption();

            } else {
                mwc_get_price_variation_product($(this));
            }
        });
    });

    $('.op_c_package_option').click(function(evt) {
        var target = $(evt.target);
        if( !target.hasClass('mwc_selected_fbt_product') && !target.is( "select" ) && !target.is( "input" ) && !target.is( ".ui-segment *" ) )
            $(this).find('.mwc_selected_package_product').click();
    });


    // if( $('.mwc_product_attribute').length ){
    //     $('.mwc_product_attribute').first().change();
    // } else {
    //     $('.mwc_selected_package_product').first().prop('checked', true).change();
    // }

    //scroll bundle option mobile
    $('.option_item').click(function() {
        var id_prod = $(this).data('id');
        var i_index = $(this).data('item');
        $('.mwc_item_div_'+id_prod+'_'+i_index).click();
        $('.option_item').removeClass('option_active');
        $(this).addClass('option_active');
        
        // scroll to option selected
        var width_scroll = $('.card').width();
        var item = $(this).data('item');
        $('.scrolling-wrapper').animate( { scrollLeft: width_scroll*item }, 500);
    });


    // add class active to custom form checkout woo onepage checkout
    // $('.op_custom_checkout_form .woocommerce .woocommerce-billing-fields .form-row').each(function(index, el) {
        
    //     if($(el).find('input').attr('placeholder') == "") {
    //         let _label = $(el).find('label').first().text();
    //         $(el).find('input').attr('placeholder', (_label.split("*", 1)));
    //     }
        
    //     if( $(this).find('input').val() != "" ) {
    //         $(this).addClass('fl-is-active');
    //     }
    // });

    // // when keyup input
    // $('.op_custom_checkout_form .woocommerce .woocommerce-billing-fields input').keyup( function(index, el) {

    //     if( $(this).val() == "" ) {
    //         $(this).parents('p.form-row').removeClass('fl-is-active');
    //     }else if( !$(this).parents('p.form-row').hasClass('fl-is-active') ) {
    //         $(this).parents('p.form-row').addClass('fl-is-active');
    //     }
    // });


    // change color label
    $(document).on('click', '#mwc_checkout .label_woothumb', function () {
        $(this).parents(".select_woothumb").find(".label_woothumb").removeClass("selected");

        $(this).addClass("selected");
        $(this).parents(".variation_item").find("select").val($(this).data("option")).trigger("change"); 
    });
    $(document).on('click', '#mwc_checkout .attribute-swatch > .swatchinput > label:not(.disabled)', function () {
        $(this).closest(".variation_item").find(".swatchinput > label").removeClass("selected");

        $(this).addClass("selected");
        $(this).closest(".variation_item").find("select").val($(this).data("option")).trigger("change"); 
    });



    // linked variations select
    $(document).on('click', '#mwc_checkout .attribute-swatch > .swatchinput .linked_product:not(.disabled)', function(e) {
        var _parent = $(this).closest(".c_prod_item");
        _parent.attr( 'data-id', $(this).attr( 'data-linked_id' ) );

        // get variation id, set image variation
        var var_id = mwc_set_image_variation(_parent);
        // set variation id
        _parent.attr('data-variation_id', var_id);

        mwc_update_item_cart_ajax();
    });

});


// element function get discount and product ids
jQuery.fn.getDiscountProductIDs = function() {
    var _self = this;
    var el_parent = jQuery(_self).parents('.mwc_item_div');

    var arr_discount = {
        'type': el_parent.find('.js-input-discount_package').attr('data-type'),
        'qty': el_parent.find('.js-input-discount_package').attr('data-qty'),
        'value': el_parent.find('.js-input-discount_package').val()
    };
     
    var arr_prod_ids = [];
    jQuery(el_parent.find('.mwc_product_variations .c_prod_item')).each(function (index, element) {
        if( jQuery(element).attr('data-variation_id') ) {
            arr_prod_ids.push(jQuery(element).attr('data-variation_id'));
        } else {
            arr_prod_ids.push(jQuery(element).attr('data-id'));
        }
    });

    return {
        'discount': arr_discount,
        'products': arr_prod_ids
    };
}




// function ajax add to cart when select option onepage checkout
function mwc_update_item_cart_ajax() {
    var bundle_id = jQuery('.mwc_active_product').data('bundle_id');

    var add_to_cart_items_data = {
        'products': {}
    };

    jQuery('.mwc_product_variations_' + bundle_id + ' .c_prod_item').each(function(index, el) {
        let variation_id = jQuery(this).attr('data-variation_id');
        let _prod_id = jQuery(this).data('id');
        
        if( _prod_id ) {
            i_product_attribute = {};
            jQuery(this).find('.checkout_prod_attr').each(function(var_i, var_el) {
                if( jQuery(var_el).val() ){
                    if( jQuery(var_el).data('attribute_name') ) {
                        i_product_attribute[ jQuery(var_el).data('attribute_name') ] = jQuery(var_el).val();
                    }
                }
            });
        }

        // linked variations
        var linked_product = {
            'id': '',
            'attributes': {}
        };
        if (jQuery(this).find('.linked_product.selected').attr('data-linked_id')) {
            var el_linked = jQuery(this).find('.linked_product.selected');
            linked_product['id'] = el_linked.attr('data-linked_id');
            linked_product['attributes'][el_linked.attr('data-attribute_name')] = el_linked.attr('data-option');
        }


        add_to_cart_items_data['products'][_prod_id+'_' + (index + 1)] = {
            product_id: _prod_id,
            linked_product: linked_product,
            variation_id: variation_id,
            i_product_attribute: i_product_attribute,
            qty: 1,
            separate: 1
        };
        
    });

    // add addon products
    if(jQuery('.mwc_item_addons_div').length) {
        var addon_products = {
            'products': {}
        };

        jQuery('.mwc_item_addons_div .mwc_item_addon.i_selected').each(function(index, el) {
            // get addon id
            let _addon_id = jQuery(this).data('addon_id');
            // get product id
            let _prod_id = jQuery(this).data('id');
            addon_attr = {};

            jQuery(el).find('.info_variations .variation_item .addon_var_select').each(function(var_i, var_el) {
                if( jQuery(var_el).val() ){
                    if( jQuery(var_el).data('attribute_name') ) {
                        addon_attr[ jQuery(var_el).data('attribute_name') ] = jQuery(var_el).val();
                    }
                }
            });

            addon_products['products'][_prod_id+'_' + (index + 1)] = {
                product_id: _prod_id,
                mwc_addon_id: _addon_id,
                i_product_attribute: addon_attr,
                qty: jQuery(el).find('.cao_qty .addon_prod_qty').val(),
            };
        });
    }

    var info = {};
    info['action'] = 'mwc_add_to_cart_multiple';
    info['bundle_id'] = bundle_id;
    info['add_to_cart_items_data'] = add_to_cart_items_data;
    info['addon_products'] = addon_products;
    info['mwc_first_check_ajax'] = 0;
    info['mwc_dont_empty_cart'] = 1;

    //ajax update cart
    jQuery.post(mwc_infos.ajax_url, info).done(function (data) {
        data = JSON.parse(data);
        if( data.status ){
            // jQuery(document.body).trigger("update_checkout");
            
            //set shipping type
            jQuery(".statistical .td-shipping").html(data.shipping);

            // set data price summary table
            if(jQuery('.mwc_upsell_product_wrap').length) {
                mwc_set_summary_prices();
            }
        
        jQuery(document.body).trigger("update_checkout");
        } else {
            alert( data.html );
            jQuery('#mwc_loading').hide();
        }
    });
}
// end function ajax add to cart


function mwc_set_summary_prices() {

    jQuery('.mwc_items_div #order_summary').addClass('mwc_loading');
    price_list = {};

    var product_qty = jQuery('.mwc_item_div.mwc_active_product .mwc_bundle_product_qty_hidden').val();

    // sale price
    sale_price = jQuery('.mwc_item_div.mwc_active_product .mwc_bundle_price_hidden');
    price_list['sale_price'] = {
        sum: 1,
        label: sale_price.data('label'),
        price: sale_price.val()
    }
    
    // Old Price
    old_price = jQuery('.mwc_item_div.mwc_active_product .mwc_bundle_price_regular_hidden');
    price_list['old_price'] = {
        sum: 0,
        label: old_price.data('label'),
        price: old_price.val() * product_qty
    }
    
    // addon price
    addon_label = '';
    addon_price = 0;
    if(jQuery('.mwc_upsell_product_wrap').length) {
        addon_label = jQuery('.mwc_upsell_product_wrap').data('label');
        jQuery('.mwc_item_addon.i_selected').each(function(i, e) {
            let e_price = parseFloat(jQuery(e).find('.mwc_addon_price_hidden').val());
            let e_qty = parseFloat(jQuery(e).find('.addon_prod_qty').val());
            addon_price += e_price * e_qty;
        });
    }
    price_list['addon_price'] = {
        sum: 1,
        label: addon_label,
        price: addon_price
    }

    var info = {};
    info['action'] = 'mwc_get_price_summary_table';
    info['price_list'] = price_list;
    info['bundle_id'] = jQuery('.mwc_item_div.mwc_active_product').data('bundle_id');

    jQuery.get(mwc_infos.ajax_url, info).done(function (data) {
        data = JSON.parse(data);   
        if(data.status) {
            img = jQuery('.mwc_item_div.mwc_active_product').find('.op_c_package_image img').attr('src');
            jQuery('#s_image').find('img').attr('src', img);

            jQuery('.mwc_summary_table').empty();
            jQuery('.mwc_summary_table').append(data.html);
            jQuery('#order_summary').show();
        }

        setTimeout(function(){
            jQuery('.mwc_items_div #order_summary').removeClass('mwc_loading');
         }, 500);
    });

}

// function get price variation MWC product
function mwc_get_price_variation_product(mwc_item_div) {

    var product_prices = [],
        id = mwc_item_div.data('bundle_id').toString().trim();
    if(jQuery('.mwc_product_variations_' + id).hasClass("is_variable")) {
        console.log("is variable");
        jQuery('.mwc_product_variations_' + id).find('.c_prod_item').each(function (i, el) {
            product_prices.push( mwc_variation_price[mwc_item_div.attr('data-bundle_id')][jQuery(this).attr('data-variation_id')] );
        });

        var info = {};
        info['action'] = 'mwc_get_price_variation_product';
        info['price_list'] = product_prices;
        info['cupon'] = mwc_item_div.data('cupon');

        //ajax update cart
        jQuery.get(mwc_infos.ajax_url, info).done(function (data) {
            data = JSON.parse(data);
            if( data.status ) {
                mwc_item_div.find('.pi-price-pricing > .pi-price-each > span').first().html(data.single_price_html);
                mwc_item_div.find('.pi-price-total > span').first().html(data.total_price_html);
                // set total price hidden input
                mwc_item_div.find('.mwc_bundle_price_hidden').first().val(data.total_price);
            }
        });
    }
    
}