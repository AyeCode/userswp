(function ($) {
    'use strict';

    window.UWP = window.UWP || {};

    UWP.Admin = {
        mediaFrames: [],

        init: function () {
            this.initColorPicker();
            this.initImageUploader();
            this.initFormHandlers();
            this.initMiscHandlers();
            this.initTooltips();
            this.initFormControls();
            this.initUserTypesReorder();
        },

        /**
         * Toggle button loading state
         * 
         * @param {jQuery} element Button element
         * @param {string} handle State to set ('loading' or 'reset')
         */
        buttonStatus: function (element, handle) {
            if (handle === "loading") {
                element.data('text', element.html());
                element.prop('disabled', true);
                element.html('<i class="fas fa-circle-notch fa-spin ml-2"></i> <span>Loading...</span>');
            } else {
                element.prop('disabled', false);
                element.html(element.data('text'));
            }
        },

        /**
         * Initialize color picker functionality
         */
        initColorPicker: function () {
            const $colorPicker = $('.uwp-color-picker');
            if ($colorPicker.length) {
                $colorPicker.wpColorPicker();
            }
        },

        /**
         * Initialize image uploader functionality
         */
        initImageUploader: function () {
            const self = this;

            // Initialize remove button visibility
            $('.uwp-upload-img').each(function () {
                const $wrap = $(this);
                const field = $wrap.data('field');
                if ($(`[name="${field}[id]"]`).length && !$(`[name="${field}[id]"]`).val()) {
                    $('.uwp_remove_image_button', $wrap).hide();
                }
            });

            // Upload button handler
            $(document).on('click', '.uwp_upload_image_button', function (e) {
                e.preventDefault();
                self.handleImageUpload($(this));
            });

            // Remove button handler
            $(document).on('click', '.uwp_remove_image_button', function () {
                self.handleImageRemove($(this));
                return false;
            });
        },

        /**
         * Handle image upload process
         * @param {jQuery} $button Upload button element
         */
        handleImageUpload: function ($button) {
            const $wrap = $button.closest('.uwp-upload-img');
            const field = $wrap.data('field');

            if (!field) return;

            if (this.mediaFrames[field]) {
                this.mediaFrames[field].open();
                return;
            }

            this.mediaFrames[field] = wp.media({
                title: uwp_admin_ajax.txt_choose_image,
                button: {
                    text: uwp_admin_ajax.txt_use_image
                },
                multiple: false
            });

            this.mediaFrames[field].on('select', function () {
                const attachment = this.mediaFrames[field].state().get('selection').first().toJSON();
                const thumbnail = attachment.sizes.medium || attachment.sizes.full;

                if (field) {
                    $(`[name="${field}[id]"]`).val(attachment.id);
                    $(`[name="${field}[src]"]`).val(attachment.url);
                    $(`[name="${field}"]`).val(attachment.id);
                }

                $wrap.closest('.form-field.form-invalid').removeClass('form-invalid');
                $('.uwp-upload-display', $wrap).find('img')
                    .attr('src', thumbnail.url);
                $('.uwp_remove_image_button').show();
            }.bind(this));

            this.mediaFrames[field].open();
        },

        /**
         * Handle image removal
         * @param {jQuery} $button Remove button element
         */
        handleImageRemove: function ($button) {
            const $wrap = $button.closest('.uwp-upload-img');
            const field = $wrap.data('field');

            $('.uwp-upload-display', $wrap).find('img')
                .attr('src', uwp_admin_ajax.img_spacer)
                .removeAttr('width height sizes alt class srcset');

            if (field) {
                $(`[name="${field}[id]"]`).val('');
                $(`[name="${field}[src]"]`).val('');
                $(`[name="${field}"]`).val('');
            }

            $button.hide();
        },

        /**
         * Initialize form related handlers
         */
        initFormHandlers: function () {
            const self = this;

            // Handle large text inputs
            $('.userswp .forminp .large-text').on({
                focus: function () {
                    const $this = $(this);
                    const placeholder = $this.attr('placeholder');
                    if ($this.val() === '') {
                        $this.val(placeholder);
                    }
                },
                blur: function () {
                    const $this = $(this);
                    const placeholder = $this.attr('placeholder');
                    if ($this.val() === placeholder) {
                        $this.val('');
                    }
                }
            });

            // Handle register form submission
            $('#uwp_user_type_form').on('submit', function (e) {
                self.handleRegisterFormSubmit(e, $(this));
            });

            // Handle register form removal
            $(document).on('click', '.register-form-remove', function (e) {
                self.handleRegisterFormRemove($(this));
            });
        },

        /**
         * Handle register form submission
         * @param {Event} e Submit event
         * @param {jQuery} $form Form element
         */
        handleRegisterFormSubmit: function (e, $form) {
            e.preventDefault();
            const { __ } = wp.i18n;
            const self = this;
            const error = $form.find('.alert.alert-danger');
            const success = $form.find('.alert.alert-success');
            const action = $form.find('[name="action"]').val() || 'edit';
            const ajaxAction = action === 'edit' ? 'uwp_ajax_update_register' : 'uwp_ajax_create_register';
            const data = $form.serialize() + `&action=${ajaxAction}&type=update`;
            const $button = $("button[type=submit]", $form);

            const formTitleInput = $form.find('[name="form_title"]');

            formTitleInput.on('input', function () {
                if ($(this).hasClass('is-invalid')) {
                    $(this).removeClass('is-invalid');
                }
            });

            if (formTitleInput.val() === '') {
                formTitleInput.addClass('is-invalid');
                formTitleInput.next('.invalid-feedback').text(__('Form title is required.', 'userswp'));
                return;
            } else {
                formTitleInput.removeClass('is-invalid');
            }

            self.buttonStatus($button, 'loading');

            $.post(uwp_admin_ajax.url, data, (response) => {
                self.buttonStatus($button, 'reset');

                if (response.success === false) {
                    this.handleError(error, success, response.data.message);
                } else if (response.success) {
                    this.handleSuccess(error, success, response.data.message);

                    if (response.data.redirect) {
                        setTimeout(() => {
                            window.location.replace(response.data.redirect);
                        }, 1000);
                    }
                }
            }, "json")
                .fail(() => {
                    self.buttonStatus($button, "reset");
                    self.handleError(error, success, __('There is something that went wrong!', 'userswp'));
                });
        },

        /**
         * Handle error response.
         * 
         * @param {jQuery} error Error element
         * @param {jQuery} success Success element
         * @param {string} message Error message
         */
        handleError: function (error, success, message) {
            if (success.is(":visible")) success.addClass('d-none');
            error.html(message).removeClass('d-none').slideDown();
        },

        /**
         * Handle success response.
         * 
         * @param {jQuery} error Error element
         * @param {jQuery} success Success element
         * @param {string} message Error message
         */
        handleSuccess: function (error, success, message) {
            if (error.is(":visible")) error.addClass('d-none');
            success.html(message).removeClass('d-none').slideDown();
        },

        /**
         * Handle register form removal
         * @param {jQuery} $button Remove button element
         */
        handleRegisterFormRemove: function ($button) {
            const self = this;
            const formId = $button.attr('data-id');

            self.buttonStatus($button, 'loading');

            const confirmation = confirm(uwp_admin_ajax.delete_register_form);

            if (confirmation) {
                const data = {
                    'action': 'uwp_ajax_remove_user_type',
                    'type': 'remove',
                    'form_id': formId,
                    'nonce': uwp_admin_ajax.nonces.uwp_delete_user_type
                };

                $.post(uwp_admin_ajax.url, data, function (response) {
                    if (response.status) {
                        window.location.replace(response.redirect);
                    }

                    self.buttonStatus($button, 'reset');
                });
            } else {
                self.buttonStatus($button, 'reset');
            }
        },

        /**
         * Initialize miscellaneous handlers
         */
        initMiscHandlers: function () {
            // Code copy handler
            $(document).on('click', '.userswp span code', function () {
                const $code = $(this);
                $('.userswp span code').removeClass('uwp-tag-copied').find('span').remove();
                $code.addClass('uwp-tag-copied').append('<span></span>');

                const $temp = $("<input>");
                $("body").append($temp);
                $temp.val($code.text()).select();
                document.execCommand("copy");
                $temp.remove();

                setTimeout(function () {
                    $('.userswp span code').removeClass('uwp-tag-copied').find('span').remove();
                }, 1000);
            });

            // SEO meta separator handler
            $('input.uwp-seo-meta-separator').on('change', function () {
                if ($(this).attr("checked") === "checked") {
                    $('input.uwp-seo-meta-separator').parent().removeClass('active');
                    $(this).parent().addClass('active');
                }
            }).change();

            // Font Awesome select handler
            $(".aui-fa-select2").select2({
                templateResult: this.formatFaSelect,
                templateSelection: this.formatFaSelection,
                escapeMarkup: function (m) {
                    return m;
                }
            });

            // Register options toggle
            $(document).on('click', '.register-show-options', function () {
                $("#uwp-form-more-options").toggle("fast");
            });
        },

        /**
         * Initialize user types reordering functionality
         */
        initUserTypesReorder: function () {
            const table = $('.wp-list-table.usertypes tbody, .wp-list-table.membershiptypes tbody');
            table.length && table.sortable({
                handle: '.uwp-user-type-handle',
                axis: 'y',
                helper: function (e, ui) {
                    ui.children().each(function () {
                        $(this).width($(this).width());
                    });
                    ui.addClass('uwp-dragging');
                    return ui;
                },
                start: function(e, ui) {
                    ui.placeholder.addClass('uwp-drag-placeholder');
                },
                stop: function(e, ui) {
                    ui.item.removeClass('uwp-dragging');
                    table.find('.uwp-drag-placeholder').removeClass('uwp-drag-placeholder');
                },
                update: function (event, ui) {
                    const order = table.find('input[name="user_types[]"]').map(function () {
                        return $(this).val();
                    }).get();

                    $.ajax({
                        url: uwp_admin_ajax.url,
                        type: 'POST',
                        data: {
                            action: 'uwp_ajax_reorder_user_types',
                            order: order,
                            nonce: uwp_admin_ajax.nonces.uwp_reorder_user_types
                        },
                        success: function (response) {
                            if (response.success) {
                                aui_toast('uwp_reorder_user_types_success', 'success', uwp_admin_ajax.txt_saved);
                            } else {
                                aui_toast('uwp_reorder_user_types_error', 'error', uwp_admin_ajax.txt_saving_error);
                            }
                        },
                        error: function () {
                            aui_toast('uwp_reorder_user_types_error', 'error', uwp_admin_ajax.txt_saving_error);
                        }
                    });
                }
            });
        },

        /**
         * Display an admin notice
         * 
         * @param {string} message The message to display
         * @param {string} type The notice type ('success' or 'error')
         */
        showNotice: function (message, type) {
            const noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
            const $notice = $(`<div class="notice ${noticeClass} is-dismissible"><p>${message}</p></div>`);
            $notice.insertAfter('.wp-header-end');

            // Auto-dismiss after 3 seconds
            setTimeout(function () {
                $notice.fadeOut(300, function () { $(this).remove(); });
            }, 3000);
        },

        /**
         * Initialize form control handlers
         */
        initFormControls: function () {
            this.initSectionToggles();
            this.initAdvancedSettings();
        },

        /**
         * Initialize section toggle handlers
         */
        initSectionToggles: function () {
            $(document).on('click', '.toggle-arrow', (e) => {
                this.handleSectionToggle($(e.currentTarget));
            });
        },

        /**
         * Initialize advanced settings toggle
         */
        initAdvancedSettings: function () {
            const $advancedToggle = $('.uwp-advanced-toggle');
            const $advancedElements = $('.uwp-advanced-setting, #default_location_set_address_button');

            $advancedToggle
                .off('click')
                .on('click', function () {
                    $advancedToggle.toggleClass('uwpa-hide');
                    $advancedElements.toggleClass('uwpa-show');
                    $advancedElements.collapse('toggle');
                });
        },

        /**
         * Handle section toggle visibility
         * @param {jQuery} $toggleElement The clicked toggle element
         */
        handleSectionToggle: function ($toggleElement) {
            const $parentSettings = $toggleElement.parent('.li-settings');
            const $currentForm = $parentSettings.find('.field_frm').first();
            const isOpen = !$currentForm.is(':hidden');

            // Hide all forms and reset all arrows
            $('.field_frm').hide();
            $('.toggle-arrow')
                .addClass('fa-caret-down')
                .removeClass('fa-caret-up');

            if (isOpen) {
                // Close current section
                $toggleElement
                    .addClass('fa-caret-down')
                    .removeClass('fa-caret-up');
                $currentForm
                    .hide()
                    .removeClass('uwp-tab-settings-open');
            } else {
                // Open current section
                $toggleElement
                    .addClass('fa-caret-up')
                    .removeClass('fa-caret-down');
                $currentForm
                    .show()
                    .addClass('uwp-tab-settings-open');
            }
        },

        /**
         * Handle radio button visibility changes
         * @param {string} elementId The ID of the radio element
         * @param {string} showHide Show/hide parameter (unused but kept for backwards compatibility)
         * @param {string} className The class to toggle visibility on
         */
        handleRadioVisibility: function (elementId, showHide, className) {
            setTimeout(() => {
                const $element = $(elementId);
                const $targetElement = $element.closest('.li-settings').find('.' + className);
                const isChecked = $element.is(':checked');

                $targetElement.toggle(isChecked ? 'fast' : 'fast');
            }, 100);
        },

        /**
         * Format Font Awesome select option
         * @param {Object} option Select option
         * @returns {string} Formatted option HTML
         */
        formatFaSelect: function (option) {
            if (!option.id) {
                return option.text;
            }
            const icon = $(option.element).attr('data-fa-icon');
            return '<i class="fa-lg ' + icon + '"></i>  ' + option.text;
        },

        /**
         * Format Font Awesome selected option
         * @param {Object} option Selected option
         * @returns {string} Formatted option HTML
         */
        formatFaSelection: function (option) {
            if (option.id.length > 0) {
                const icon = $(option.element).attr('data-fa-icon');
                return "<i class='fa-lg " + icon + "'></i>  " + option.text;
            }
            return option.text;
        },

        /**
         * Initialize tooltips
         */
        initTooltips: function () {
            const $tooltips = $('.uwp-help-tip').tooltip();
            const method = this.getTooltipVersion() >= 4 ? 'dispose' : 'destroy';

            $tooltips.tooltip(method).tooltip({
                content: function () {
                    return $(this).prop('title');
                },
                tooltipClass: 'uwp-ui-tooltip',
                position: {
                    my: 'center top',
                    at: 'center bottom+10',
                    collision: 'flipfit'
                },
                show: null,
                close: function (event, ui) {
                    ui.tooltip.hover(
                        function () {
                            $(this).stop(true).fadeTo(400, 1);
                        },
                        function () {
                            $(this).fadeOut("400", function () {
                                $(this).remove();
                            });
                        }
                    );
                }
            });
        },

        /**
         * Get Bootstrap tooltip version
         * @returns {number} Tooltip version number
         */
        getTooltipVersion: function () {
            let version = 0;
            if (typeof $.fn === 'object' &&
                typeof $.fn.tooltip === 'function' &&
                typeof $.fn.tooltip.Constructor === 'function' &&
                typeof $.fn.tooltip.Constructor.VERSION !== 'undefined') {
                version = parseFloat($.fn.tooltip.Constructor.VERSION);
            }
            return version;
        },
    };

    // Global function mappings for backward compatibility
    window.uwp_show_hide = function ($element) {
        UWP.Admin.handleSectionToggle($($element));
    };

    window.uwp_show_hide_radio = function (id, sh, cl) {
        UWP.Admin.handleRadioVisibility(id, sh, cl);
    };

    window.uwp_init_advanced_settings = function () {
        UWP.Admin.initAdvancedSettings();
    };

    window.uwp_init_tooltips = function () {
        UWP.Admin.initTooltips();
    };

    window.uwp_tooltip_version = function () {
        return UWP.Admin.getTooltipVersion();
    };

    // Initialize when document is ready
    $(document).ready(function () {
        UWP.Admin.init();
    });

})(jQuery);