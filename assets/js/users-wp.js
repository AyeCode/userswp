jQuery(window).load(function() {

    // Enable auth modals
    uwp_init_auth_modal();
});


(function( $, window, undefined ) {
    $(document).ready(function() {
        var showChar = uwp_localize_data.uwp_more_char_limit;
        var ellipsestext = uwp_localize_data.uwp_more_ellipses_text;
        var moretext = uwp_localize_data.uwp_more_text;
        var lesstext = uwp_localize_data.uwp_less_text;
        $('.uwp_more').each(function() {
            var content = $.trim($(this).text());

            if(content.length > showChar) {

                var c = content.substr(0, showChar);
                var h = content.substr(showChar, content.length - showChar);
                var html = uwp_nl2br(c) + '<span class="uwp_more_ellipses">' + ellipsestext+ '&nbsp;</span><span class="uwp_more_content"><span style="display: none;">' + uwp_nl2br(h) + '</span>&nbsp;&nbsp;<a href="" class="uwp_more_link">' + moretext + '</a></span>';

                $(this).html(html);
            }

        });

        $(".uwp_more_link").click(function(){
            if($(this).hasClass("uwp_less")) {
                $(this).removeClass("uwp_less");
                $(this).html(moretext);
            } else {
                $(this).addClass("uwp_less");
                $(this).html(lesstext);
            }
            $(this).parent().prev().toggle();
            $(this).prev().toggle();
            return false;
        });
    });
}( jQuery, window ));


(function( $, window, undefined ) {

    var uwp_popup_type;

    $(document).ready( function() {
        $( '.uwp-profile-modal-form-trigger' ).on( 'click', function( event ) {
            event.preventDefault();

            uwp_popup_type = $( this ).data( 'type' );

            // do something with the file here
            var data = {
                'action': 'uwp_ajax_image_crop_popup_form',
                'type': uwp_popup_type
            };

            var container = jQuery('#uwp-popup-modal-wrap');
            container.show();

            jQuery.post(uwp_localize_data.ajaxurl, data, function(response) {
                $(document.body).append("<div id='uwp-modal-backdrop'></div>");
                container.replaceWith(response);
            });
        });
    });

    $(document).ready(function() {
        $(".uwp_upload_file_remove").click(function(event){
            event.preventDefault();

            var htmlvar =  $( this ).data( 'htmlvar' );
            var uid =  $( this ).data( 'uid' );

            var data = {
                'action': 'uwp_upload_file_remove',
                'htmlvar': htmlvar,
                'uid': uid
            };

            jQuery.post(uwp_localize_data.ajaxurl, data, function(response) {
                $("#"+htmlvar+"_row").find(".uwp_file_preview_wrap").remove();
                $("#"+htmlvar).closest("td").find(".uwp_file_preview_wrap").remove();
                if($('input[name='+htmlvar+']').data( 'is-required' )){
                    $('input[name='+htmlvar+']').prop('required',true);
                }
            });
        });
    });

}( jQuery, window ));

(function( $, window, undefined ) {
    $(document).ready(function() {
        $("#uwp_layout").change(function(){
            var layout = $(this).val();
            var container = $('#uwp_user_items_layout');
            container.removeClass();
            if (layout == 'list') {
                container.addClass('uwp-users-list-wrap uwp_listview');
            } else if (layout == '2col') {
                container.addClass('uwp-users-list-wrap uwp_gridview uwp_gridview_2col');
            } else if (layout == '3col') {
                container.addClass('uwp-users-list-wrap uwp_gridview uwp_gridview_3col');
            } else if (layout == '4col') {
                container.addClass('uwp-users-list-wrap uwp_gridview uwp_gridview_4col');
            } else if (layout == '5col') {
                container.addClass('uwp-users-list-wrap uwp_gridview uwp_gridview_5col');
            } else {
                container.addClass('uwp-users-list-wrap uwp_listview');
            }
        });

        jQuery( document ).ready(function($) {
            $( "#uwp_login_modal form.uwp-login-form" ).submit(function( e ) {
                e.preventDefault();
                uwp_ajax_login(this);
            });
        });

        function uwp_ajax_login($this) {

            $('#uwp_login_modal .uwp-login-ajax-notice').remove();

            var data = jQuery($this).serialize()+ "&action=uwp_ajax_login";

            jQuery.post(uwp_localize_data.ajaxurl, data, function(response) {
                response = jQuery.parseJSON(response);

                if(response.error){
                    $('#uwp_login_modal form.uwp-login-form').before(response.message);
                } else {
                    $('#uwp_login_modal form.uwp-login-form').before(response.message);
                    setTimeout(function(){location.reload()}, 1200)
                }

            });
        }

    });
}( jQuery, window ));

