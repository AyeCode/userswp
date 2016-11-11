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