<?php
require_once("../inc/functions.php");
require_once("../inc/youtube.class.php");

if(!check_var($_GET['id'])) {
    die($error_no_id['value']);
}
$id = $_GET['id'];
if(!youtube_validate_id($id)) {
    die($error_invalid_id['value']);
}

if(!check_var($_GET['type'])) {
    die($error_no_caption_format['value']);
}
$type = strtolower($_GET['type']);

if(!in_array($type, $caption_format)) {
    die($error_caption_format_not_exist['value']);
}

$edit = check_var($_GET['edit'], True);
$video = new Youtube($id);

$data = $video->get_asr_caption($edit);
if($data == False){
    die("No captions");
}

if(check_var($_GET['dl'])) { // Download it.
    header('Content-Transfer-Encoding: binary');
    if($edit) {
        header(sprintf("Content-Disposition: attachment; filename=\"asr_%s_corrected.%s\"", $id, $type));
    } else {
        header(sprintf("Content-Disposition: attachment; filename=\"asr_%s.%s\"", $id, $type));
    }
}


switch($type) {
    case "vtt":
        header("Content-Type: text/vtt"); 
        echo $video->convert_json_to_VTT($data);
        break;
    case "sbv":
        header("Content-Type: text/sbv"); 
        echo $video->convert_json_to_SBV($data);
        break;
    case "txt":
        header("Content-Type: text/plain"); 
        echo $video->convert_json_to_txt($data);
        break;
    case "srt":
        header("Content-Type: text/srt"); 
        echo $video->convert_json_to_srt($data);
        break;
    case "json":
        header("Content-Type: application/json"); 
        echo json_encode($data);
        break;
    default:
        return False;
}

    

?>