function uwp_nl2br(str, is_xhtml) {
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1'+ breakTag +'$2');
}

function uwp_profile_image_change(type){
    // remove it first
    jQuery('.uwp-profile-image-change-modal').remove();

    var $modal = '<div class="modal fade uwp-profile-image-change-modal bsui" tabindex="-1" role="dialog" aria-labelledby="uwp-profile-modal-title" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="uwp-profile-modal-title"></h5></div><div class="modal-body text-center"><i class="fas fa-circle-notch fa-spin fa-3x"></i></div></div></div></div>';
    jQuery('body').append($modal);

    jQuery('.uwp-profile-image-change-modal').modal({
        backdrop: 'static'
    });

    // do something with the file here
    var data = {
        'action': 'uwp_ajax_image_crop_popup_form',
        'type': type,
        'style': 'bootstrap'
    };

    jQuery.post(uwp_localize_data.ajaxurl, data, function(response) {
        jQuery('.uwp-profile-image-change-modal .modal-content').html(response);
    });
}

function uwp_init_auth_modal(){

    // open login form
    if(uwp_localize_data.login_modal) {
        jQuery('.users-wp-login-nav a, .uwp-login-link').unbind('click');
        jQuery(".users-wp-login-nav a, .uwp-login-link").click(function (e) {
            uwp_cancelBubble(e);
            uwp_modal_login_form();
            return false;
        });
    }

    // open the register form
    if(uwp_localize_data.register_modal) {
        jQuery('.users-wp-register-nav a, .uwp-register-link').unbind('click');
        jQuery(".users-wp-register-nav a, .uwp-register-link").click(function (e) {
            uwp_cancelBubble(e);
            uwp_modal_register_form();
            return false;
        });
    }

    // open the forgot password form
    if(uwp_localize_data.forgot_modal) {
        jQuery('.users-wp-forgot-nav a, .uwp-forgot-password-link').unbind('click');
        jQuery(".users-wp-forgot-nav a, .uwp-forgot-password-link").click(function (e) {
            uwp_cancelBubble(e);
            uwp_modal_forgot_password_form();
            return false;
        });
    }
}

function uwp_modal_loading(inputs){
    $input_single = '<span class="badge badge-pill badge-light p-3 mt-3 w-100 bg-loading">&nbsp;</span>';
    $inputs = inputs ? $input_single.repeat(inputs) : $input_single;

    var $modal_content = '<div class="modal-header">' +
        '<span class="badge badge-pill badge-light p-0 mt-2 w-25 bg-loading">&nbsp;</span></div>' +
        '<div class="modal-body text-center">' + $inputs + '</div>';
    var $modal = '<div class="modal fade uwp-auth-modal bsui" tabindex="-1" role="dialog" aria-labelledby="uwp-profile-modal-title" aria-hidden="true">' +
        '<div class="modal-dialog modal-dialog-centered">' +
        '<div class="modal-content">' +
        $modal_content +
        '</div></div></div>';

    if(!jQuery('.uwp-auth-modal').length){
        jQuery('body').append($modal);
    }else{
        jQuery('.uwp-auth-modal .modal-content').html($modal_content);
    }
    jQuery('.uwp-auth-modal').modal();
}

/**
 * Get the login form via ajax and load it in a modal.
 */
function uwp_modal_login_form(){
    var data = {
        'action': 'uwp_ajax_login_form' // deliberately no nonce for caching reasons
    };
    jQuery.ajax({
        type: "POST",
        url: uwp_localize_data.ajaxurl,
        data: data,
        beforeSend: function() {
            uwp_modal_loading(4);
        },
        success: function(data) {
            if(data.success){
                jQuery('.uwp-auth-modal .modal-content').html(data.data);
                setTimeout(function(){jQuery('.uwp-auth-modal .modal-content input:visible:enabled:first').focus().unbind('focus');}, 300); // set focus on the first input after load animation

                // process login form
                jQuery(".uwp-auth-modal .modal-content form.uwp-login-form").submit(function(e){
                    e.preventDefault(e);
                    uwp_modal_login_form_process();
                });
            }
            uwp_init_auth_modal();
        }
    });
}

