jQuery(document).ready(function () {

    jQuery("#uwp-form-builder-tab-existing, #uwp-form-builder-tab, #uwp-form-builder-tab-predefined, #uwp-form-builder-tab-custom").find("ul li a").click(function() {
        if(!jQuery(this).attr('id')){return;}
        var htmlvar_name = jQuery(this).attr('id').replace('uwp-','');
        var htmlvar = htmlvar_name;
        var field_type = jQuery(this).data('field-type');
        var type_key = jQuery(this).data("field-type-key");
        var form_type = jQuery(this).closest('#uwp-form-builder-tab, #uwp-form-builder-tab-predefined').find('#form_type').val();
        var id = 'new'+jQuery(".field_row_main ul.core").children('li:last-child').index() + 1;
        var manage_field_type = jQuery(this).closest('#uwp-available-fields').find(".manage_field_type").val();
        var field_data_type = jQuery(this).data('data_type');
        var custom_type = jQuery(this).data("field-custom-type");
        var form_id = jQuery('.manage_field_form_id').val();
        var _nonce = jQuery('#uwp-admin-settings').val();

        var data = {
            'htmlvar_name': htmlvar_name,
            'field_type':field_type,
            'field_type_key': type_key,
            'form_type':form_type,
            'field_id': id,
            'field_ins_upd': 'new',
            'manage_field_type': manage_field_type,
            'field_data_type':field_data_type,
            'custom_type': custom_type,
            'form_id': form_id,
            '_wpnonce': _nonce

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
                'user_decided':      jQuery(this).data('user_decided'),
                'form_id': form_id,
                '_wpnonce': _nonce
            };
        } else if(manage_field_type == 'user_sorting'){
            var action = 'uwp_ajax_user_sorting_action';
            data = {
                'htmlvar_name':      htmlvar_name,
                'form_type':         form_type,
                'field_type':        jQuery(this).data('field_type'),
                'field_ins_upd':     'new',
                'data_type':         jQuery(this).data('data_type'),
                'tab_level':         jQuery(this).data('tab_level'),
                'tab_parent':        jQuery(this).data('tab_parent'),
                'field_icon':        jQuery(this).data('field_icon'),
                'site_title':        jQuery(this).data('site_title'),
                'sort':              jQuery(this).data('sort'),
                '_wpnonce': _nonce
            };
        }else { //custom field
            var action = "uwp_ajax_action";
            htmlvar_name = id;
        }

        jQuery.get(uwp_admin_ajax.url+'?action=' + action + '&create_field=true', data ,
            function(data)
            {
                console.log(id);
                jQuery('.field_row_main ul.core').append(data);
                jQuery('#licontainer_'+htmlvar_name).find('#sort_order').val( parseInt(jQuery('#licontainer_'+htmlvar_name).index()) + 1 );
                uwp_show_hide(jQuery("#licontainer_"+htmlvar_name).find('.toggle-arrow'));
                aui_init_select2();
                uwp_init_tooltips();
                jQuery('html, body').animate({
                    scrollTop: jQuery("#licontainer_"+htmlvar_name).offset().top
                }, 1000);

                if (manage_field_type == 'register') {
                    save_field(htmlvar_name, 'register'); // save registration fields on add
                }

                if (manage_field_type == 'search') {
                    save_search_field(htmlvar_name); // save search fields on add
                }

            });

        if(jQuery('#uwp-form-builder-tab-existing #uwp-' + htmlvar).length > 0){
            jQuery('#uwp-form-builder-tab-existing #uwp-' + htmlvar).closest('li').hide();
        }

        if(htmlvar_name!='fieldset' && (manage_field_type == 'register' || manage_field_type == 'search') ){
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

            form_id = jQuery('.manage_field_form_id').val();
            form_id_param = '&form_id=' + form_id;

            if (manage_field_type == 'register'){
                var action = "uwp_ajax_register_action";
            } else if (manage_field_type == 'search') {
                var action = "uwp_ajax_search_action";
            } else {
                var action = "uwp_ajax_action";
            }

            jQuery.get(uwp_admin_ajax.url + '?action='+ action +'&create_field=true', order + form_id_param, function (theResponse) {
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
            var form_id = jQuery('.manage_field_form_id').val();

            jQuery.each($tabs, function( index, tab ) {
                if(tab.id){
                    $order[index] = {id:tab.id, tab_level: tab.depth,tab_parent: tab.parent_id};
                }
            });

            if (manage_field_type == 'user_sorting') {
                var action = "uwp_ajax_user_sorting_action";
            } else {
                var action = "uwp_ajax_profile_tabs_action";
            }

            var data = {
                'tabs': $order,
                'form_id': form_id,
                '_wpnonce': jQuery('#uwp-admin-settings').val()
            };

            jQuery.get(uwp_admin_ajax.url + '?action='+ action +'&create_field=true&update=update&manage_field_type=' + manage_field_type, data, function (theResponse) {

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

            if (jQuery('#licontainer_' + cont).find(".uwp-price-extra-set input[name='extra[is_price]']:checked").val() == '1') {
                jQuery('#licontainer_' + cont).find('.uwp-price-extra').show();
            }

        } else {
            jQuery('#licontainer_' + cont).find('.uwp-price-extra-set').hide();
            jQuery('#licontainer_' + cont).find('.uwp-price-extra').hide();
        }
    }
}

function save_field(id, type) {

    form_id = jQuery('.manage_field_form_id').val();
    form_id_param = '&form_id=' + form_id;
    if('profile_tab' == type){
        var action = 'uwp_ajax_profile_tabs_action';
        var manage_field_type = 'profile_tab';
    } else if('register' == type){
        var action = 'uwp_ajax_register_action';
        var manage_field_type = 'register';
    } else if('user_sorting' == type){
        var action = 'uwp_ajax_user_sorting_action';
        var manage_field_type = 'user_sorting';
    } else {
        var action = 'uwp_ajax_action';
        var manage_field_type = 'custom_fields';
    }

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
        'url': uwp_admin_ajax.url + '?action=' + action + '&manage_field_type=' + manage_field_type + form_id_param,
        'type': 'POST',
        'data': request_data,
        'success': function (result) {

            if (jQuery.trim(result) == 'invalid_key') {
                alert(uwp_admin_ajax.custom_field_unique_name);
            } else {
                jQuery('#licontainer_' + id).replaceWith(jQuery.trim(result));

                aui_init_select2();
                uwp_init_tooltips();

                if('profile_tab' == type){
                    var $tabs = jQuery('.field_row_main ul.core').nestedSortable('toArray', {startDepthCount: 0});
                    var $order = {};
                    jQuery.each($tabs, function( index, tab ) {
                        if(tab.id){
                            $order[index] = {id:tab.id, tab_level: tab.depth,tab_parent: tab.parent_id};
                        }
                    });

                    var data = {
                        'tabs': $order,
                        '_wpnonce': jQuery('#uwp-admin-settings').val()
                    };

                    jQuery.get(uwp_admin_ajax.url + '?action='+ action +'&create_field=true&update=update&manage_field_type=' + manage_field_type, data, function (theResponse) {

                    });

                } else {
                    var order = jQuery(".field_row_main ul.core").sortable("serialize") + '&update=update&manage_field_type='+manage_field_type+ form_id_param;

                    jQuery.get(uwp_admin_ajax.url+'?action=' + action + '&create_field=true', order,
                        function (theResponse) {

                        });
                }

                jQuery('.field_frm').hide();
            }

        }
    });
}

function delete_field(id, nonce, deleteid, type) {

    form_id = jQuery('.manage_field_form_id').val();
    form_id_param = '&form_id=' + form_id;

    if('profile_tab' == type){
        var action = 'uwp_ajax_profile_tabs_action';
        var manage_field_type = 'profile_tab';
    } else if('register' == type){
        var action = 'uwp_ajax_register_action';
        var manage_field_type = 'register';
    }else if('user_sorting' == type){
        var action = 'uwp_ajax_user_sorting_action';
        var manage_field_type = 'user_sorting';
    } else {
        var action = 'uwp_ajax_action';
        var manage_field_type = 'custom_fields';
    }

    var confirmation = confirm(uwp_admin_ajax.custom_field_delete);

    if (confirmation == true) {

        if (id.substring(0, 3) == "new") {
            jQuery('#licontainer_' + id).remove();
        } else {
            jQuery.get(uwp_admin_ajax.url+'?action='+action+'&create_field=true&manage_field_type=' + manage_field_type + form_id_param, {
                    field_id: id,
                    form_id: form_id,
                    field_ins_upd: 'delete',
                    _wpnonce: nonce
                },
                function () {
                    jQuery('#licontainer_' + id).remove();

                });

            jQuery('#uwp-'+deleteid).closest('li').show();
        }

    }

}