(function( $, window, undefined ) {
    $(document).ready(function () {
        $('.uwp-profile-avatar-modal-trigger').click(function(e) {
            e.preventDefault();
            $('#uwp-avatar-modal').show();
            $(document.body).append("<div id='uwp-modal-backdrop'></div>");
        });
        $('#uwp-avatar-modal-close').click(function(e) {
            e.preventDefault();
            $('#uwp-avatar-modal').hide();
            $("#uwp-modal-backdrop").remove();
        });
        $('.uwp-profile-banner-modal-trigger').click(function(e) {
            e.preventDefault();
            $('#uwp-banner-modal').show();
            $(document.body).append("<div id='uwp-modal-backdrop'></div>");
        });
        $('#uwp-banner-modal-close').click(function(e) {
            e.preventDefault();
            $('#uwp-banner-modal').hide();
            $("#uwp-modal-backdrop").remove();
        });
    });
}( jQuery, window ));

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
    jQuery(document).on('click', '#submit', function(e){
        e.preventDefault();

        var fd = new FormData();
        var file = jQuery(document).find('input[type="file"]');
        var caption = jQuery(this).find('input[name=img_caption]');
        var individual_file = file[0].files[0];
        fd.append("file", individual_file);
        var individual_capt = caption.val();
        fd.append("caption", individual_capt);
        fd.append('action', 'uwp_ajax_upload_file');

        jQuery.ajax({
            type: 'POST',
            url: fiuajax.ajaxurl,
            data: fd,
            contentType: false,
            processData: false,
            success: function(response){

                console.log(response);
            }
        });
    });
}( jQuery, window ));

(function( $, window, undefined ) {

    $(document).ready( function() {
        var file_frame; // variable for the wp.media file_frame

        // attach a click event (or whatever you want) to some element on your page
        $( '.uwp-profile-banner-modal-trigger' ).on( 'click', function( event ) {
            event.preventDefault();

            // if the file_frame has already been created, just reuse it
            if ( file_frame ) {
                file_frame.open();
                return;
            }

            file_frame = wp.media.frames.file_frame = wp.media({
                title: $( this ).data( 'uploader_title' ),
                button: {
                    text: $( this ).data( 'uploader_button_text' )
                },
                multiple: false, // set this to true for multiple file selection
                /*
                 * Here is where the main magic happens.
                 *
                 * We take the type, e.g. video, image, audio,
                 * and we send it to library.type which only
                 * shows the files of that type.
                 */
                library: { type : "image" }
            });

            file_frame.on( 'select', function() {
                attachment = file_frame.state().get('selection').first().toJSON();

                // do something with the file here
                $( '.uwp-profile-banner-modal-trigger' ).hide();

                var data = {
                    'action': 'uwp_ajax_image_crop_popup',
                    'image_url': attachment.url,
                    'type': 'banner'
                };

                jQuery.post(ajaxurl, data, function(response) {
                    $('#uwp-banner-modal-content').html(response);
                });

            });

            file_frame.open();
        });
    });

}( jQuery, window ));