/**
 * Check if we are waiting on a recaptcha callback.
 *
 * @param $form
 * @returns {boolean}
 */
function uwp_maybe_check_recaptcha($form){
    if(typeof uwp_recaptcha_loops === 'undefined' || !uwp_recaptcha_loops){uwp_recaptcha_loops = 1;}
    if(jQuery('.uwp-auth-modal .modal-content .g-recaptcha-response').length && jQuery('.uwp-auth-modal .modal-content .g-recaptcha-response').val() == ''){
        setTimeout(function(){
            // remove the original spinner
            jQuery('.uwp-auth-modal .modal-content button[type="submit"] i.fa-spin,.uwp-auth-modal .modal-content button[type="submit"] svg.fa-spin').remove();

            // bail and add warning if still no recaptcha after 5 loops
            if(uwp_recaptcha_loops>=6){
                jQuery('.uwp-auth-modal .modal-content .uwp_login_submit').prop('disabled', false);
                jQuery('.uwp-auth-modal .modal-content .uwp_register_submit').prop('disabled', false);
                jQuery('.uwp-auth-modal .modal-content .uwp_forgot_submit').prop('disabled', false);
                jQuery('.uwp-auth-modal .modal-content .uwp-captcha-render').addClass("alert alert-danger");

                // maybe show general error
                if(jQuery('.uwp-auth-modal .modal-content .modal-error').html()==''){
                    jQuery('.uwp-auth-modal .modal-content .modal-error').html("<div class='alert alert-danger'>"+uwp_localize_data.error_retry+"</div>");
                }

                return false;
            }

            if($form=='login'){
                uwp_modal_login_form_process();
            }else if($form == 'register'){
                uwp_modal_register_form_process();
            }else if($form == 'forgot'){
                uwp_modal_forgot_password_form_process();
            }
        }, 500); // 6 x 500 = 3 seconds we wait for response before showing error.
        uwp_recaptcha_loops++;
        return false;
    }
    uwp_recaptcha_loops = 0;
    return true;
}

/**
 * Maybe reset the recpatcha on ajax submit fail.
 */
function uwp_maybe_reset_recaptcha() {
    if( jQuery('.uwp-auth-modal .modal-content .g-recaptcha-response').length ){
        jQuery('.uwp-auth-modal .modal-content .uwp-captcha-render').html(''); // reset
        var id = jQuery('.uwp-auth-modal .modal-content .uwp-captcha-render').attr('id');
        jQuery('.uwp-auth-modal .modal-content .uwp-captcha-render').replaceWith("<div id='"+id+"' class='uwp-captcha-render'></div>");
        setTimeout(function(){
            uwp_init_recaptcha();
        }, 50);
    }
}

/**
 * Submit the login form via ajax.
 */
function uwp_modal_login_form_process(){
    var data = jQuery(".modal-content form.uwp-login-form").serialize() + '&action=uwp_ajax_login';
    $button_text = jQuery('.uwp-auth-modal .modal-content .uwp_login_submit').html();
    jQuery.ajax({
        type: "POST",
        url: uwp_localize_data.ajaxurl,
        data: data,
        beforeSend: function() {
            jQuery('.uwp-auth-modal .modal-content .uwp_login_submit').html('<i class="fas fa-circle-notch fa-spin"></i> ' + $button_text).prop('disabled', true);// disable submit
            jQuery('.uwp-auth-modal .modal-content .modal-error').html(''); // clear error messages
            return uwp_maybe_check_recaptcha('login');
        },
        success: function(data) {
            if(data.success==true){
                jQuery('.uwp-auth-modal .modal-content .uwp_login_submit').html($button_text).prop('disabled', true);// remove spinner
                jQuery('.uwp-auth-modal .modal-content .modal-error').html(data.data);
                // Show success message for 1 second before redirecting.
                setTimeout(function(){
                    location.reload();
                }, 1000);

            }else if(data.success===false){
                jQuery('.uwp-auth-modal .modal-content .modal-error').html(data.data);
                jQuery('.uwp-auth-modal .modal-content .uwp_login_submit').html($button_text).prop('disabled', false);// enable submit
                uwp_maybe_reset_recaptcha();
            }
            uwp_init_auth_modal();
        }
    });
}


