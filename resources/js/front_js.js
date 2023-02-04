jQuery(document).ready( function( $ ) {

    //progress bar animation
    var progress = '100%';
    $('.loadingMessageContainerWrapper .bar').animate({
        width: progress,
    }, {
        duration: 6000,
        step: function (now, fx) {
            if (now >= 25) {
                $('.counter .steps1').hide();
                $('.counter .steps2').show();
            }
            if(now >= 50 ) {
                $('.counter .steps2').hide();
                $('.counter .steps3').show();
            }
            if(now >= 75 ) {
                $('.counter .steps3').hide();
                $('.counter .steps4').show();
            }
        },
        complete: function () {
            $('.loadingMessageContainerWrapper').hide();
        }
    });
    //end progress bar


    var mwc_first_check_ajax = 1;
    var checkout_link = $('#mwc_checkout_link').val();
    var price_symbol = $('.woocommerce-Price-currencySymbol').first().text();
    var product_id_cart = null;
    // $(".mwc_product_attribute").Segment();
    var mwc_package_is_empty = 0;
    if( $('#mwc_package_is_empty').length )
        mwc_package_is_empty = $('#mwc_package_is_empty');

    //load image variation to select form
    if($('#shortcode_type').val() == 'package_order') {
        $('.sel_product_pa_color').each(function(index, el){
            var img = $(el).find(':selected').data('img');
            $(el).parents('.option_variation').find('.img-variations img').attr('src', img);
        });
    }
                                                          
    $('.mwc_collapser').click( do_mwc_collapse );
    function do_mwc_collapse(){
        if( $(this).hasClass('mwc_collapser_disabled') )
            return;
        if( $(this).hasClass('mwc_collapser_open') ){
            $(this).removeClass('mwc_collapser_open').addClass('mwc_collapser_close');
            $(this).next('.mwc_collapser_inner').slideUp();
        } else {
            $(this).removeClass('mwc_collapser_close').addClass('mwc_collapser_open');
            $(this).next('.mwc_collapser_inner').slideDown();
        }
    }

    $('.i_mwc_form').submit( i_mwc_checkout );
    function i_mwc_checkout( e ){
        e.preventDefault();
        var product_id = ''; var qty = 1; var separate = 1;
        var add_to_cart_items_data = {
            'products': {}
        };
        $(this).find( 'input.mwc_product_ids' ).each(function(index, el){
            if ( $(el).val() ) {
                product_id = $(el).val();
                if( $(el).attr('data-qty') )
                    qty = $(el).attr('data-qty');
                if( $(el).attr('data-separate') )
                    separate = $(el).attr('data-separate');

                if( typeof add_to_cart_items_data['products'][product_id] == "undefined"  ){
                    add_to_cart_items_data['products'][product_id] = {};
                }
                add_to_cart_items_data['products'][product_id] = {
                    product_id: product_id,
                    i_product_attribute: '',
                    qty: qty,
                    separate: separate
                };
            }
        });

        var info = {};
        info['action'] = 'mwc_add_to_cart_multiple';
        info['add_to_cart_items_data'] = add_to_cart_items_data;

        $('#mwc_loading').show();

        $.post(mwc_infos.ajax_url, info).done(function (data) {
            data = JSON.parse(data);
            //console.log( data );
            if( data.status ){
                location.href = checkout_link //'?mwc_checkout=1';//mwc_infos.checkout_url;
            } else {
                alert( data.html );
                $('#mwc_loading').hide();
            }
        });
        return false;
    }

    $('.i_mwc_form_pack_selector').submit( change_mwc_item_pack );
    function change_mwc_item_pack( e ){
        e.preventDefault();

        if( !$(this).parents('.mwc_item_pack_selector_div').hasClass('mwc_pack_selector_selected')
        || $(this).parents('.mwc_item_pack_selector_div').hasClass('mwc_pack_select_again')){

            $('.mwc_item_div').hide().removeClass('mwc_active_product');
            $('.mwc_pack_selector_selected').removeClass('mwc_pack_selector_selected').removeClass('mwc_pack_select_again');
            $(this).parents('.mwc_item_pack_selector_div').addClass('mwc_pack_selector_selected');
            var product_id = '';
            /*var add_to_cart_items_data = {
                'products': {}
            };*/
            $(this).find( 'input.mwc_product_ids' ).each(function(index, el){
                if ( $(el).val() ) {
                    product_id = $(el).val();
                    $('.mwc_item_div_'+product_id).show().addClass('mwc_active_product');
                }
            });
            update_mwc_item_pack();
        }

        return false;
    }

    function update_mwc_item_pack(){
        var product_id = ''; 
        var separate = 1;
        var add_to_cart_items_data = {
            'products': {}
        };
        var product_n = 1;
        var bundle_id = '';
        $('.mwc_active_product').each( function (index, el) {
            var type = $(el).attr('data-type');
            bundle_id = $(el).data('bundle');
            var i_index = $(el).find('.mwc_selected_package_product').data('index');

            if(type == 'free' || type == 'off') {
                product_id = $(el).find('.product_id').val();
                var i_items = $('.product_mwc_id_'+product_id+'_'+i_index).find('.i_variations').attr("data-items");
                for (let i = 1; i <= i_items; i++) {
                    if ( product_id ) {
                        separate = '';
                        if( $(el).find('.product_separate').val() )
                            separate = $(el).find('.product_separate').val();
        
                        if( typeof add_to_cart_items_data['products'][product_id] == "undefined"  ){
                            add_to_cart_items_data['products'][product_id] = {};
                        }
        
                        i_product_attribute = {};
                        // $(el).find('.mwc_product_attribute').each(function(var_index, var_el){
                        $('.product_mwc_id_'+product_id+'_'+i_index).find('.mwc_product_attribute').each(function(var_index, var_el) {
                            if( $(var_el).val() ){
                                if($(var_el).attr('data-item') == i) {
                                    i_product_attribute[$(var_el).attr('name').replace('i_variation_','')] = $(var_el).val();
                                }
                            }
                            // console.log(i_product_attribute);
                        });
                        i_product_attribute['attribute_separate'] = separate;
        
                        add_to_cart_items_data['products'][product_id+'_'+product_n] = {
                            product_id: product_id,
                            i_product_attribute: i_product_attribute,
                            qty: 1,
                            separate: 1
                        };              
                    }
                    product_n++;
                }
            }else {
                post_id = $(el).find('.product_id').val();
                var i_index = $(el).find('.mwc_selected_package_product').data('index');

                $('.product_mwc_id_'+post_id+'_'+i_index).find('.select-variations').each(function(var_index, var_el) {
                    
                    var product_id = $(var_el).attr('data-id');
                    separate = '';
                    if( $(el).find('.product_separate').val() )
                        separate = $(el).find('.product_separate').val();
    
                    if( typeof add_to_cart_items_data['products'][product_id] == "undefined" ){
                        add_to_cart_items_data['products'][product_id] = {};
                    }

                    i_product_attribute = {};
                    $(var_el).find('.mwc_product_attribute').each(function(sel_i, sel_el) {
                        if( $(sel_el).val() ){
                            i_product_attribute[$(sel_el).attr('name').replace('i_variation_','')] = $(sel_el).val();
                        }
                    });
                    
                    i_product_attribute['attribute_separate'] = separate;
        
                    add_to_cart_items_data['products'][product_id+'_'+product_n] = {
                        product_id: product_id,
                        i_product_attribute: i_product_attribute,
                        qty: 1,
                        separate: 1
                    };
                    product_n++;
                });
            }
        });

        //// Check also addon products --
        
        var product_o = 1;
        var addon_products = [];

        $('.mwc_fbt_item.i_active_product').each( function (index, el) {            
            if ( $(el).find('.mwc_selected_fbt_product').data('product_id') ) {
                product_id = $(el).find('.mwc_selected_fbt_product').data('product_id');
                
                //separate = 'x';
                //if( $(el).find('.product_separate').val() )
                //    separate = $(el).find('.product_separate').val();

                if( typeof add_to_cart_items_data['products'][product_id] == "undefined"  ){
                    add_to_cart_items_data['products'][product_id] = {};
                }

                i_product_attribute = {};
                $('.product_mwc_id_'+product_id).find('.mwc_product_attribute').each(function(var_index, var_el){
                    if( $(var_el).val() ){
                        i_product_attribute[$(var_el).attr('name').replace('i_variation_','')] = $(var_el).val();
                    }
                });
                
                i_product_qty = $(el).find('.i_product_qty').val();
                //i_product_attribute['attribute_separate'] = separate;
                
                addon_products.push(product_id);

                add_to_cart_items_data['products'][product_id+'_'+product_o] = {
                    product_id: product_id,
                    i_product_attribute: i_product_attribute,
                    i_product_qty: i_product_qty,
                    qty: 1,
                    //separate: 1
                };
            }
            product_o++;
        });

        
                
        add_to_cart_items_data['addon_products'] = addon_products.join(",");
        ///////// -- Addon products check is done

        var info = {};
        info['action'] = 'mwc_add_to_cart_multiple';
        info['bundle_id'] = bundle_id;
        info['add_to_cart_items_data'] = add_to_cart_items_data;
        info['mwc_first_check_ajax'] = mwc_first_check_ajax;
        if( mwc_first_check_ajax )
            mwc_first_check_ajax = 0;

        // if(product_id_cart == product_id) {
        //     info['mwc_dont_empty_cart'] = true;
        // }else {
        //     product_id_cart = product_id;
        // }

        $('#mwc_loading').show();
        //console.log(mwc_infos.ajax_url);
        
        $.post(mwc_infos.ajax_url, info).done(function (data) {
            data = JSON.parse(data);
            if( data.status ){
                $(document.body).trigger("update_checkout");
            } else {
                alert( data.html );
                $('#mwc_loading').hide();
            }
        });

    }
    
    /* 
    $(document.body).on("update_checkout", function(){
        $('.mwc_subtotal_price').html( $('.cart-subtotal .woocommerce-Price-amount.amount').html('...') );
    });
    $(document.body).on("updated_checkout", function(){
        $('.mwc_subtotal_price').html( $('.cart-subtotal .woocommerce-Price-amount.amount').html() );
    });
    */
    
    $('.mwc_product_attribute').change( function( i, e){
        var i_item = $(this).find(':selected').data('item');
        var i_img = $(this).find(':selected').data('img');
        
        $('.mwc_item_div.mwc_active_product').each( function( index, el ){
            c_product_price = 0;
            var c_product_id = $(el).find('input.product_id').val();

            if(i_img != '') {
                $('.img_'+c_product_id+'_'+i_item).attr('srcset','').attr('src', i_img);
            }
            
            if( $(el).find('.i_variations').length ){
                var i_product_variations = mwc_products_variations[c_product_id];

                var i_variations_el = $(el).find('.i_variations');
                var variation_found = false;
                var found_n_max = 0; var found_variation_index = 0;
                var var_span_txt = '';
                var var_price_txt = '';
            
                $('.i_mwc_pack_variations_intro_div').show();
                $('.step').css('padding-bottom', '66px');

                $.each(i_product_variations, function(var_index, var_value) {
                    var found_i = 0;
                    var found_n = 0;
                    $.each(var_value, function(opt_index, opt_value) {
                        if( opt_value && opt_index != 'image' ){
                            if ( i_variations_el.find('select[name=i_variation_'+opt_index+']').val() == opt_value ){
                                
                                found_i++;
                                found_n ++;
                            } else {
                                found_i--;
                            }
                        } else {
                            found_i++;
                        }
                    });
                    if( found_i == Object.keys(var_value).length ){
                        if( found_n >= found_n_max ){
                            found_n_max = found_n;
                            variation_found = true;
                            found_variation_index = var_index;
                            c_product_price = Number( mwc_products_variations_prices[c_product_id][var_index] );
                        }
                    }

                });
                if( found_variation_index ){
                    variation_image_url = mwc_products_variations[c_product_id][found_variation_index]['image'];
                    //console.log( mwc_products_variations[c_product_id][found_variation_index] );
                    $(el).find('.mwc_item_image_div img').attr('srcset','').attr('src', variation_image_url);
                    
                }
                /*
                var_span_txt = '('+price_symbol+c_product_price+', '; k = 1;
                $(el).find('.i_variations .mwc_product_attribute').each(function( var_i, variation_select){
                    var variation_val = $(variation_select).val();
                    if( variation_val ){
                        if( var_i > 0 )
                            var_span_txt+= ' - ';
                        var_span_txt+=variation_val;
                    }
                });
                var_span_txt+= ')';
                */
                var_price_txt = '<span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">'+price_symbol+'</span>'+c_product_price+'</span>';
                //$(el).find('.i_for_variations').html(var_span_txt);
                $(el).find('.i_for_price ins').html(var_price_txt);
            }

        });

        $('.mwc_fbt_item.i_active_product').each( function( index, el ){
            c_product_price = 0;
            var c_product_id = $(el).find('input.mwc_selected_fbt_product').val();
            if( $(el).find('.i_variations').length ){
                var i_product_variations = mwc_products_variations[c_product_id];

                var i_variations_el = $(el).find('.i_variations');
                var variation_found = false;
                var found_n_max = 0; var found_variation_index = 0;

                $.each(i_product_variations, function(var_index, var_value) {
                    var found_i = 0;
                    var found_n = 0;
                    $.each(var_value, function(opt_index, opt_value) {
                        if( opt_value && opt_index != 'image' ){
                            if ( i_variations_el.find('select[name=i_variation_'+opt_index+']').val() == opt_value ){
                                found_i++;
                                found_n ++;
                            } else {
                                found_i--;
                            }
                        } else {
                            found_i++;
                        }
                    });
                    if( found_i == Object.keys(var_value).length ){
                        if( found_n >= found_n_max ){
                            found_n_max = found_n;
                            variation_found = true;
                            found_variation_index = var_index;
                            c_product_price = Number( mwc_products_variations_prices[c_product_id][var_index] );
                        }
                    }

                });
                if( found_variation_index ){
                    variation_image_url = mwc_products_variations[c_product_id][found_variation_index]['image'];
                    //console.log( mwc_products_variations[c_product_id][found_variation_index] );
                    $(el).find('.mwc_image_container img').attr('srcset','').attr('src', variation_image_url);
                }

                var_span_txt = '('+price_symbol+c_product_price+', '; k = 1;
                $(el).find('.i_variations .mwc_product_attribute').each(function( var_i, variation_select){
                    var variation_val = $(variation_select).val();
                    if( variation_val ){
                        if( var_i > 0 )
                            var_span_txt+= ' - ';
                        var_span_txt+=variation_val;
                    }
                });
                var_span_txt+= ')';
                $(el).find('.i_product_price').html(var_span_txt);
            }

        });
        update_mwc_item_pack();
    } );

    /////////////////////////

    $('.mwc_selected_fbt_product').change( mwc_selected_fbt_product_changed );
    function mwc_selected_fbt_product_changed(){
        if( $(this).is(':checked') ){
            $(this).parents('.mwc_fbt_item').addClass('i_active_product');
        } else {
            $(this).parents('.mwc_fbt_item').removeClass('i_active_product');
        }
        var mwc_fbt_active_products = '';
        $('.mwc_fbt_product_ids').remove();
        $('.mwc_fbt_item.i_active_product').each(function(index, el){
            if( mwc_fbt_active_products != '' )
                mwc_fbt_active_products+=', ';
            mwc_fbt_active_product_id = $(el).find('.mwc_selected_fbt_product').attr('data-product_id');
            mwc_fbt_active_products+= mwc_fbt_active_product_id;
            /*$('.mwc_item_pack_selector form.i_mwc_form_pack_selector').append(
                '<input type="hidden" name="product_ids[]" value="'+mwc_fbt_active_product_id+'_x" class="mwc_product_ids mwc_fbt_product_ids">'
            );*/
        });
        //$('.mwc_item_pack_selector').attr('data-addon_products', mwc_fbt_active_products);
        if( $('.mwc_items_div').length ){
            if( $('.mwc_package_option').length ){
                $('.mwc_active_product input.mwc_selected_package_product').change();
            } else {
                $('.mwc_item_pack_selector.mwc_pack_selector_selected').addClass('mwc_pack_select_again').find('form.i_mwc_form_pack_selector').submit();
            }
        } else {

        }

        //$('html,body').animate({'scrollTop': ($('.mwc_items_pack_selector_div').offset().top - 40)}, 500);
    }

    $('.mwc_fbt_item').click(function(evt){
        var target = $(evt.target);
        if( !target.hasClass('mwc_selected_fbt_product') && !target.is( "select" ) && !target.is( "input" ) && !target.is( ".ui-segment *" ) )
            $(this).find('.mwc_selected_fbt_product').click();
    });

    
    //var mwc_padding_bottom = 80;
    $('.mwc_selected_package_product').change( mwc_selected_package_changed );
    function mwc_selected_package_changed(){

        var _parent = $(this).parents('.mwc_item_div');
        

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

        //mwc_padding_bottom = $('.mwc_item_div.mwc_active_product .mwc_item_config_div').height()+10;
        //$('.mwc_items_div.mwc_package_items_div').css('padding-bottom', mwc_padding_bottom+'px');

        $('.option_item').removeClass('option_active');
        $('#opt_item_'+id_product+'_'+i_index).addClass('option_active');


        // show form select product variations
        $('.mwc_product_variations').hide();
        if($(this).parents('.mwc_item_div').find('.mwc_product_variations .product_variations_table tr td .variation_item').length > 0) {
            $(this).parents('.mwc_item_div').find('.mwc_product_variations').slideDown();
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
            if($(element).attr('data-type') == 'bun') {
                $(element).find('.mwc_product_variations').getPriceTotalAndDiscountBundleOption();

            } else {
                mwc_get_price_variation_product($(this));
            }
        });
    });

    
    $('.mwc_package_option').click(function(evt){
        var target = $(evt.target);
        if( !target.hasClass('mwc_selected_fbt_product') && !target.is( "select" ) && !target.is( "input" ) && !target.is( ".ui-segment *" ) )
            $(this).find('.mwc_selected_package_product').click();
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

    jQuery('.mwc_active_product').find('.info_products_checkout .c_prod_item').each(function(index, el) {
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

    var product_prices = [];
    if(mwc_item_div.find('.mwc_product_variations').hasClass("is_variable")) {
        mwc_item_div.find('.c_prod_item').each(function (i, el) {
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