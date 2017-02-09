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
        var showChar = 100;
        var ellipsestext = "...";
        var moretext = "more";
        var lesstext = "less";
        $('.uwp_more').each(function() {
            var content = $(this).html();

            if(content.length > showChar) {

                var c = content.substr(0, showChar);
                var h = content.substr(showChar-1, content.length - showChar);

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
        $("#uwp_upload_file_remove").click(function(event){
            event.preventDefault();

            var htmlvar =  $( this ).data( 'htmlvar' );
            
            var data = {
                'action': 'uwp_upload_file_remove',
                'htmlvar': htmlvar
            };

            jQuery.post(ajaxurl, data, function(response) {
                $(".uwp_upload_file_preview").remove();
                $("#uwp_upload_file_remove").remove();
            });
        });
    });

}( jQuery, window ));

(function( $, window, undefined ) {
    $(document).ready(function() {
        $("#uwp_layout").change(function(){
            var layout = $(this).val();
            var container = $('#uwp_user_items_layout');
            if (layout == 'grid') {
                container.removeClass('uwp_listview');
                container.addClass('uwp_gridview uwp_gridview_2col');
            } else {
                container.removeClass('uwp_gridview uwp_gridview_2col');
                container.addClass('uwp_listview');
            }
        });
    });
}( jQuery, window ));