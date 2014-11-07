<?php
//ini_set('error_reporting', E_ALL);
/* PREDEF */
$error_no_id = array("error" => True, "value" => "No Video ID.");
$error_invalid_id = array("error" => True, "value" => "Invalid Video ID.");
$error_no_video = array("error" => True, "value" => "This Video doesn't exist");
$error_no_caption_format = array("error" => True, "value" => "Please set a caption format");
$error_no_asr = array("error" => True, "value" => "No ASR Caption available");
$error_caption_format_not_exist = array("error" => True, "value" => "Please provide a valid caption format");
$youtube_id_validator = "/^[a-zA-Z0-9_-]{11}$/";
$caption_format = array("json", "vtt", "txt", "sbv", "srt");

$page = basename($_SERVER['SCRIPT_NAME']);




function hms_to_sec($hms) {
	list($h, $m, $s) = explode (":", $hms);
	$seconds = 0;
	$seconds += (intval($h) * 3600);
	$seconds += (intval($m) * 60);
	$seconds += (intval($s));
	return $seconds;
}

function curl_prepare($url) {
    $header = array(
        "Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5",
        "Cache-Control: max-age=0",
        "Connection: keep-alive",
        "Keep-Alive: 300",
        "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7",
        "Accept-Language: en-us,en;q=0.5",
        "Pragma: ",
    );
    $user_agent = "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.116 Safari/537.36";
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_USERAGENT, $user_agent);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header); 
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 4);

    return $curl;
}

function check_var(&$var, $return_value = False) {
    if (isset($var)) {
        if(!empty($var)) {
            if($return_value) {
                return $var;
            } else {
                return True;
            }
        }
    }
    return False;
}

function redirect($url) {
        header("Location: ".$url);
        exit();
}

function youtube_validate_id($id) { // Validate Video ID
    global $youtube_id_validator;
    preg_match($youtube_id_validator, $id, $matches);
    if($matches == NULL) {
        return False;
    }
    return True;
}

?>
