jQuery(window).load(function() {
    
    // Load color picker
    var UWPColorPicker = jQuery('.uwp-color-picker');
    console.log('uwpColorPicker');
    if (UWPColorPicker.length) {
        UWPColorPicker.wpColorPicker();
    }

    jQuery('.uwp_upload_btn').click(function() {
        var $this = jQuery(this);

        frame = wp.media({
            title: 'Select or Upload Media',
            button: {
                text: 'Use Media'
            },
            multiple: false
        });

        frame.on( 'select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            $this.parent().next().find("img").attr('src', attachment.url);
            $this.parent().next().find("img").show();
            $this.parent().find(".uwp_remove_btn").show();
            $this.parent().find(".uwp_img_url").val(attachment.id);
        });

        frame.open();
    });

    jQuery('.uwp_remove_btn').click(function() {
        var answer = confirm('Are you sure?');
        if (answer == true) {
            jQuery(this).parent().next().find("img").attr('src', '');
            jQuery(this).parent().find('input.uwp_img_url').val('');
            jQuery(this).parent().next().find("img").hide();
            jQuery(this).hide();
        }
        return false;
    });

    jQuery('.uwp-upload-img').each(function() {
        var $wrap = jQuery(this);
        var field = $wrap.data('field');
        if (jQuery('[name="' + field + '[id]"]').length && !jQuery('[name="' + field + '[id]"]').val()) {
            jQuery('.uwp_remove_image_button', $wrap).hide();
        }
    });

    var media_frame = [];
    jQuery(document).on('click', '.uwp_upload_image_button', function(e) {
        e.preventDefault();

        var $this = jQuery(this);
        var $wrap = $this.closest('.uwp-upload-img');
        var field = $wrap.data('field');

        if ( !field ) {
            return
        }

        if (media_frame && media_frame[field]) {
            media_frame[field].open();
            return;
        }

        media_frame[field] = wp.media.frames.downloadable_file = wp.media({
            title: uwp_admin_ajax.txt_choose_image,
            button: {
                text: uwp_admin_ajax.txt_use_image
            },
            multiple: false
        });

        // When an image is selected, run a callback.
        media_frame[field].on('select', function() {
            var attachment = media_frame[field].state().get('selection').first().toJSON();

            var thumbnail = attachment.sizes.medium || attachment.sizes.full;
            if (field) {
                if(jQuery('[name="' + field + '[id]"]').length){
                    jQuery('[name="' + field + '[id]"]').val(attachment.id);
                }
                if(jQuery('[name="' + field + '[src]"]').length){
                    jQuery('[name="' + field + '[src]"]').val(attachment.url);
                }
                if(jQuery('[name="' + field + '"]').length){
                    jQuery('[name="' + field + '"]').val(attachment.id);
                }


            }
            $wrap.closest('.form-field.form-invalid').removeClass('form-invalid');
            jQuery('.uwp-upload-display', $wrap).find('img').attr('src', thumbnail.url);
            jQuery('.uwp_remove_image_button').show();
        });
        // Finally, open the modal.
        media_frame[field].open();
    });

    jQuery(document).on('click', '.uwp_remove_image_button', function() {
        var $this = jQuery(this);
        var $wrap = $this.closest('.uwp-upload-img');
        var field = $wrap.data('field');
        jQuery('.uwp-upload-display', $wrap).find('img').attr('src', uwp_admin_ajax.img_spacer).removeAttr('width height sizes alt class srcset');
        if (field) {
            if (jQuery('[name="' + field + '[id]"]').length > 0) {
                jQuery('[name="' + field + '[id]"]').val('');
                jQuery('[name="' + field + '[src]"]').val('');
            }
            if (jQuery('[name="' + field + '"]').length > 0) {
                jQuery('[name="' + field + '"]').val('');
            }
        }
        $this.hide();
        return false;
    });

    jQuery('.userswp .forminp .large-text').focus(function() {
        var placeholder = jQuery(this).attr('placeholder');
        var current_val = jQuery(this).val();
        if( '' == current_val ){
            jQuery(this).val( placeholder );
        }
    }).blur(function() {
        var placeholder = jQuery(this).attr('placeholder');
        var current_val = jQuery(this).val();
        if( current_val == placeholder ){
            jQuery(this).val('');
        }
    });

    uwp_init_tooltips();
});

