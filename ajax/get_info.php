<?php
require_once("../inc/functions.php");
require_once("../inc/youtube.class.php");

if(!check_var($_GET['id'])) {
    die(json_encode($error_no_id));
}
$id = $_GET['id'];

if(!youtube_validate_id($id)) {
    die(json_encode($error_invalid_id));
}

$video = new Youtube($id);
$info = $video->retrieve_info();
if($info == False) {
    die(json_encode(array("error" => True, "value" => $video->info['reason'])));
}
if($video->has_asr_caption() == False) {
    die(json_encode($error_no_asr));
}

$data = array(
    "error" => False,
    "title" => $info['title'],
);

echo json_encode($data);
?>
