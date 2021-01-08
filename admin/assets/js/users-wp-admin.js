jQuery(window).on('load', function () {

    // Load color picker
    var UWPColorPicker = jQuery('.uwp-color-picker');
    if (UWPColorPicker.length) {
        UWPColorPicker.wpColorPicker();
    }

    jQuery('.uwp-upload-img').each(function () {
        var $wrap = jQuery(this);
        var field = $wrap.data('field');
        if (jQuery('[name="' + field + '[id]"]').length && !jQuery('[name="' + field + '[id]"]').val()) {
            jQuery('.uwp_remove_image_button', $wrap).hide();
        }
    });

    var media_frame = [];
    jQuery(document).on('click', '.uwp_upload_image_button', function (e) {
        e.preventDefault();

        var $this = jQuery(this);
        var $wrap = $this.closest('.uwp-upload-img');
        var field = $wrap.data('field');

        if (!field) {
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
        media_frame[field].on('select', function () {
            var attachment = media_frame[field].state().get('selection').first().toJSON();

            var thumbnail = attachment.sizes.medium || attachment.sizes.full;
            if (field) {
                if (jQuery('[name="' + field + '[id]"]').length) {
                    jQuery('[name="' + field + '[id]"]').val(attachment.id);
                }
                if (jQuery('[name="' + field + '[src]"]').length) {
                    jQuery('[name="' + field + '[src]"]').val(attachment.url);
                }
                if (jQuery('[name="' + field + '"]').length) {
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

    jQuery(document).on('click', '.uwp_remove_image_button', function () {
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

    jQuery('.userswp .forminp .large-text').focus(function () {
        var placeholder = jQuery(this).attr('placeholder');
        var current_val = jQuery(this).val();
        if ('' == current_val) {
            jQuery(this).val(placeholder);
        }
    }).blur(function () {
        var placeholder = jQuery(this).attr('placeholder');
        var current_val = jQuery(this).val();
        if (current_val == placeholder) {
            jQuery(this).val('');
        }
    });

    jQuery(document).on('click', 'span code', function ($) {
        jQuery('span code').removeClass('uwp-tag-copied');
        jQuery('span code span').remove();
        jQuery(this).addClass('uwp-tag-copied');
        jQuery(this).append('<span></span>');
        var $temp = jQuery("<input>");
        jQuery("body").append($temp);
        $temp.val(jQuery(this).text()).select();
        document.execCommand("copy");
        $temp.remove();

        setTimeout(function () {
            jQuery('span code').removeClass('uwp-tag-copied');
            jQuery('span code span').remove();
        }, 1000);
    });

    jQuery('input.uwp-seo-meta-separator').on('change', function () {
        if (jQuery(this).attr("checked") === "checked") {
            jQuery('input.uwp-seo-meta-separator').parent().removeClass('active');
            jQuery(this).parent().addClass('active');
        }
    }).change();

    jQuery(".aui-fa-select2").select2({
        templateResult: aui_fa_select_format,
        templateSelection: function (option) {
            if (option.id.length > 0) {
                var icon = jQuery(option.element).attr('data-fa-icon');
                return "<i class='fa-lg " + icon + "'></i>  " + option.text;
            } else {
                return option.text;
            }
        },
        escapeMarkup: function (m) {
            return m;
        }
    });

    uwp_init_tooltips();
});

function aui_fa_select_format(option) {
    if (!option.id) {
        return option.text;
    }

    var icon = jQuery(option.element).attr('data-fa-icon');
    return '<i class="fa-lg ' + icon + '"></i>  ' + option.text;
}

function uwp_show_hide($this) {
    var is_open = !jQuery($this).parent('.li-settings').find('.field_frm').first().is(':hidden');
    jQuery('.field_frm').hide();
    jQuery('.field_frm').parent().parent().find('.toggle-arrow').addClass("fa-caret-down").removeClass("fa-caret-up");
    if (is_open) {
        jQuery($this).addClass("fa-caret-down").removeClass("fa-caret-up");
        jQuery($this).parent('.li-settings').find('.field_frm').first().hide().removeClass("uwp-tab-settings-open");
    } else {
        jQuery($this).addClass("fa-caret-up").removeClass("fa-caret-down");
        jQuery($this).parent('.li-settings').find('.field_frm').first().show().addClass("uwp-tab-settings-open");
    }
}

function uwp_init_advanced_settings() {
    jQuery(".uwp-advanced-toggle").off("click").click(function () {
        jQuery(".uwp-advanced-toggle").toggleClass("uwpa-hide");
        console.log('toggle');
        jQuery(".uwp-advanced-setting, #default_location_set_address_button").toggleClass("uwpa-show");
    });
}

/**
 * Init the tooltips
 */
function uwp_init_tooltips() {

    // we create, then destroy then create so we can ajax load and then call this function with impunity.
    jQuery('.uwp-help-tip').tooltip({
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

function uwp_get_spin_loader(loader_obj) {

    loader_obj.append('<i class="fas fa-circle-notch fa-spin ml-2 userswp-admin-spin"></i>');
}

function uwp_remove_spin_loader(loader_obj) {

    setTimeout(function () {
        loader_obj.children('i.userswp-admin-spin').remove();
    }, 1000);
}