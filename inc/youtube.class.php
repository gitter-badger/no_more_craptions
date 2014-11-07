<?php
/**************************************************
Youtube Class, to handle main youtube function, such as getting stream URL, available cCaption
Caption will be stored as JSON.
**************************************************/


class Youtube{

    public $id;
    public $url;
    public $info = NULL;
    public $streams = NULL;
    public $real_caption_list = NULL;
    public $tts_url = NULL;
    public $asr_langcode = NULL;

    public $real_caption = array();
    

    protected $url_youtube_retry_limit = 2;
    protected $itags = array(22, 43, 18, 5, 36, 17);

    protected $path_original_ASR;
    protected $path_edit_ASR;
    protected $path_original_real;
    protected $path_edit_real;

    protected $url_youtube = "http://www.youtube.com/watch?v=%s";
    protected $url_info = "http://www.youtube.com/get_video_info?video_id=%s";
    protected $url_real_caption_list = "http://video.google.com/timedtext?type=list&v=%s";
    protected $url_real_caption = "http://video.google.com/timedtext?type=track&v=%s&lang=%s&name=%s";

    protected $url_asr_langcode = "%s&type=list&asrs=1";
    protected $url_asr_caption = "%s&type=track&lang=%s&name&kind=asr&fmt=1";
    
    protected $ASR_extractor = "/'TTS_URL': \"(.*)\",/";

    protected $format_VTT_header = "%s.%s --> %s.%s %s\n%s\n\n";
    protected $format_SBV_header = "%s.%s,%s.%s\n%s\n\n";
    protected $format_SRT_header = "%s\n%s,%s --> %s,%s\n%s\n\n";
    
    function __construct($id) {
        $this->id = $id;
        $this->path_original_ASR = dirname(__FILE__)."/../captions/originals/asr_%s.json";
        $this->path_edit_ASR = dirname(__FILE__)."/../captions/edits/asr_%s.json";
        $this->path_original_real = dirname(__FILE__)."/../captions/originals/real_%s.json";
        $this->path_edit_real = dirname(__FILE__)."/../captions/edits/real_%s.json";
        
    }

    function get_id() { // Return Video ID
        return $this->id;
    }

    function retrieve_info() { // Retrieve video info
        $data = array();
        $retry = 0;
        $url = sprintf($this->url_info, $this->id);

        while(sizeof($data) < 2 || empty($data) && $retry < $this->url_youtube_retry_limit) {
            $curl = curl_prepare($url);
            $response = curl_exec($curl);
            $response = explode("&", $response);
            
            foreach($response as $each) {
                $each = explode("=", $each);
                $data[$each[0]] = urldecode($each[1]);
            }

            if($data['status'] == "fail") {
                $this->info = $data;
                return False;
            }
            $retry++;

        }

        if(sizeof($data) < 2 || empty($data)) {
            return False;
        }

        $this->info = $data;
        return $this->info;

    }

    function get_streams() { // Get Video streams list
        $streams = array();
        
        if($this->info == NULL) { // Check if we have info first
            if(!$this->retrieve_info()) { // Get info if not
                return False;
            }
        }
        
        $raw_streams = $this->info['url_encoded_fmt_stream_map'];
        $raw_streams = explode(",", $raw_streams);
        
        foreach($raw_streams as $stream){
            parse_str($stream, $stream); //Decode the stream
            array_push($streams, $stream);
        }
        
        $this->streams = $streams;
        return $this->streams;
    }

    function get_best_stream() {
        if($this->streams == NULL) { // Check if we have info first
            if(!$this->get_streams()) { // Get info if not
                return False;
            }
        }
        foreach($this->itags as $itag) {
            foreach($this->streams as $stream) {
                if($itag == $stream['itag']) {
                    return $stream;
                }
            }
        }
    }