/**
 * Get the register form via ajax and load it in a modal.
 */
function uwp_modal_register_form(){
    var data = {
        'action': 'uwp_ajax_register_form' // deliberately no nonce for caching reasons
    };
    jQuery.ajax({
        type: "POST",
        url: uwp_localize_data.ajaxurl,
        data: data,
        beforeSend: function() {
            uwp_modal_loading(6);
        },
        success: function(data) {
            if(data.success){
                jQuery('.uwp-auth-modal .modal-content').html(data.data);
                setTimeout(function(){jQuery('.uwp-auth-modal .modal-content input:visible:enabled:first').focus().unbind('focus');}, 300); // set focus on the first input after load animation

                // process register form
                jQuery(".uwp-auth-modal .modal-content form.uwp-registration-form").submit(function(e){
                    e.preventDefault(e);
                    uwp_modal_register_form_process();
                });
            }
            uwp_init_auth_modal();
        }
    });
}

/**
 * Submit the login form via ajax.
 */
function uwp_modal_register_form_process(){
    var data = jQuery(".modal-content form.uwp-registration-form").serialize() + '&action=uwp_ajax_register';
    $button = jQuery('.uwp-auth-modal .modal-content .uwp_register_submit');
    $button_text = $button.html();
    jQuery.ajax({
        type: "POST",
        url: uwp_localize_data.ajaxurl,
        data: data,
        beforeSend: function() {
            $button.html('<i class="fas fa-circle-notch fa-spin"></i> ' + $button_text).prop('disabled', true);// disable submit
            jQuery('.uwp-auth-modal .modal-content .modal-error').html(''); // clear error messages
            return uwp_maybe_check_recaptcha('register');
        },
        success: function(data) {
            if(data.success){
                $button.html($button_text).prop('disabled', true);// remove spinner

                if(data.data.message){
                    jQuery('.uwp-auth-modal .modal-content .modal-error').html(data.data.message);
                    // Show success message for 1 second before redirecting.
                    setTimeout(function(){
                        if(data.data.redirect){
                            window.location = data.data.redirect;
                        }else{
                            location.reload();
                        }
                    }, 1000);
                }else{
                    jQuery('.uwp-auth-modal .modal-content .modal-error').html(data.data);
                }


            }else if(data.success===false){
                jQuery('.uwp-auth-modal .modal-content .modal-error').html(data.data);
                $button.html($button_text).prop('disabled', false);// enable submit
                uwp_maybe_reset_recaptcha();
            }
            uwp_init_auth_modal();
        }
    });
}


/**
 * Get the forgot password form via ajax and load it in a modal.
 */
function uwp_modal_forgot_password_form(){
    var data = {
        'action': 'uwp_ajax_forgot_password_form' // deliberately no nonce for caching reasons
    };
    jQuery.ajax({
        type: "POST",
        url: uwp_localize_data.ajaxurl,
        data: data,
        beforeSend: function() {
            uwp_modal_loading(2);
        },
        success: function(data) {
            if(data.success){
                jQuery('.uwp-auth-modal .modal-content').html(data.data);
                setTimeout(function(){jQuery('.uwp-auth-modal .modal-content input:visible:enabled:first').focus().unbind('focus');}, 300); // set focus on the first input after load animation

                // process login form
                jQuery(".uwp-auth-modal .modal-content form.uwp-forgot-form").submit(function(e){
                    e.preventDefault(e);
                    uwp_modal_forgot_password_form_process();
                });
            }
            uwp_init_auth_modal();
        }
    });
}

/**
 * Submit the forgot password form via ajax.
 */
