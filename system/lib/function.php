<?php if( !defined("BOT_START") ) die("Direct access is not allowed.");

/**
 * Global function
 */

function ApiRequest($url,$data=false,$method='GET')
{

    $header[] = 'Authorization: Bearer '.CHANNEL_ACCESS_TOKEN;

    $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);

        if( $data ) {
            $header[] = 'Content-Type: application/json';

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS,$data);

        }

        switch( strtoupper($method) ){
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS,'');
                break;
            
            case 'GET': break;
            
            default: curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $exec = curl_exec($ch);

    $log = "URL: {$url}";
    $log .= "\n\n".( is_array($exec) ? json_encode($exec) : $exec );

    file_put_contents(BASE_PATH."/log/log_request.json",$log);

    $json = json_decode($exec,true);

    if( !empty($json) ) return $json;
    else return false;
}

function ReplyChat($replyToken, $pesan)
{
    $data['replyToken'] = $replyToken;

    if( !is_array($pesan) ){
        $data['messages'] = [
            [
                'type'  => 'text',
                'text'  => $pesan
            ]
        ];
    } else {
        $data['messages'] = [ $pesan ];
    }

    return ApiRequest('https://api.line.me/v2/bot/message/reply',json_encode($data));
}

function GetUserAccount($userId) {
    return ApiRequest("https://api.line.me/v2/bot/profile/{$userId}");
}

function PushMessage($data){
    return ApiRequest('https://api.line.me/v2/bot/message/push',json_encode($data));
}

function leaveGroup($groupId){
    return ApiRequest("https://api.line.me/v2/bot/group/{$groupId}/leave",false,'POST');
}

function leaveRoom($roomId){
    return ApiRequest("https://api.line.me/v2/bot/room/{$roomId}/leave",false,'POST');
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