    function retrieve_tts_url() { // Retrieve and parse youtube.com page to get TTS_URL
        if($this->tts_url != NULL) {
            return $this->tts_url;
        }
        $url = sprintf($this->url_youtube, $this->id);
        $retry = 0;

        $curl = curl_prepare($url);
        
        while(in_array(curl_getinfo($curl)['http_code'], array(0, '301')) && $retry < $this->url_youtube_retry_limit) { // Keep trying until get or reach limit
            $curl = curl_prepare($url);
            $data = curl_exec($curl);
            $retry++;
        }

        preg_match($this->ASR_extractor, $data, $matches);
        if(empty($matches)) {
            return False; // No ASR link found :(
        }
        $this->tts_url = json_decode('"'.$matches[1].'"');
        return $this->tts_url;
    }

    function retrieve_asr_langcode() { // Retrieve the langcode of the ASR caption (if existing)
        if($this->retrieve_tts_url() == False) {
            return False;
        }
        if($this->asr_langcode != NULL) {
            return $this->asr_langcode;
        }
        
        $url =  sprintf($this->url_asr_langcode, $this->tts_url);
        $curl = curl_prepare($url);
        $data = curl_exec($curl);
        if(curl_getinfo($curl)['http_code'] == "404") {
            return False; // ASR doesn't exist?
        }
        $langcode = simplexml_load_string($data)->xpath("//track[@kind='asr']")[0]['lang_code'];
        if($langcode == NULL) {
            return False;
        }
        $this->asr_langcode = $langcode;
        return $this->asr_langcode;      

    }

    function retrieve_asr_caption() { // Retrieve asr caption, need tts_url and langcode
        if(!$this->retrieve_asr_langcode()) {
            return False;
        }        
        
        $url =  sprintf($this->url_asr_caption, $this->tts_url, $this->asr_langcode);

        $curl = curl_prepare($url);
        $data = curl_exec($curl);
        if(curl_getinfo($curl)['http_code'] == "404") {
            return False; // ASR doesn't exist?
        }
        return $data;      

    }

    function has_asr_caption() { // Find out if ASR is available, looking to get the langcode
        if(!$this->retrieve_asr_langcode()) {
            return False;
        }
        return True;
    }

    function get_asr_caption($edit = False, $update = False) { // Get the ASR caption, original or edit, from file or Youtube.
        if($edit == False) {
            $path = sprintf($this->path_original_ASR, $this->id);
        } else {
            $path = sprintf($this->path_edit_ASR, $this->id);
        }
        if(!file_exists($path)) {
            $update = True;
        }
        if($update) {
            $data = $this->retrieve_asr_caption();
            if($data == False) {
                return False;
            }
            $data = htmlspecialchars_decode($data);
            $data = $this->convert_xml_to_array($data);
            file_put_contents($path, json_encode($data));
            return $data;   
        } else {
            $data = json_decode(file_get_contents($path), True);
            return $data;
        }
        
    }

    function update_asr_cue($cue) { // Change a cue in a caption, save it.
        $path = sprintf($this->path_edit_ASR, $this->id);

        $data = $this->get_asr_caption(True); // Get the caption from file, if doesn't exist get original from Youtube

        foreach($data as $key => $each) {
            if($each['start'] == $cue['start']) {
                $data[$key]['value'] = $cue['value'];
                file_put_contents($path, json_encode($data));
                return True;
            }
        }
        return False;
        
    }

    function retrieve_real_caption_list() { // Retrieve real caption list, not ASR
        if(!empty($this->real_caption_list)) { // We already have it.
            return $this->real_caption_list;
        }
        
        $real_caption_list = array();
        $url = sprintf($this->url_real_caption_list, $this->id);
        
        $curl = curl_prepare($url);
        $response = curl_exec($curl);

        $data = json_decode(json_encode(simplexml_load_string($response)->xpath("//track")), True);

        foreach($data as $caption) {
            $real_caption_list[] = $caption['@attributes'];
        }

        $this->real_caption_list = $real_caption_list;
        return $this->real_caption_list;

    }


