jQuery(window).load(function() {
    // Chosen selects
    if (jQuery("select.uwp_chosen_select").length > 0) {
        jQuery("select.uwp_chosen_select").chosen();
        jQuery("select.uwp_chosen_select_nostd").chosen({
            allow_single_deselect: 'true'
        });
    }
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
                var html = c + '<span class="uwp_more_ellipses">' + ellipsestext+ '&nbsp;</span><span class="uwp_more_content"><span>' + h + '</span>&nbsp;&nbsp;<a href="" class="uwp_more_link">' + moretext + '</a></span>';

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

            jQuery.post(ajaxurl, data, function(response) {
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

            jQuery.post(ajaxurl, data, function(response) {
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
    });
}( jQuery, window ));