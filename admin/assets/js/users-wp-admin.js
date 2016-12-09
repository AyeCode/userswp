jQuery(window).load(function() {
    // Chosen selects
    if (jQuery("select.uwp_chosen_select").length > 0) {
        jQuery("select.uwp_chosen_select").chosen();
        jQuery("select.uwp_chosen_select_nostd").chosen({
            allow_single_deselect: 'true'
        });
    }
});

function show_hide(id) {
    jQuery('#' + id).toggle();
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
                jQuery('#licontainer_' + id).find('#sort_order').val(parseInt(jQuery('#licontainer_' + id).index()) + 1);
                show_hide('field_frm'+id);
                jQuery('html, body').animate({
                    scrollTop: jQuery("#licontainer_"+id).offset().top
                }, 1000);

            });
        }
    });
    jQuery(".field_row_main ul.core").sortable({
        opacity: 0.8,
        cursor: 'move',
        placeholder: "ui-state-highlight",
        cancel: "input,label,select",
        update: function () {
            var manage_field_type = jQuery(this).closest('#uwp-selected-fields').find(".manage_field_type").val();
            var order = jQuery(this).sortable("serialize") + '&update=update&manage_field_type=' + manage_field_type;
            if (manage_field_type == 'custom_fields' || manage_field_type == 'sorting_options') {
                jQuery.get(uwp_admin_ajax.url + '?action=uwp_ajax_action&create_field=true', order, function (theResponse) {
                    //alert('Fields have been ordered.');
                });
            }
        }
    });

    jQuery(".field_row_main ul.uwp_form_extras").sortable({ opacity: 0.8, placeholder: "ui-state-highlight",
        cancel: "input,label,select",cursor: 'move', update: function() {

            var order = jQuery(this).sortable("serialize") + '&update=update';

            jQuery.get(uwp_admin_ajax.url+'?action=uwp_ajax_register_action&create_field=true', order, function(theResponse){

            });
        }
    });

    jQuery("#uwp-form-builder-tab").find("ul li a").click(function() {
        if(!jQuery(this).attr('id')){return;}
        var htmlvar_name = jQuery(this).attr('id').replace('uwp-','');

        var form_type = jQuery(this).closest('#uwp-form-builder-tab').find('#form_type').val();

        var id = 'new'+jQuery(".field_row_main ul.uwp_form_extras li:last").index();

        var manage_field_type = jQuery(this).closest('#uwp-available-fields').find(".manage_field_type").val();

        if(manage_field_type == 'register'){

            jQuery.get(uwp_admin_ajax.url+'?action=uwp_ajax_register_action&create_field=true',{ htmlvar_name: htmlvar_name,form_type:form_type, field_id: id, field_ins_upd: 'new' },
                function(data)
                {
                    console.log(id);
                    jQuery('.field_row_main ul.uwp_form_extras').append(data);

                    jQuery('#licontainer_'+htmlvar_name).find('#sort_order').val( parseInt(jQuery('#licontainer_'+htmlvar_name).index()) + 1 );

                    show_hide('field_frm'+htmlvar_name);
                    jQuery('html, body').animate({
                        scrollTop: jQuery("#licontainer_"+htmlvar_name).offset().top
                    }, 1000);

                });

            if(htmlvar_name!='fieldset'){
                jQuery(this).closest('li').hide();
            }

        }

    });
});

function save_field(id) {

    if (jQuery('#licontainer_' + id + ' #htmlvar_name').length > 0) {

        var htmlvar_name = jQuery('#licontainer_' + id + ' #htmlvar_name').val();

        if (htmlvar_name == '') {

            alert(uwp_admin_ajax.custom_field_not_blank_var);

            return false;
        }

        if (htmlvar_name != '') {

            var iChars = "!`@#$%^&*()+=-[]\\\';,./{}|\":<>?~ ";

            for (var i = 0; i < htmlvar_name.length; i++) {
                if (iChars.indexOf(htmlvar_name.charAt(i)) != -1) {

                    alert(uwp_admin_ajax.custom_field_not_special_char);


                    return false;
                }
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
            if (jQuery.trim(result) == 'HTML Variable Name should be a unique name') {

                alert(uwp_admin_ajax.custom_field_unique_name);

            }
            else {
                jQuery('#licontainer_' + id).replaceWith(jQuery.trim(result));

                var order = jQuery(".field_row_main ul.core").sortable("serialize") + '&update=update&manage_field_type=custom_fields';

                jQuery.get(uwp_admin_ajax.url + '?action=uwp_ajax_action&create_field=true', order,
                    function (theResponse) {
                        //alert(theResponse);
                    });

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

function show_hide_register(id)
{
    jQuery('#'+id).toggle();
}

function delete_register_field(id, nonce,deleteid)
{

    var restore_id = id.replace('new','');

    var confirmation = confirm(uwp_admin_ajax.custom_field_delete);

    if(confirmation == true)
    {
        jQuery('#create_advance_search_li_'+deleteid).show();
        jQuery.get(uwp_admin_ajax.url+'?action=uwp_ajax_register_action&create_field=true', { field_id: id, field_ins_upd: 'delete', _wpnonce:nonce },
            function(data)
            {
                jQuery('#licontainer_'+id).remove();

            });

        jQuery('#uwp-'+deleteid).closest('li').show();

    }

}

function save_register_field(id)
{
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
        'url': uwp_admin_ajax.url+'?action=uwp_ajax_register_action',
        'type': 'POST',
        'data':  request_data ,
        'success': function(result){


            if(jQuery.trim( result ) == 'HTML Variable Name should be a unique name')
            {

                alert(uwp_admin_ajax.custom_field_unique_name);

            }
            else
            {
                jQuery('#licontainer_'+id).replaceWith(jQuery.trim( result ));

                var order = jQuery(".field_row_main ul.uwp_form_extras").sortable("serialize") + '&update=update';

                jQuery.get(uwp_admin_ajax.url+'?action=uwp_ajax_register_action&create_field=true', order,
                    function(theResponse){
                        //alert(theResponse);
                    });

                jQuery('.field_frm').hide();
            }


        }
    });


}