    function get_real_caption_name($lang_code) { // Get name of real caption from lang_code
        $this->retrieve_real_caption_list();
        foreach($this->real_caption_list as $caption) {
            if($caption['lang_code'] == $lang_code) {
                return $caption['name'];
            }
        }
        return False; // Not found.
    }


    function has_real_captions() { // Check if there is any real captions
        $this->retrieve_real_caption_list();
        if(empty($this->real_caption_list)) {
            return False;
        }
        return True;
    }

    function has_real_caption($lang_code, $name = NULL) { // Check if real caption exist for lang_code
        $this->retrieve_real_caption_list();
        if(array_key_exists($lang_code, $this->real_caption)) {
            return True;
        }
        
        foreach($this->real_caption_list as $caption) {
            if($caption['lang_code'] == $lang_code) {
                return True;
            }
        }
        
        return False;
        
    }    

    function retrieve_real_caption($lang_code, $name = NULL) { // Retrieve caption file as XML, wil get $name from $this->real_caption_list if not specified.
        if(array_key_exists($lang_code, $this->real_caption)) {
            $this->real_caption[$lang_code] = $response;
        }
        
        if($name == NULL) {
            $name = $this->get_real_caption_name($lang_code);
            if($name == False) {
                return False;
            }
        }
        
        $url = sprintf($this->url_real_caption, $this->id, $lang_code, urlencode($name));
        $curl = curl_prepare($url);
        $response = curl_exec($curl);
        $this->real_caption[$lang_code] = $this->convert_xml_to_array($response);
        return $this->real_caption[$lang_code];
    }

    function convert_xml_to_array($input) { // Convert from Youtube XML format to JSON
        $data = array();
        $elements = simplexml_load_string($input)->xpath("//text");
        foreach($elements as $element) {
            $data[] = array(
                "start" => (float)$element['start'],
                "dur" => (float)$element['dur'],
                "value" => (string)$element,
                "extra_data" => array(),
            );
        }
        return $data;
    }

    function convert_json_to_VTT($input) { // Convert from JSON to VTT format
        $output = "WEBVTT\n\n";
        foreach($input as $cue) {
            $extra = check_var($cue['extra']);
            $end = $cue['start'] + $cue['dur'];
            $start_ms = str_pad(explode(".", $cue['start'])[1], 3, 0);
            $end_ms = str_pad(explode(".", $end)[1], 3, '0');
            $output .= sprintf($this->format_VTT_header, gmdate("H:i:s", $cue['start']), $start_ms, gmdate("H:i:s", $end), $end_ms, $extra, $cue['value']);
            
        }
        return $output;
        
    }

    function convert_json_to_SBV($input) { // Convert from JSON to SBV format
        $output = "";
        foreach($input as $cue) {
            $end = $cue['start'] + $cue['dur'];
            $start_ms = str_pad(explode(".", $cue['start'])[1], 3, 0);
            $end_ms = str_pad(explode(".", $end)[1], 3, '0');
            $output .= sprintf($this->format_SBV_header, gmdate("H:i:s", $cue['start']), $start_ms, gmdate("H:i:s", $end), $end_ms, $cue['value']);
            
        }
        return $output;
    }

    function convert_json_to_SRT($input) { // Convert from JSON to SBV format
        $output = "";
        $i = 1;
        foreach($input as $cue) {
            $end = $cue['start'] + $cue['dur'];
            $start_ms = str_pad(explode(".", $cue['start'])[1], 3, 0);
            $end_ms = str_pad(explode(".", $end)[1], 3, '0');
            $output .= sprintf($this->format_SRT_header, $i, gmdate("H:i:s", $cue['start']), $start_ms, gmdate("H:i:s", $end), $end_ms, $cue['value']);
            $i++;
            
        }
        return $output;
    }

    function convert_json_to_txt($input) { // Convert from JSON to plain text
        $output = "";
        foreach($input as $cue) {
            $output .= $cue['value']."\n\n";
        }
        return $output;
    }

}

?>