function uwp_show_hide($this) {
    var is_open = !jQuery($this).parent('.li-settings').find('.field_frm').first().is(':hidden');
    jQuery('.field_frm').hide();
    jQuery('.field_frm').parent().parent().find('.toggle-arrow').addClass("fa-caret-down").removeClass( "fa-caret-up");
    if(is_open){
        jQuery($this).addClass("fa-caret-down").removeClass( "fa-caret-up");
        jQuery($this).parent('.li-settings').find('.field_frm').first().hide().removeClass( "uwp-tab-settings-open" );
    }else{
        jQuery($this).addClass("fa-caret-up").removeClass( "fa-caret-down");
        jQuery($this).parent('.li-settings').find('.field_frm').first().show().addClass( "uwp-tab-settings-open" );
    }
}

function validate_field(field) {

    var is_error = true;
    switch (jQuery(field).attr('field_type')) {
        case 'radio':
        case 'checkbox':

            if (jQuery(field).closest('.required_field').find(":checked").length > 0) {
                is_error = false;
            }
            break;

        case 'select':
            if (jQuery(field).find("option:selected").length > 0 && jQuery(field).find("option:selected").val() != '') {
                is_error = false;
            }
            break;

        case 'multiselect':

            if (jQuery(field).find("option:selected").length > 0) {
                is_error = false;
            }


            break;

        case 'email':
            var email_filter = /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
            if (field.value != '' && email_filter.test(field.value)) {
                is_error = false;
            }
            break;

        case 'url':
            var url_filter = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
            if (field.value != '' && url_filter.test(field.value)) {
                is_error = false;
            }
            break;

        case 'editor':
            if (jQuery('#' + jQuery(field).attr('field_id')).val() != '') {
                is_error = false;
            }
            break;

        case 'datepicker':
        case 'time':
        case 'text':
        case 'textarea':
            if (field.value != '') {
                is_error = false;
            }
            break;

        default:
            if (field.value != '') {
                is_error = false;
            }
            break;

    }


    if (is_error) {
        if (jQuery(field).closest('.required_field').find('span.uwp_message_error').html() == '') {
            jQuery(field).closest('.required_field').find('span.uwp_message_error').html(uwp_admin_ajax.custom_field_id_required)
        }

        jQuery(field).closest('.required_field').find('span.uwp_message_error').fadeIn();

        return false;
    } else {

        jQuery(field).closest('.required_field').find('span.uwp_message_error').html('');
        jQuery(field).closest('.required_field').find('span.uwp_message_error').fadeOut();

        return true;
    }
}

