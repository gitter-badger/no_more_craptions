<?php
require_once("../inc/functions.php");
require_once("../inc/youtube.class.php");

if(check_var($_POST['id']) && check_var($_POST['caption_block_value']) && check_var($_POST['caption_block_start'])) {
    if(!check_var($_POST['id'])) {
        die($error_no_id['value']);
    }
    
    $id = $_POST['id'];

    $cue = array(
        "start" => $_POST['caption_block_start'],
        "value" => $_POST['caption_block_value']
    );
    
    $video = new Youtube($id);
    echo var_dump($video->update_asr_cue($cue));
}

?>
