function preview(img, selection) {
    var scaleX = 100 / selection.width;
    var scaleY = 100 / selection.height;

    $("#thumbnail").find("+ div > img").css({
        width: Math.round(scaleX * 200) + "px",
        height: Math.round(scaleY * 300) + "px",
        marginLeft: "-" + Math.round(scaleX * selection.x1) + "px",
        marginTop: "-" + Math.round(scaleY * selection.y1) + "px"
    });
    $("#x1").val(selection.x1);
    $("#y1").val(selection.y1);
    $("#x2").val(selection.x2);
    $("#y2").val(selection.y2);
    $("#w").val(selection.width);
    $("#h").val(selection.height);
}

(function( $, window, undefined ) {
    $(document).ready(function () {
        $("#save_thumb").click(function() {
            var x1 = $("#x1").val();
            var y1 = $("#y1").val();
            var x2 = $("#x2").val();
            var y2 = $("#y2").val();
            var w = $("#w").val();
            var h = $("#h").val();
            if(x1=="" || y1=="" || x2=="" || y2=="" || w=="" || h==""){
                alert("You must make a selection first");
                return false;
            }else{
                return true;
            }
        });
    });
}( jQuery, window ));