jQuery(document).ready(function () {
    jQuery("#uwp-form-builder-tab, #uwp-form-builder-tab-predefined").find("ul li a").click(function () {
        if(!jQuery(this).attr('id')){return;}
        var type = jQuery(this).data("field-type");
        var type_key = jQuery(this).data("field-type-key");
        var custom_type = jQuery(this).data("field-custom-type");
        var form_type = jQuery(this).closest('#uwp-form-builder-tab, #uwp-form-builder-tab-predefined').find('#form_type').val();
        var id = 'new' + jQuery(".field_row_main ul.core li:last").index();
        var manage_field_type = jQuery(this).closest('#uwp-available-fields').find(".manage_field_type").val();
        if (manage_field_type == 'custom_fields') {
            jQuery.get(uwp_admin_ajax.url + '?action=uwp_ajax_action&create_field=true', {
                field_type: type,
                field_type_key: type_key,
                form_type: form_type,
                field_id: id,
                field_ins_upd: 'new',
                manage_field_type: manage_field_type,
                custom_type: custom_type
            }, function (data) {
                jQuery('.field_row_main ul.core').append(data);
                aui_init_select2()
                jQuery('#licontainer_' + id).find('#sort_order').val(parseInt(jQuery('#licontainer_' + id).index()) + 1);
                uwp_show_hide(jQuery("#licontainer_"+id).find('.toggle-arrow'));
                uwp_init_tooltips();
                jQuery('html, body').animate({
                    scrollTop: jQuery("#licontainer_"+id).offset().top
                }, 1000);

            });
        }
    });

    jQuery("#uwp-form-builder-tab, #uwp-form-builder-tab-predefined, #uwp-form-builder-tab-custom").find("ul li a").click(function() {
        if(!jQuery(this).attr('id')){return;}
        var htmlvar_name = jQuery(this).attr('id').replace('uwp-','');
        var field_type = jQuery(this).data('type');
        var form_type = jQuery(this).closest('#uwp-form-builder-tab, #uwp-form-builder-tab-predefined').find('#form_type').val();
        var id = 'new'+jQuery(".field_row_main ul.core li:last").index();
        var manage_field_type = jQuery(this).closest('#uwp-available-fields').find(".manage_field_type").val();
        var field_data_type = jQuery(this).data('data_type');
        if (manage_field_type == 'custom_fields') {return;}

        var data = {
            'htmlvar_name': htmlvar_name,
            'form_type':form_type,
            'field_type':field_type,
            'field_data_type':field_data_type,
            'field_id': id,
            'field_ins_upd': 'new'
        };

        if (manage_field_type == 'register'){
            var action = "uwp_ajax_register_action";
        } else if (manage_field_type == 'search') {
            var action = "uwp_ajax_search_action";
        } else if (manage_field_type == 'profile_tabs') {
            var action = "uwp_ajax_profile_tabs_action";
            data = {
                'htmlvar_name':      htmlvar_name,
                'form_type':         form_type,
                'field_type':        field_type,
                'field_ins_upd':     'new',
                'tab_layout':        jQuery(this).data('tab_layout'),
                'tab_level':         jQuery(this).data('tab_level'),
                'tab_parent':        jQuery(this).data('tab_parent'),
                'tab_name':          jQuery(this).data('tab_name'),
                'tab_type':          jQuery(this).data('tab_type'),
                'tab_icon':          jQuery(this).data('tab_icon'),
                'tab_key':           jQuery(this).data('tab_key'),
                'tab_content':       jQuery(this).data('tab_content'),
                'tab_privacy':       jQuery(this).data('tab_privacy'),
                'user_decided':      jQuery(this).data('user_decided')
            };
        }

        jQuery.get(uwp_admin_ajax.url+'?action=' + action + '&create_field=true', data ,
            function(data)
            {
                console.log(id);
                jQuery('.field_row_main ul.uwp_form_extras').append(data);

                jQuery('#licontainer_'+htmlvar_name).find('#sort_order').val( parseInt(jQuery('#licontainer_'+htmlvar_name).index()) + 1 );

                uwp_show_hide(jQuery("#licontainer_"+htmlvar_name).find('.toggle-arrow'));
                uwp_init_tooltips();
                jQuery('html, body').animate({
                    scrollTop: jQuery("#licontainer_"+htmlvar_name).offset().top
                }, 1000);

                if (manage_field_type == 'register') {
                    save_register_field(htmlvar_name); // save registration fields on add
                }

                if (manage_field_type == 'search') {
                    save_search_field(htmlvar_name); // save search fields on add
                }

            });

        if(htmlvar_name!='fieldset' && manage_field_type != 'profile_tabs'){
            jQuery(this).closest('li').hide();
        }

    });

    jQuery("ul.uwp-tabs-selected").sortable({
        opacity: 0.8,
        cursor: 'move',
        placeholder: "ui-state-highlight",
        cancel: "input,label,select",
        update: function () {
            var manage_field_type = jQuery(this).closest('#uwp-selected-fields').find(".manage_field_type").val();
            var order = jQuery(this).sortable("serialize") + '&update=update&manage_field_type=' + manage_field_type;

            if (manage_field_type == 'register'){
                var action = "uwp_ajax_register_action";
            } else if (manage_field_type == 'search') {
                var action = "uwp_ajax_search_action";
            } else {
                var action = "uwp_ajax_action";
            }

            jQuery.get(uwp_admin_ajax.url + '?action='+ action +'&create_field=true', order, function (theResponse) {
                console.log('Fields have been ordered.');
            });
        }
    });

    jQuery('ul.uwp-profile-tabs-selected').nestedSortable({
        maxLevels: 2,
        handle: '.uwp-fieldset',
        items: 'li',
        disableNestingClass: 'mjs-nestedSortable-no-nesting',
        helper:	'clone',
        placeholder: 'ui-state-highlight',
        forcePlaceholderSize: true,
        listType: 'ul',
        update: function (event, ui) {
            var manage_field_type = jQuery(this).closest('#uwp-selected-fields').find(".manage_field_type").val();
            var $tabs = jQuery('.field_row_main ul.core').nestedSortable('toArray', {startDepthCount: 0});
            var $order = {};
            jQuery.each($tabs, function( index, tab ) {
                if(tab.id){
                    $order[index] = {id:tab.id, tab_level: tab.depth,tab_parent: tab.parent_id};
                }
            });

            var action = "uwp_ajax_profile_tabs_action";

            var data = {
                'tabs': $order
            };

            jQuery.get(uwp_admin_ajax.url + '?action='+ action +'&create_field=true&update=update&manage_field_type=' + manage_field_type, data, function (theResponse) {
                console.log('Fields have been ordered.');
            });
        }
    });

});

