(function( $, window, undefined ) {
    $(document).ready(function () {
        $('.uwp-profile-avatar-modal-trigger').click(function (e) {
            $('#uwp-avatar-modal').show();
            $(document.body).append("<div id='uwp-modal-backdrop'></div>");
        });
        $('#uwp-avatar-modal-close').click(function (e) {
            e.preventDefault();
            $('#uwp-avatar-modal').hide();
            $("#uwp-modal-backdrop").remove();
        });
        $('.uwp-profile-banner-modal-trigger').click(function (e) {
            $('#uwp-banner-modal').show();
            $(document.body).append("<div id='uwp-modal-backdrop'></div>");
        });
        $('#uwp-banner-modal-close').click(function (e) {
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