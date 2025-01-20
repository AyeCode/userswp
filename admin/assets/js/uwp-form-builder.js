(function ($) {
    'use strict';

    window.UWP = window.UWP || {};

    UWP.Form_Builder = {

        actionMap: {
            register: "uwp_ajax_register_action",
            profile_tabs: "uwp_ajax_profile_tabs_action",
            profile_tab: "uwp_ajax_profile_tabs_action",
            user_sorting: "uwp_ajax_user_sorting_action",
        },

        /**
         * Initialize the admin functionality
         */
        init: function () {
            this.initFieldAddition();
            this.initFieldSorting();
        },

        /**
         * Initialize the field addition functionality
         */
        initFieldAddition: function () {
            const self = this;
            const $tabs = $("#uwp-form-builder-tab-existing, #uwp-form-builder-tab, #uwp-form-builder-tab-predefined, #uwp-form-builder-tab-custom");

            $tabs.find("ul li a").on('click', (e) => {
                e.preventDefault();
                const $element = $(e.currentTarget);
                if (!$element.attr('id')) {
                    return;
                }

                let htmlvarName = $element.attr('id').replace('uwp-', '');
                const fieldType = $element.data('field-type');
                const typeKey = $element.data("field-type-key");
                const formType = $element.closest('#uwp-form-builder-tab, #uwp-form-builder-tab-predefined').find('#form_type').val();
                const id = 'new' + $(".field_row_main ul.core").children('li:last-child').index() + 1;
                const manageFieldType = $element.closest('#uwp-available-fields').find(".manage_field_type").val();
                const fieldDataType = $element.data('data_type');
                const customType = $element.data("field-custom-type");
                const formId = $('[name="manage_field_form_id"]').val();
                const nonce = $('[name="_wpnonce"]').val();

                let data = {
                    'htmlvar_name': htmlvarName,
                    'field_type': fieldType,
                    'field_type_key': typeKey,
                    'form_type': formType,
                    'field_id': id,
                    'field_ins_upd': 'new',
                    'manage_field_type': manageFieldType,
                    'field_data_type': fieldDataType,
                    'custom_type': customType,
                    'form_id': formId,
                    '_wpnonce': nonce
                };

                if (manageFieldType === 'profile_tabs') {
                    data = {
                        ...data,
                        'tab_layout': $element.data('tab_layout'),
                        'tab_level': $element.data('tab_level'),
                        'tab_parent': $element.data('tab_parent'),
                        'tab_name': $element.data('tab_name'),
                        'tab_type': $element.data('tab_type'),
                        'tab_icon': $element.data('tab_icon'),
                        'tab_key': $element.data('tab_key'),
                        'tab_content': $element.data('tab_content'),
                        'tab_privacy': $element.data('tab_privacy'),
                        'user_decided': $element.data('user_decided'),
                    };
                } else if (manageFieldType === 'user_sorting') {
                    data = {
                        ...data,
                        'data_type': $element.data('data_type'),
                        'tab_level': $element.data('tab_level'),
                        'tab_parent': $element.data('tab_parent'),
                        'field_icon': $element.data('field_icon'),
                        'site_title': $element.data('site_title'),
                        'sort': $element.data('sort'),
                    };
                }

                const actionType = self.getActionType(manageFieldType);
                const action = actionType.action;

                self.addNewField($element, action, data, htmlvarName, manageFieldType);
            });
        },

        /**
         * Get the action type based on the manage field type
         *
         * @param {string} type - The manage field type
         * @returns {string} The action type
         */
        getActionType: function (type) {
            if (this.actionMap[type]) {
                return {
                    fieldType: type,
                    action: this.actionMap[type]
                };
            }

            var formBuilderEvent = $.Event('uwp_resolve_form_builder_action');
            formBuilderEvent.fieldType = type;
            formBuilderEvent.manageFieldType = "custom_fields";
            formBuilderEvent.actionType = "uwp_ajax_action";

            $(document).trigger(formBuilderEvent);

            return {
                fieldType: formBuilderEvent.manageFieldType,
                action: formBuilderEvent.actionType
            };
        },

        /**
         * Add a new field to the form
         *
         * @param {HTMLElement} element - The clicked element
         * @param {string} action - The action type
         * @param {Object} data - The field data
         * @param {string} htmlvarName - The HTML variable name
         * @param {string} manageFieldType - The manage field type
         */
        addNewField: function (element, action, data, htmlvarName, manageFieldType) {
            $(window).trigger('uwp_form_builder_before_add_field', [htmlvarName, data]);

            $.get(uwp_admin_ajax.url + '?action=' + action + '&create_field=true', data,
                function (response) {
                    $('.field_row_main ul.core').append(response);
                    $(`#licontainer_${htmlvarName}`).find('#sort_order').val(parseInt($(`#licontainer_${htmlvarName}`).index()) + 1);

                    let $liContainer = $(`#licontainer_${htmlvarName}`);

                    if (!$liContainer.length) {
                        $liContainer = $(`#licontainer_${data.field_id}`);
                    }

                    if ($liContainer.length) {
                        if (manageFieldType !== 'register') {
                            UWP.Form_Builder.initTabSettings($liContainer.find('.uwp-fieldset'));
                        }

                        $('html, body').animate({
                            scrollTop: $liContainer.offset().top,
                        }, 1000);
                    }

                    if (manageFieldType === 'register') {
                        UWP.Form_Builder.saveField(htmlvarName, 'register');
                    }

                    $(window).trigger('uwp_form_builder_after_add_field', [manageFieldType, htmlvarName, data]);
                });

            if ($('#uwp-form-builder-tab-existing #uwp-' + htmlvarName).length > 0) {
                $('#uwp-form-builder-tab-existing #uwp-' + htmlvarName).closest('li').hide();
            }

            if (htmlvarName !== 'fieldset' && (manageFieldType === 'register' || manageFieldType === 'search')) {
                element.closest('li').hide();
            }
        },

        /**
         * Initialize the field sorting functionality
         */
        initFieldSorting: function () {
            $("ul.uwp-tabs-selected").sortable({
                opacity: 0.8,
                cursor: 'move',
                placeholder: "ui-state-highlight",
                cancel: "input,label,select",
                update: function () {
                    UWP.Form_Builder.updateFieldOrder($(this));
                    console.log('Fields have been ordered.');
                    aui_toast('uwp_tabs_reorder_tab_success', 'success', uwp_admin_ajax.txt_saved);
                }
            });

            $('ul.uwp-profile-tabs-selected').nestedSortable({
                maxLevels: 2,
                handle: '.uwp-fieldset',
                items: 'li',
                disableNestingClass: 'mjs-nestedSortable-no-nesting',
                helper: 'clone',
                placeholder: 'ui-state-highlight',
                forcePlaceholderSize: true,
                listType: 'ul',
                update: function () {
                    UWP.Form_Builder.updateTabOrder($(this));
                }
            });
        },

        /**
         * Update the field order after sorting
         *
         * @param {jQuery} $sortable - The sortable element
         */
        updateFieldOrder: function ($sortable) {
            const manageFieldType = $sortable.closest('#uwp-selected-fields').find(".manage_field_type").val();
            const order = $sortable.sortable("serialize") + '&update=update&manage_field_type=' + manageFieldType;
            const formId = $('[name="manage_field_form_id"]').val();
            const formIdParam = '&form_id=' + formId;
            const actionType = UWP.Form_Builder.getActionType(manageFieldType);
            const action = actionType.action;

            $.get(uwp_admin_ajax.url + '?action=' + action + '&create_field=true', order + formIdParam, function () { });
        },

        /**
         * Update the tab order after sorting
         *
         * @param {jQuery} $sortable - The sortable element
         */
        updateTabOrder: function ($sortable) {
            const manageFieldType = $sortable.closest('#uwp-selected-fields').find(".manage_field_type").val();
            const $tabs = $('.field_row_main ul.core').nestedSortable('toArray', {
                startDepthCount: 0
            });
            const $order = {};
            const formId = $('[name="manage_field_form_id"]').val();

            $.each($tabs, function (index, tab) {
                if (tab.id) {
                    $order[index] = {
                        id: tab.id,
                        tab_level: tab.depth,
                        tab_parent: tab.parent_id
                    };
                }
            });

            const actionType = UWP.Form_Builder.getActionType(manageFieldType);
            const action = actionType.action;

            const data = {
                'tabs': $order,
                'form_id': formId,
                '_wpnonce': $('[name="_wpnonce"]').val()
            };

            $.get(uwp_admin_ajax.url + '?action=' + action + '&create_field=true&update=update&manage_field_type=' + manageFieldType, data, function () { });
        },

        /**
         * Save a field
         *
         * @param {string} id - The field ID
         * @param {string} type - The field type
         */
        saveField: function (id, type) {
            const formId = $('[name="manage_field_form_id"]').val();
            const formIdParam = '&form_id=' + formId;
            const actionType = UWP.Form_Builder.getActionType(type);
            const action = actionType.action;
            const manageFieldType = actionType.fieldType;

            if ($('.uwp-form-settings-form #htmlvar_name').length > 0) {
                const htmlvarName = $('.uwp-form-settings-form  #htmlvar_name').val();
                if (htmlvarName !== '') {
                    const iChars = "!`@#$%^&*()+=-[]\\\';,./{}|\":<>?~ ";
                    for (let i = 0; i < htmlvarName.length; i++) {
                        if (iChars.indexOf(htmlvarName.charAt(i)) !== -1) {
                            alert(uwp_admin_ajax.custom_field_not_special_char);
                            return false;
                        }
                    }
                }
                const optionValInput = $('.uwp-form-settings-form #option_values');
                if (optionValInput.length === 1 && optionValInput.val() === '') {
                    alert(uwp_admin_ajax.custom_field_options_not_blank_var);
                    return false;
                }
            }

            const requestData = type === 'register' ?
                $(`#licontainer_${id} form`).serializeObject() :
                $('.uwp-form-settings-form').serializeObject();

            requestData['create_field'] = true;
            requestData['field_ins_upd'] = 'submit';

            $.post({
                'url': `${uwp_admin_ajax.url}?action=${action}&manage_field_type=${manageFieldType}${formIdParam}`,
                'data': requestData,
                'beforeSend': function () {
                    $('.uwp-form-settings-form #save').html('<span class="spinner-border spinner-border-sm" role="status"></span> ' + uwp_admin_ajax.txt_saving).addClass('disabled');
                },
                'success': function (result) {
                    if ($.trim(result) === 'invalid_key') {
                        $('.uwp-form-settings-form #save').html(uwp_admin_ajax.txt_save).removeClass('disabled');
                        alert(uwp_admin_ajax.custom_field_unique_name);
                    } else {
                        $(`#licontainer_${id}`).replaceWith($.trim(result));
                        aui_toast('uwp_tabs_save_tab_success', 'success', uwp_admin_ajax.txt_saved);

                        if (type === 'profile_tab') {
                            UWP.Form_Builder.updateTabOrder($('.field_row_main ul.core'));
                        } else {
                            UWP.Form_Builder.updateFieldOrder($(".field_row_main ul.core"));
                        }

                        UWP.Form_Builder.closeTabSettings();
                        aui_init();
                    }
                }
            });
        },

        /**
         * Delete a field
         *
         * @param {string} id - The field ID
         * @param {string} nonce - The security nonce
         * @param {string} deleteId - The ID of the element to show after deletion
         * @param {string} type - The field type
         */
        deleteField: function (id, nonce, deleteId, type) {
            const formId = $('[name="manage_field_form_id"]').val();
            const actionType = UWP.Form_Builder.getActionType(type);

            aui_confirm(uwp_admin_ajax.custom_field_delete, uwp_admin_ajax.txt_delete, uwp_admin_ajax.txt_cancel, true).then(function (confirmed) {
                if (confirmed) {
                    if (id.substring(0, 3) === "new") {
                        $(`#licontainer_${id}`).remove();
                    } else {
                        $.get(`${uwp_admin_ajax.url}`, {
                            action: actionType.action,
                            create_field: true,
                            field_ins_upd: 'delete',
                            manage_field_type: actionType.fieldType,
                            field_id: id,
                            form_id: formId,
                            _wpnonce: nonce
                        },
                            function () {
                                $(`#licontainer_${id}`).remove();
                            });
                        $(`#uwp-${deleteId}`).closest('li').show();
                    }

                    aui_toast('uwp_tabs_delete_success', 'success', uwp_admin_ajax.txt_deleted);

                    if ($('#uwp-field-settings:visible button.btn-close').length) {
                        $('#uwp-field-settings:visible button.btn-close').trigger("click");
                    }

                    if ($(`#uwp-form-builder-tab-existing #uwp-${deleteId}`).length > 0) {
                        $(`#uwp-form-builder-tab-existing #uwp-${deleteId}`).closest('li').hide();
                    }
                }
            });
        },

        /**
         * Initialize the tab settings
         *
         * @param {jQuery} $element - The element to initialize settings for
         */
        initTabSettings: function ($element) {
            // Close any open settings first.
            if ($('#licontainer_').length && $($element).parent().attr("id") != 'licontainer_') {
                $('#licontainer_').remove();
            } else if ($('#licontainer_new-1').length && $($element).parent().attr("id") != 'licontainer_new-1') {
                $('#licontainer_new-1').remove();
            }

            let $settings = $($element).parent().find('.dd-setting').first().html();
            $settings = jQuery('<div class="dd-setting">' + $settings + '</div>');
            $settings.removeClass('d-none');

            const $id = $settings.find('[name="id"]').val();
            const $type = $settings.find('[name="tab_type"]').val();

            if ($($element).closest('ul').hasClass('dd-list') || $type == 'fieldset') {
                $settings.find('.alert-info').addClass('d-none');
            } else {
                $settings.find(`[data-argument="gd-tab-name-${$id}"],[data-argument="gd-tab-icon-${$id}"]`).addClass('d-none');
            }

            $('#uwp-form-builder-tab-selected .dd-form').removeClass('border-width-2 border-primary');
            $($element).parent().find('.dd-form').first().addClass('border-width-2 border-primary');
            $('#uwp-field-settings .card-body').html($settings);
            $('#uwp-field-settings .card-body').find('.iconpicker-input').removeClass('iconpicker-input');
            $('#uwp-field-settings-tab').tab('show');
            $('#uwp-field-settings .card-footer').html('');
            $('#uwp-field-settings .uwp-tab-actions').detach().appendTo('#uwp-field-settings .card-footer');

            UWP.Admin.initAdvancedSettings();
            aui_init();

            // Conditional Fields on change
            $(".uwp-form-settings-form").off('change').on("change", function () {
                try {
                    aui_conditional_fields('.uwp-form-settings-form');
                } catch (err) {
                    console.log(err.message);
                }
            });

            // Conditional Fields on load
            try {
                aui_conditional_fields(".uwp-form-settings-form");
            } catch (err) {
                console.log(err.message);
            }
        },

        /**
         * Close the tab settings
         */
        closeTabSettings: function () {
            $('#uwp-fields-tab').tab('show');
            $('#uwp-selected-fields .dd-form').removeClass('border-width-2 border-primary');

            // If not saved then remove
            const $id = $('#uwp-field-settings').find('[name="id"],[name="field_id"]').val();
            if (!$id || !$.isNumeric($id)) {
                $(`#licontainer_,#licontainer_${$id}`).remove();
            }
        },

        /**
         * Handle changes in the data type
         *
         * @param {HTMLElement} obj - The changed element
         * @param {string} cont - The container ID
         */
        dataTypeChanged: function (obj, cont) {
            if (obj && cont) {
                $(`#licontainer_${cont}`).find('.decimal-point-wrapper').hide();
                if ($(obj).val() == 'FLOAT') {
                    $(`#licontainer_${cont}`).find('.decimal-point-wrapper').show();
                }
                if ($(obj).val() == 'FLOAT' || $(obj).val() == 'INT') {
                    $(`#licontainer_${cont}`).find('.uwp-price-extra-set').show();
                    if ($(`#licontainer_${cont}`).find(".uwp-price-extra-set input[name='extra[is_price]']:checked").val() == '1') {
                        $(`#licontainer_${cont}`).find('.uwp-price-extra').show();
                    }
                } else {
                    $(`#licontainer_${cont}`).find('.uwp-price-extra-set').hide();
                    $(`#licontainer_${cont}`).find('.uwp-price-extra').hide();
                }
            }
        },
    };

    // Global function mappings for backward compatibility
    window.uwp_data_type_changed = UWP.Form_Builder.dataTypeChanged;
    window.save_field = UWP.Form_Builder.saveField;
    window.delete_field = UWP.Form_Builder.deleteField;
    window.uwp_tabs_close_settings = UWP.Form_Builder.closeTabSettings;
    window.uwp_tabs_item_settings = UWP.Form_Builder.initTabSettings;

    // Initialize when document is ready
    $(document).ready(function () {
        UWP.Form_Builder.init();
    });

    $.fn.serializeObject = function () {
        var o = {};
        var a = this.serializeArray();
        $.each(a, function () {
            if (o[this.name]) {
                if (!o[this.name].push) {
                    o[this.name] = [o[this.name]];
                }

                o[this.name].push(this.value || '');
            } else {
                o[this.name] = this.value || '';
            }
        });
        return o;
    };

})(jQuery);