function uwp_data_type_changed(obj, cont) {
    if (obj && cont) {
        jQuery('#licontainer_' + cont).find('.decimal-point-wrapper').hide();
        if (jQuery(obj).val() == 'FLOAT') {
            jQuery('#licontainer_' + cont).find('.decimal-point-wrapper').show();
        }

        if (jQuery(obj).val() == 'FLOAT' || jQuery(obj).val() == 'INT') {
            jQuery('#licontainer_' + cont).find('.uwp-price-extra-set').show();

            if(jQuery('#licontainer_' + cont).find(".uwp-price-extra-set input[name='extra[is_price]']:checked").val()=='1'){
                jQuery('#licontainer_' + cont).find('.uwp-price-extra').show();
            }

        }else{
            jQuery('#licontainer_' + cont).find('.uwp-price-extra-set').hide();
            jQuery('#licontainer_' + cont).find('.uwp-price-extra').hide();
        }
    }
}

function save_field(id) {

    if (jQuery('#licontainer_' + id + ' #htmlvar_name').length > 0) {

        var htmlvar_name = jQuery('#licontainer_' + id + ' #htmlvar_name').val();

        if (htmlvar_name != '') {

            var iChars = "!`@#$%^&*()+=-[]\\\';,./{}|\":<>?~ ";

            for (var i = 0; i < htmlvar_name.length; i++) {
                if (iChars.indexOf(htmlvar_name.charAt(i)) != -1) {

                    alert(uwp_admin_ajax.custom_field_not_special_char);


                    return false;
                }
            }
        }

        var option_val_input = jQuery('#licontainer_' + id + ' #option_values');
        if (option_val_input.length == 1) {
            var option_values = option_val_input.val();
            if (option_values == '') {
                alert(uwp_admin_ajax.custom_field_options_not_blank_var);
                return false;
            }

        }
    }

    var fieldrequest = jQuery('#licontainer_' + id).find("select, textarea, input").serialize();
    var request_data = 'create_field=true&field_ins_upd=submit&' + fieldrequest;


    jQuery.ajax({
        'url': uwp_admin_ajax.url + '?action=uwp_ajax_action&manage_field_type=custom_fields',
        'type': 'POST',
        'data': request_data,
        'success': function (result) {

            //alert(result);
            if (jQuery.trim(result) == 'invalid_key') {

                alert(uwp_admin_ajax.custom_field_unique_name);

            }
            else {
                jQuery('#licontainer_' + id).replaceWith(jQuery.trim(result));
                aui_init_select2()

                var order = jQuery(".field_row_main ul.core").sortable("serialize") + '&update=update&manage_field_type=custom_fields';

                jQuery.get(uwp_admin_ajax.url + '?action=uwp_ajax_action&create_field=true', order,
                    function (theResponse) {
                        //alert(theResponse);
                    });

                uwp_init_tooltips();
                jQuery('.field_frm').hide();
            }



        }
    });


}

