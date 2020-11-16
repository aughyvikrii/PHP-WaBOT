<?php if( !defined("BOT_START") ) die("Direct access is not allowed.");

/**
 * Global function
 */

function get_var($varName=FALSE){
    $input = file_get_contents("php://input");
    $json_decode = json_decode($input,TRUE);

    if($varName) {
        return @$json_decode[$varName];
    } else return $json_decode;
}

function pre($data,$die=false) {
    print '<pre>'; 
    print_r($data);
    print '</pre>';
    if($die) die;
}

function get_log($id){
    if(!$id) return false;
    
    $file = BASE_PATH."/log/account/{$id}.json";
    if( !file_exists($file) ){
        file_put_contents($file,json_encode([]));
    }
    
    $get = file_get_contents($file);
    
    return json_decode($get,true);
}

function put_log($id,$data){
    if(!$id) return false;
    
    $file = BASE_PATH."/log/account/{$id}.json";
    
    file_put_contents($file,json_encode($data));
}

function clean_log($id,$full=false){
    
    $data = ["last_action"=>""];
    if($full) $data = [];
    
    put_log($id,$data);
}

function log_error($message,$with_response_web=true,$die=true){

    error_log("[".date("Y-m-d H:i:s")."] $message\n",3,ERROR_LOG_FILE);

    $log = "message:\n$message\n\n";
    $log .= "request:\n ".file_get_contents("php://input")."\n\n";

    file_put_contents(BASE_PATH."/log/error-".LOG_ID.".json",$log);
    
    if( $with_response_web ){
        echo json_encode([
            'error' => $message
        ]);
    }

    if($die) die;
}

function main_curl($url,$params) {
    $url =  rtrim(API_BASE_URL,"/")."/".ltrim($url,"/");

    $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $exec = curl_exec($ch);
        curl_close($ch);
    $json_decode = json_decode($exec,TRUE);
    return empty($json_decode) ? [] : $json_decode;
}

function sendMessage($message,$destination,$device_key,$quoted_id=FALSE,$quoted_message=FALSE,$quoted_participant=FALSE,$is_group=FALSE) {

    $data = [
        'api_key' => API_KEY,
        'device_key' => $device_key,
        'destination' => $destination,
        'message' => $message,
        'quoted_id' => $quoted_id,
        'quoted_message' => $quoted_message,
        'quoted_participant' => $quoted_participant
    ];

    if($is_group) {
        unset($data['destination']);
        $data['group_id'] = $destination;
    }

    return main_curl('send-message',$data);
}

function sendMessageGroup($message,$destination,$device_key=FALSE,$quoted_id=FALSE,$quoted_message=FALSE,$quoted_participant=FALSE){
    return sendMessage($message,$destination,$device_key,$quoted_id,$quoted_message,$quoted_participant,TRUE);
}

function reply($message,$destination=false){
    $device_key = get_var('device');
    $quoted_id = get_var('message_id');
    $quoted_message = get_var('text');
    $quoted_participant = get_var('from');
    $is_group = FALSE;

    if(!$destination) {
        if($group_id = get_var('group_id')) {
            $is_group = TRUE;
        } else {
            $destination = $quoted_participant;
        }
    }

    if($quoted_id && $quoted_message && $quoted_participant){

    } else {
        $quoted_participant = $quoted_message = $quoted_id = FALSE;
    }

    if($is_group) {
        return sendMessageGroup($message,$group_id,$device_key,$quoted_id,$quoted_message,$quoted_participant);
    } else {
        return sendMessage($message,$destination,$device_key,$quoted_id,$quoted_message,$quoted_participant);
    }
}