function uwp_modal_forgot_password_form_process(){
    var data = jQuery(".modal-content form.uwp-forgot-form").serialize() + '&action=uwp_ajax_forgot_password';
    $button = jQuery('.uwp-auth-modal .modal-content .uwp_forgot_submit');
    $button_text = $button.html();
    jQuery.ajax({
        type: "POST",
        url: uwp_localize_data.ajaxurl,
        data: data,
        beforeSend: function() {
            $button.html('<i class="fas fa-circle-notch fa-spin"></i> ' + $button_text).prop('disabled', true);// disable submit
            jQuery('.uwp-auth-modal .modal-content .modal-error').html(''); // clear error messages
            return uwp_maybe_check_recaptcha('forgot');
        },
        success: function(data) {
            if(data.success){
                $button.html($button_text).prop('disabled', true);// remove spinner
                jQuery('.uwp-auth-modal .modal-content .modal-error').html(data.data);
                // Show success message for 1 second before redirecting.
                // setTimeout(function(){
                //     location.reload();
                // }, 1000);

            }else if(data.success===false){
                jQuery('.uwp-auth-modal .modal-content .modal-error').html(data.data);
                $button.html($button_text).prop('disabled', false);// enable submit
                uwp_maybe_reset_recaptcha();
            }
            uwp_init_auth_modal();
        }
    });
}

/**
 * A password strength indicator.
 *
 * @param $pass1
 * @param $pass2
 * @param $strengthResult
 * @param $submitButton
 * @param blacklistArray
 * @returns {*|number}
 */
function uwp_checkPasswordStrength( $pass1,
                                    $pass2,
                                    $strengthResult,
                                    $submitButton,
                                    blacklistArray ) {
    var pass1 = $pass1.val();
    var pass2 = $pass2.val();

    // maybe insert
    if(!jQuery('#uwp-password-strength').length && pass1){
        if($pass2.length){
            $container = $pass2.closest('.form-group');
        }else{
            $container = $pass1.closest('.form-group');
        }
        $container.append( '<div class="progress mt-1"><div id="uwp-password-strength" class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0;"></div></div>' );
        $strengthResult = jQuery('#uwp-password-strength');
    }else if(!pass1 && !pass2){
        $strengthResult.parent().remove();
    }

    // Reset the form & meter
    $strengthResult.removeClass( 'short bad good strong bg-warning bg-success bg-danger' );

    // Extend our blacklist array with those from the inputs & site data
    blacklistArray = blacklistArray.concat( wp.passwordStrength.userInputBlacklist() );

    // Get the password strength
    var strength = wp.passwordStrength.meter( pass1, blacklistArray, pass2 );

    // Add the strength meter results
    switch ( strength ) {

        case 2:
            $strengthResult.addClass( 'bad bg-warning' ).html( pwsL10n.bad ).width('50%');
            break;

        case 3:
            $strengthResult.addClass( 'good bg-success' ).html( pwsL10n.good ).width('75%');
            break;

        case 4:
            $strengthResult.addClass( 'strong bg-success' ).html( pwsL10n.strong ).width('100%');
            break;

        case 5:
            $strengthResult.addClass( 'short bg-danger' ).html( pwsL10n.mismatch ).width('25%');
            break;

        default:
            $strengthResult.addClass( 'short bg-danger' ).html( pwsL10n.short ).width('25%');

    }

    return strength;
}

/**
 * Prevent onclick affecting parent elements.
 *
 * @param e
 */
function uwp_cancelBubble(e){
    var evt = e ? e:window.event;
    if (evt.stopPropagation)    evt.stopPropagation();
    if (evt.cancelBubble!=null) evt.cancelBubble = true;
}

function uwp_gd_delete_post($post_id){
    var message = geodir_params.my_place_listing_del;
    if (confirm(message)) {

        jQuery.ajax({
            url: geodir_params.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'geodir_user_delete_post',
                security: geodir_params.basic_nonce,
                post_id: $post_id
            },
            timeout: 20000,
            success: function(data) {

                if(data.success){
                    lity('<div class="gd-notification gd-success"><i class="fas fa-check-circle"></i> '+ data.data.message +'</div>');
                    setTimeout(function() {
                        location.reload();
                    }, 3000);
                }else{
                    lity('<div class="gd-notification gd-error"><i class="fas fa-exclamation-circle"></i> '+ data.data.message +'</div>');
                }
            }
        });

        return true;
    } else {
        return false;
    }
}