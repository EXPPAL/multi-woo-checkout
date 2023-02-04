jQuery(document).ready(function ($) {

    var custom_uploader;

    // add tinymce to title bundle
    tinymce.init({
        selector: '.title_main',
        theme: 'modern',
        plugins: [
            'paste textcolor colorpicker code'
        ],
        toolbar1: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent',
        toolbar2: 'forecolor backcolor code',
    });

    // load wp color picker to item label
    try {
        $('.my-color-field').wpColorPicker();
    } catch (error) {
        console.log('wpColorPicker not found!');
    }

    $('.upload_image_button').click(function(e) {

        e.preventDefault();
        parent = $(this).parent('td label');

        //If the uploader object has already been created, reopen the dialog
        if (custom_uploader) {
            custom_uploader.open();
            return;
        }

        //Extend the wp.media object
        custom_uploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose Image',
            button: {
                text: 'Choose Image'
            },
            multiple: true
        });

        //When a file is selected, grab the URL and set it as the text field's value
        custom_uploader.on('select', function() {
            attachment = custom_uploader.state().get('selection').first().toJSON();
            parent.find('.upload_image').val(attachment.url);
        });

        //Open the uploader dialog
        custom_uploader.open();

    });

    
    $('.description_add').on('click',function() {
        var create = true;
        var parents = $(this).parents('tr').find('td .feature_desc_add');
        var inputs = parents.find('.desc_input');
        for(inp of inputs){
            if(!inp.value)
                create = false;
        }

        if(create) {
            var type = parents.data('type');
            parents.find('.input_zone').append(
            '<div class=\'selectpicker_list\'>'+
                '<input name=\'feature_'+ type +'_desc[]\' class=\'desc_input quantity_main_bundle\' type=\'text\' value=\'\'>'+
                '<button type=\'button\' class=\'remove button\'>x</button>'+
            '</div>');
        }
    });

    $('.label_add').on('click',function() {
        var create_label = true;
        var parents = $(this).parents('tr').find('td .item_label_add');
        var inputs = parents.find('.label_input');
        for(inp of inputs){
            if(!inp.value)
            create_label = false;
        }
        if(create_label) {
            var type = parents.data('type');
            parents.find('.input_zone').append(
            '<div class=\'selectpicker_list\'>'+
            '<input name=\'name_label_'+ type +'[]\' class=\'label_input quantity_main_bundle\' type=\'text\' value=\'\'>'+
            '<label class="label_inline"> Color: </label><input type=\'text\' value=\'#bada55\' name=\'color_label_'+ type +'[]\' class=\'my-color-field\' data-default-color=\'#effeff\' />'+
                '<button type=\'button\' class=\'remove button\'>x</button>'+
            '</div>');

            $('.my-color-field').wpColorPicker();
        }
    });


    
    $('.selectpicker').select2({
        minimumInputLength: 3,
        tags: [],
        ajax: {
                type: "GET",
                url: '/wp-admin/admin-ajax.php',             
//                data: 'action=bundle',
                    dataType: 'json',
                    data: function (term) {
                    
                    if(term && term.term)
                        return 'action=bundle&product_title='+term.term;      
                    else
                        return 'action=bundle&product_title='+'';   
                        },
                    processResults: function (data) {                
                        return {
                            results: data.results.map((item) => {                              
                                return {
                                    prod_id: item.ID,
                                    text: item.ID+': '+item.post_title,                                   
                                    id: item.ID+'/%%/'+item.post_title
                                }  
                            })  
                        };  
                    }   
            }
    });

    
    // disable input no selected
    $('#mwc_bundle_selection_meta .product').each(function (i, el) {
        if(!$(this).hasClass('activetype')) {
            $(this).find('input').attr('disabled', 'disabled');
        }
    });
    // event change bundle option
    $('.select_type').on('change',function() {
        var type = $('.select_type').val();
        $('.activetype').removeClass('activetype');
        $('.activetype_button').removeClass('activetype_button');
        // disable input
        $('#mwc_bundle_selection_meta .product input').attr('disabled', 'disabled');

        $('.product_'+type).addClass('activetype');
        // remove disable current option
        $('#mwc_bundle_selection_meta .product.product_'+type+' input').removeAttr("disabled");
        if(type == 'bun'){
                $('.product_add_bun').addClass('activetype_button');
                $('.product_bun_cupon').addClass('activetype_button');
        }
    });
    $('.product_add_bun').on('click',function() {
        var create = true;
        var inputs = $('.product_select_bun');
        for(inp of inputs){
            if(!inp.value)
                create = false;
        }
        if(create)
            $('.product_bun .new_prod').append(
                '<div class=\'selectpicker_list\'>'+
                    ' <select name=\'selValue_bundle[]\' class=\'selectpicker product_select_bun\' style=\'width: 400px;\'></select>' +                     
                '  <label class="label_inline">Quantity </label> <input name=\'bundle_quantity[]\' type=\'number\' class=\'quantity_main_bundle small-text\'> '+
                    ' <button type=\'button\' class=\'remove button\'>x</button> '+
                '</div>');
            $('.selectpicker').select2({
                minimumInputLength: 3,
                tags: [],
                ajax: {
                    type: "GET",
                    url: '/wp-admin/admin-ajax.php',
                    // data: 'action=bundle',
                    dataType: 'json',
                    data: function (term) {
                        
                        if(term && term.term)
                            return 'action=bundle&product_title='+term.term;
                        else
                            return 'action=bundle&product_title='+'';   
                    },
                    processResults: function (data) {
                        return {
                            results: data.results.map((item) => {
                                return {
                                    prod_id: item.ID,
                                    text: item.ID +' :'+ item.post_title,
                                    id: item.ID+'/%%/'+item.post_title
                                }  
                            })  
                        };  
                    }   
                }
            });
    });        
    $(document).on('click','.remove',function(event){
        $(this).closest('.selectpicker_list').remove();
    });

    // dropdown collapse price
    $(document).on('click','#mwc_bundle_selection_meta .collapsible',function(event){
        $(this).toggleClass("active");
        if ($(this).next().css('display') === "block") {
            $(this).next().slideUp();
        } else {
            $(this).next().slideDown();
        }
    });

    // get html custom product price
    $('.selectpicker').on('select2:select', function (e) {
        var parent_bundle = $(this).parents('.product.activetype');
        var prod_id = e.params.data.prod_id;
        
        var info = {};
        info['action'] = 'mwc_get_html_custom_product_price';
        info['product_id'] = prod_id;
        
        //ajax update cart
        $.get('/wp-admin/admin-ajax.php', info).done(function (data) {
            data = JSON.parse(data);
            if( data.status ) {
                parent_bundle.find('.custom_prod_price').empty();
                parent_bundle.find('.custom_prod_price').append(data.html);
            }
        });
    });

    // focus input custom price
    $(document).on("focus", ".custom_price_prod .input_price", function() {
        if($(this).val() == '') {
            $(this).attr('value', $(this).attr('data-value'));
        }
    });

});