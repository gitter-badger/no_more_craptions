function youtube_parse_id(url){
    if(url.length == 11 && url.indexOf(".") == -1) {
        return url;
    } else {
        var p = /^(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/;
        return (url.match(p)) ? RegExp.$1 : false;
    }
}

function reset_youtube_id() {
    $("input#youtube-id").val("");
    $("input#youtube-id").trigger("input");
}

$(document).ready(function() {

    $("input#youtube-id").on("input", function() {
        $("#youtube-video-button").removeClass("open");
        $("#youtube-video-button").html("");
        var id = youtube_parse_id($(this).val())
        if(id) {
            $("#youtube-video-button").html("");
            $.getJSON("ajax/get_info.php?id=" + id, function(data) {
                if(data == false) {
                    $("#youtube-video-button").html($('<div class="row"><div class="text-center col-md-12"><button class="btn-danger btn btn-lg" onclick="reset_youtube_id();">No information found for this video</button></div></div>'));
                } else {
                    if(data.error == true) {
                        $("#youtube-video-button").html($('<div class="row"><div class="text-center col-md-12"><button class="btn-danger btn btn-lg" onclick="reset_youtube_id();">'+data.value+'</button></div></div>'));
                    } else {
                        $("#youtube-video-button").html($('<div class="row"><a href="go.php?id='+id+'"><div class="text-center col-md-12"><button class="btn-success btn btn-lg">'+data.title+'</button></div></a></div>'));
                    }
                }
                $("#youtube-video-button").addClass("open");
            });
        }
    });

    $("input#youtube-id").trigger("input");
    
});