function delete_field(id, nonce) {

    var confarmation = confirm(uwp_admin_ajax.custom_field_delete);

    if (confarmation == true) {

        jQuery.get(uwp_admin_ajax.url + '?action=uwp_ajax_action&create_field=true&manage_field_type=custom_fields', {
                field_id: id,
                field_ins_upd: 'delete',
                _wpnonce: nonce
            },
            function (data) {
                jQuery('#licontainer_' + id).remove();

            });

    }

}

function delete_register_field(id, nonce, deleteid, type)
{

    if('profile_tab' == type){
        var action = 'uwp_ajax_profile_tabs_action';
    } else {
        var action = 'uwp_ajax_register_action';
    }

    var confirmation = confirm(uwp_admin_ajax.custom_field_delete);

    if(confirmation == true)
    {
        jQuery('#create_advance_search_li_'+deleteid).show();
        jQuery.get(uwp_admin_ajax.url+'?action='+action+'&create_field=true', { field_id: id, field_ins_upd: 'delete', _wpnonce:nonce },
            function(data)
            {
                jQuery('#licontainer_'+id).remove();

            });

        jQuery('#uwp-'+deleteid).closest('li').show();

    }

}

function save_register_field(id, type)
{
    if('profile_tab' == type){
        var action = 'uwp_ajax_profile_tabs_action';
    } else {
        var action = 'uwp_ajax_register_action';
    }

    if(jQuery('#licontainer_'+id+' #field_title').length > 0){

        var htmlvar_name = jQuery('#licontainer_'+id+' #field_title').val();

        if(htmlvar_name == '')
        {
            alert(uwp_admin_ajax.custom_field_not_blank_var);

            return false;
        }
    }

    var fieldrequest = jQuery('#licontainer_'+id).find("select, textarea, input").serialize();

    var request_data = 'create_field=true&field_ins_upd=submit&' + fieldrequest ;

    jQuery.ajax({
        'url': uwp_admin_ajax.url+'?action=' + action,
        'type': 'POST',
        'data':  request_data ,
        'success': function(result){


            if(jQuery.trim( result ) == 'invalid_key')
            {

                alert(uwp_admin_ajax.custom_field_unique_name);

            }
            else
            {
                jQuery('#licontainer_'+id).replaceWith(jQuery.trim( result ));

                var order = jQuery(".field_row_main ul.uwp_form_extras").sortable("serialize") + '&update=update';

                jQuery.get(uwp_admin_ajax.url+'?action=' + action + '&create_field=true', order,
                    function(theResponse){

                    });

                jQuery('.field_frm').hide();
            }


        }
    });

}

function uwp_init_advanced_settings(){
    jQuery( ".uwp-advanced-toggle" ).off("click").click(function() {
        jQuery(".uwp-advanced-toggle").toggleClass("uwpa-hide");
        console.log('toggle');
        jQuery(".uwp-advanced-setting, #default_location_set_address_button").toggleClass("uwpa-show");
    });
}

/**
 * Init the tooltips
 */
function uwp_init_tooltips(){

    // we create, then destroy then create so we can ajax load and then call this function with impunity.
    jQuery('.uwp-help-tip').tooltip().tooltip('destroy').tooltip({
        content: function () {
            return jQuery(this).prop('title');
        },
        tooltipClass: 'uwp-ui-tooltip',
        position: {
            my: 'center top',
            at: 'center bottom+10',
            collision: 'flipfit',
        },
        show: null,
        close: function (event, ui) {
            ui.tooltip.hover(

                function () {
                    jQuery(this).stop(true).fadeTo(400, 1);
                },

                function () {
                    jQuery(this).fadeOut("400", function () {
                        jQuery(this).remove();
                    })
                });
        }
    });
}